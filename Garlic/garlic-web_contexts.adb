with Ada.Streams;

pragma Warnings (Off);
with System.IO;
pragma Warnings (On);

with WASM.Abort_Execution;
with Web.Message_Events;

package body Garlic.Web_Contexts is

   procedure Find_Unit
     (Self : Context'Class;
      Name : String;
      Found : out Interfaces.Unsigned_64;
      Empty : out Interfaces.Unsigned_64);

   function Equal_Case_Insensitive (Left, Right : String) return Boolean;

   procedure Connected
     (Self : in out Connection'Class;
      Data : Ada.Streams.Stream_Element_Array);

   procedure Responsed
     (Self : in out Connection'Class;
      Data : Garlic.Contexts.Stream_Access);

   ----------------------------
   -- Equal_Case_Insensitive --
   ----------------------------

   function Equal_Case_Insensitive (Left, Right : String) return Boolean is
      function To_Upper (C : Character) return Character;

      function To_Upper (C : Character) return Character is
      begin
         if C in 'a' .. 'z' then
            return Character'Val
              (Character'Pos (C) - Character'Pos ('a') + Character'Pos ('A'));
         else
            return C;
         end if;
      end To_Upper;
   begin
      if Left'Length = Right'Length then
         for J in Left'First .. Left'Last loop
            if To_Upper (Right (Right'First + J - Left'First))
              /= To_Upper (Left (J))
            then
               return False;
            end if;
         end loop;

         return True;
      else
         return False;
      end if;
   end Equal_Case_Insensitive;

   ---------------
   -- Find_Unit --
   ---------------

   procedure Find_Unit
     (Self : Context'Class;
      Name : String;
      Found : out Interfaces.Unsigned_64;
      Empty : out Interfaces.Unsigned_64) is
   begin
      Found := 0;
      Empty := 0;

      for J in Self.Units'Range loop
         if Self.Units (J).Name = null then
            Empty := J;
         elsif Equal_Case_Insensitive (Self.Units (J).Name.all, Name) then
            Found := J;
            return;
         end if;
      end loop;
   end Find_Unit;

   ---------------
   -- Connected --
   ---------------

   procedure Connected
     (Self : in out Connection'Class;
      Data : Ada.Streams.Stream_Element_Array)
   is
      Input : aliased System.RPC.Params_Stream_Type (Data'Length);
      Units : Interfaces.Unsigned_64;
   begin
      Input.Write (Data);
      System.RPC.Partition_ID'Read (Input'Access, Self.PID);
      Interfaces.Unsigned_64'Read (Input'Access, Units);

      for J in 1 .. Units loop
         declare
            Name  : String := String'Input (Input'Access);
            Found : Interfaces.Unsigned_64;
            Empty : Interfaces.Unsigned_64;
         begin
            System.IO.Put_Line (Name);
            Self.Context.Find_Unit (Name, Found, Empty);

            if Found in 0 then
               Self.Context.Units (Empty).Name := new String'(Name);
               Found := Empty;
            end if;

            Self.Context.Units (Found).Info.Resolve
              ((PID => Self.PID, Receiver => J));
         end;
      end loop;
   end Connected;

   -----------------------
   -- Defered_Execution --
   -----------------------

   procedure Defered_Execution
     (Self : in out Context'Class;
      Run  : not null Procedure_Access) is
   begin
      Self.Run := Run;
      Run.all;
      raise Program_Error;
   end Defered_Execution;

   ------------------
   -- Handle_Event --
   ------------------

   overriding procedure Handle_Event
    (Self  : in out Connection;
     Event : in out Web.DOM.Events.Event'Class)
   is
      use type Ada.Streams.Stream_Element_Count;
      Message : Web.Message_Events.Message_Event := Event.As_Message_Event;
      Length  : Ada.Streams.Stream_Element_Count := Message.Data_Byte_Length;
      Data    : aliased Ada.Streams.Stream_Element_Array :=
        (1 .. Length => <>);
   begin
      System.IO.Put_Line ("Handle_Event");
      Message.Read (Data);

      if Self.Open then
         Responsed (Self, Data'Unchecked_Access);
      else
         Self.Connected (Data);
         Self.Open := True;
      end if;

      if Self.Context.Run /= null then
         Self.Context.Run.all;
      end if;
   end Handle_Event;

   -------------------------
   -- Get_RCI_Information --
   -------------------------

   overriding function Get_RCI_Information
     (Self : in out Context;
      Name : String)
        return not null access Garlic.Contexts.RCI_Promises.Promise
   is
      Found : Interfaces.Unsigned_64;
      Empty : Interfaces.Unsigned_64;
   begin
      Self.Find_Unit (Name, Found, Empty);

      if Found not in 0 then
         return Self.Units (Found).Info'Unchecked_Access;
      end if;

      Self.Units (Empty).Name := Name'Unrestricted_Access;

      return Self.Units (Empty).Info'Unchecked_Access;
   end Get_RCI_Information;

   ----------------
   -- Initialize --
   ----------------

   procedure Initialize
     (Self : in out Context'Class;
      URL  : Web.Strings.Web_String;
      PID  : System.RPC.Partition_ID)
   is
      Message : constant Web.Strings.Web_String :=
        Web.Strings.To_Web_String ("message");
   begin
      Self.PID := PID;
      Self.C.WS := Web.Sockets.Create (URL);
      Self.C.WS.Set_Binary_Type (Web.Sockets.arraybuffer);
      Self.C.WS.Add_Event_Listener
        (Message, Self.C'Unchecked_Access, Capture => True);
   end Initialize;

   --------------
   -- Local_Id --
   --------------

   overriding function Local_Id
     (Self : Context) return System.RPC.Partition_ID is
   begin
      return Self.PID;
   end Local_Id;

   ---------------
   -- Responsed --
   ---------------

   procedure Responsed
     (Self : in out Connection'Class;
      Data : Garlic.Contexts.Stream_Access) is
   begin
      Self.Requests (1).Promise.Resolve (Data);
   end Responsed;

   ----------
   -- Send --
   ----------

   overriding function Send
     (Self   : in out Context;
      PID    : System.RPC.Partition_ID;
      Args   : in out System.RPC.Params_Stream_Type)
      return not null access Garlic.Contexts.Stream_Promises.Promise
   is
      Length : Ada.Streams.Stream_Element_Count := Args.Length;
      Data   : Ada.Streams.Stream_Element_Array (1 .. Length);
   begin
      if Self.C.Requests (1).Promise = null then
         Args.Read (Data, Length);
         pragma Assert (Length in Data'Last);
         Self.C.WS.Send (Data);

         Self.C.Requests (1).Promise :=
           new Garlic.Contexts.Stream_Promises.Promise;
      end if;

      return Self.C.Requests (1).Promise;
   end Send;

   ------------------
   -- Wait_Promise --
   ------------------

   overriding procedure Wait_Promise
     (Self    : in out Context;
      Promise : Garlic.Promises.Abstract_Promise'Class) is
   begin
      if Promise.State in Garlic.Promises.Pending then
         WASM.Abort_Execution;
      end if;
   end Wait_Promise;

end Garlic.Web_Contexts;
