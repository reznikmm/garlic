------------------------------------------------------------------------------
--                                                                          --
--                            GLADE COMPONENTS                              --
--                                                                          --
--           S Y S T E M . P A R T I T I O N _ I N T E R F A C E            --
--                                                                          --
--                                 S p e c                                  --
--                                                                          --
--         Copyright (C) 1996-2020 Free Software Foundation, Inc.           --
--                                                                          --
-- GARLIC is free software;  you can redistribute it and/or modify it under --
-- terms of the  GNU General Public License  as published by the Free Soft- --
-- ware Foundation;  either version 2,  or (at your option)  any later ver- --
-- sion.  GARLIC is distributed  in the hope that  it will be  useful,  but --
-- WITHOUT ANY WARRANTY;  without even the implied warranty of MERCHANTABI- --
-- LITY or  FITNESS FOR A PARTICULAR PURPOSE.  See the  GNU General Public  --
-- License  for more details.  You should have received  a copy of the GNU  --
-- General Public License  distributed with GARLIC;  see file COPYING.  If  --
-- not, write to the Free Software Foundation, 59 Temple Place - Suite 330, --
-- Boston, MA 02111-1307, USA.                                              --
--                                                                          --
-- As a special exception,  if other files  instantiate  generics from this --
-- unit, or you link  this unit with other files  to produce an executable, --
-- this  unit  does not  by itself cause  the resulting  executable  to  be --
-- covered  by the  GNU  General  Public  License.  This exception does not --
-- however invalidate  any other reasons why  the executable file  might be --
-- covered by the  GNU Public License.                                      --
--                                                                          --
------------------------------------------------------------------------------

with Garlic.Contexts;

with System.IO;

package body System.Partition_Interface is

   type Context_Access is
     access all Garlic.Contexts.Context'Class with Storage_Size => 0;

   type String_Access is access constant String;

   type Receiving_Stub_Item is record
      Name          : String_Access;
      Receiver      : RPC_Receiver;
      Version       : String_Access;
      Subp_Info     : System.Address;
      Subp_Info_Len : Integer;
   end record;

   Stubs : array (Interfaces.Unsigned_64 range 1 .. RPC.Max_Local_Units)
     of Receiving_Stub_Item;

   Last : Interfaces.Unsigned_64 range 0 .. RPC.Max_Local_Units := 0;

   type RCI_Listener is new Garlic.Contexts.RCI_Promises.Listener with record
      Info : Garlic.Contexts.RCI_Information;
   end record;

   overriding procedure On_Resolve
     (Self  : in out RCI_Listener;
      Value : Garlic.Contexts.RCI_Information);

   overriding procedure On_Reject (Self : in out RCI_Listener);

   Shared_Listener : aliased RCI_Listener;

--     Parition_Map :
--       array (Interfaces.Unsigned_64 range 1 .. RPC.Max_Local_Units)
--         of RPC.Partition_ID := (others => 0);
   --  Map from Unit index to corresponding partition

   procedure Global_RPC_Receiver
     (Params : access System.RPC.Params_Stream_Type;
      Result : access System.RPC.Params_Stream_Type);
   --  RMM: Request handler for whole partition. It should be registered in
   --  System.RPC according to ARM E.5

   -----------
   -- Check --
   -----------

   procedure Check (Name : Unit_Name; Version : String; RCI : Boolean := True)
   is
   begin
      raise Program_Error with "Unimplemented procedure Check";
   end Check;

   --------------------
   -- Same_Partition --
   --------------------

   function Same_Partition
     (Left : access RACW_Stub_Type; Right : access RACW_Stub_Type)
      return Boolean
   is
      pragma Unreferenced (Right, Left);
   begin
      return raise Program_Error with "Unimplemented function Same_Partition";
   end Same_Partition;

   --------------------------------
   -- Register_Passive_Partition --
   --------------------------------

   procedure Register_Passive_Partition
     (Partition : out RPC.Partition_ID; Name : String; Location : String)
   is
   begin
      raise Program_Error
        with "Unimplemented procedure Register_Passive_Partition";
   end Register_Passive_Partition;

   ---------------------------------------------------
   -- Register_Passive_Package_On_Passive_Partition --
   ---------------------------------------------------

   procedure Register_Passive_Package_On_Passive_Partition
     (Partition : RPC.Partition_ID; Name : String; Version : String := "")
   is
   begin
      raise Program_Error with "Unimplemented procedure " &
        "Register_Passive_Package_On_Passive_Partition";
   end Register_Passive_Package_On_Passive_Partition;

   ---------------------------------
   -- Elaborate_Passive_Partition --
   ---------------------------------

   procedure Elaborate_Passive_Partition (Partition : RPC.Partition_ID) is
   begin
      raise Program_Error
        with "Unimplemented procedure Elaborate_Passive_Partition";
   end Elaborate_Passive_Partition;

   -----------------------------
   -- Get_Active_Partition_ID --
   -----------------------------

   function Get_Active_Partition_ID
     (Name : Unit_Name) return System.RPC.Partition_ID
   is
   begin
      System.IO.Put_Line ("Get_Active_Partition_ID: " & Name);
      for J in 1 .. Last loop
         if Stubs (J).Name.all = Name then
            return Garlic.Contexts.Current.Local_Id;
         end if;
      end loop;

      declare
         Promise : constant not null access
           Garlic.Contexts.RCI_Promises.Promise :=
             Garlic.Contexts.Current.Get_RCI_Information (Name);
      begin
         Promise.Add_Listener (Shared_Listener'Access);
         Garlic.Contexts.Current.Wait_Promise (Promise.all);

         return Shared_Listener.Info.PID;
      end;
   end Get_Active_Partition_ID;

   ------------------------
   -- Get_Active_Version --
   ------------------------

   function Get_Active_Version (Name : Unit_Name) return String is
      pragma Unreferenced (Name);
   begin
      return raise Program_Error
          with "Unimplemented function Get_Active_Version";
   end Get_Active_Version;

   ----------------------------
   -- Get_Local_Partition_ID --
   ----------------------------

   function Get_Local_Partition_ID return RPC.Partition_ID is
   begin
      return Garlic.Contexts.Current.Local_Id;
   end Get_Local_Partition_ID;

   ------------------------
   -- Get_Partition_Name --
   ------------------------

   function Get_Partition_Name (Partition : Integer) return String is
      pragma Unreferenced (Partition);
   begin
      return raise Program_Error
          with "Unimplemented function Get_Partition_Name";
   end Get_Partition_Name;

   ------------------------------
   -- Get_Passive_Partition_ID --
   ------------------------------

   function Get_Passive_Partition_ID
     (Name : Unit_Name) return System.RPC.Partition_ID
   is
      pragma Unreferenced (Name);
   begin
      return raise Program_Error
          with "Unimplemented function Get_Passive_Partition_ID";
   end Get_Passive_Partition_ID;

   -------------------------
   -- Get_Passive_Version --
   -------------------------

   function Get_Passive_Version (Name : Unit_Name) return String is
      pragma Unreferenced (Name);
   begin
      return raise Program_Error
          with "Unimplemented function Get_Passive_Version";
   end Get_Passive_Version;

   ------------------------------
   -- Get_RCI_Package_Receiver --
   ------------------------------

   function Get_RCI_Package_Receiver
     (Name : Unit_Name) return Interfaces.Unsigned_64
   is
   begin
      for J in 1 .. Last loop
         if Stubs (J).Name.all = Name then
            return J;
         end if;
      end loop;

      declare
         Promise : constant not null access
           Garlic.Contexts.RCI_Promises.Promise :=
             Garlic.Contexts.Current.Get_RCI_Information (Name);
      begin
         Promise.Add_Listener (Shared_Listener'Access);
         Garlic.Contexts.Current.Wait_Promise (Promise.all);

         return Shared_Listener.Info.Receiver;
      end;
   end Get_RCI_Package_Receiver;

   -----------------------------
   -- Register_Receiving_Stub --
   -----------------------------

   procedure Register_Receiving_Stub
     (Name          : Unit_Name;
      Receiver      : RPC_Receiver;
      Version       : String := "";
      Subp_Info     : System.Address;
      Subp_Info_Len : Integer)
   is
      Item : constant Receiving_Stub_Item :=
        (Name          => Name'Unrestricted_Access,
         Receiver      => Receiver,
         Version       => Version'Unrestricted_Access,
         Subp_Info     => Subp_Info,
         Subp_Info_Len => Subp_Info_Len);
   begin
      System.IO.Put_Line ("Register_Receiving_Stub: " & Name);
      Last := Interfaces.Unsigned_64'Succ (Last);
      Stubs (Last) := Item;
   end Register_Receiving_Stub;

   ------------------
   -- Get_RAS_Info --
   ------------------

   procedure Get_RAS_Info
     (Name          :     Unit_Name; Subp_Id : Subprogram_Id;
      Proxy_Address : out Interfaces.Unsigned_64)
   is
   begin
      raise Program_Error with "Unimplemented procedure Get_RAS_Info";
   end Get_RAS_Info;

   ------------------------------
   -- Register_Passive_Package --
   ------------------------------

   procedure Register_Passive_Package
     (Name : Unit_Name; Version : String := "")
   is
   begin
      raise Program_Error
        with "Unimplemented procedure Register_Passive_Package";
   end Register_Passive_Package;

   -------------------------------
   -- Get_Unique_Remote_Pointer --
   -------------------------------

   procedure Get_Unique_Remote_Pointer (Handler : in out RACW_Stub_Type_Access)
   is
   begin
      raise Program_Error
        with "Unimplemented procedure Get_Unique_Remote_Pointer";
   end Get_Unique_Remote_Pointer;

   -------------------------
   -- Global_RPC_Receiver --
   -------------------------

   procedure Global_RPC_Receiver
     (Params : access System.RPC.Params_Stream_Type;
      Result : access System.RPC.Params_Stream_Type)
   is
      RCI_Package_Receiver : Interfaces.Unsigned_64;
      Request              : constant Request_Access :=
        (RST_Access (Params), RST_Access (Result));
   begin
      Interfaces.Unsigned_64'Read (Params, RCI_Package_Receiver);
      Stubs (RCI_Package_Receiver).Receiver (Request);
   end Global_RPC_Receiver;

   ----------------
   -- Initialize --
   ----------------

   procedure Initialize (Context : access Garlic.Contexts.Context'Class) is
   begin
      Garlic.Contexts.Current := Context_Access (Context);
      System.RPC.Establish_RPC_Receiver
        (Context.Local_Id, Global_RPC_Receiver'Access);
   end Initialize;

   ---------------
   -- On_Reject --
   ---------------

   overriding procedure On_Reject (Self : in out RCI_Listener) is
   begin
      raise Program_Error;
   end On_Reject;

   ----------------
   -- On_Resolve --
   ----------------

   overriding procedure On_Resolve
     (Self  : in out RCI_Listener;
      Value : Garlic.Contexts.RCI_Information) is
   begin
      Self.Info := Value;
   end On_Resolve;

   ------------------------------------
   -- Raise_Program_Error_For_E_4_18 --
   ------------------------------------

   procedure Raise_Program_Error_For_E_4_18 is
   begin
      raise Program_Error
        with "Unimplemented procedure Raise_Program_Error_For_E_4_18";
   end Raise_Program_Error_For_E_4_18;

   -------------------------------------
   -- Raise_Program_Error_Unknown_Tag --
   -------------------------------------

   procedure Raise_Program_Error_Unknown_Tag
     (E : Ada.Exceptions.Exception_Occurrence)
   is
   begin
      raise Program_Error
        with "Unimplemented procedure Raise_Program_Error_Unknown_Tag";
   end Raise_Program_Error_Unknown_Tag;

   -----------------
   -- RCI_Locator --
   -----------------

   package body RCI_Locator is

      Listener : aliased RCI_Listener;

      ------------------------------
      -- Get_RCI_Package_Receiver --
      ------------------------------

      function Get_RCI_Package_Receiver return Interfaces.Unsigned_64 is
      begin
         if Listener.Info.Receiver in 0 then
            for J in 1 .. Last loop
               if Stubs (J).Name.all = RCI_Name then
                  Listener.Info.Receiver := J;
                  return J;
               end if;
            end loop;

            declare
               Promise : constant not null access
                 Garlic.Contexts.RCI_Promises.Promise :=
                   Garlic.Contexts.Current.Get_RCI_Information (RCI_Name);
            begin
               Promise.Add_Listener (Listener'Access);
               Garlic.Contexts.Current.Wait_Promise (Promise.all);
            end;
         end if;

         return Listener.Info.Receiver;
      end Get_RCI_Package_Receiver;

      -----------------------------
      -- Get_Active_Partition_ID --
      -----------------------------

      function Get_Active_Partition_ID return RPC.Partition_ID is
      begin
         if Listener.Info.PID in 0 then
            for J in 1 .. Last loop
               if Stubs (J).Name.all = RCI_Name then
                  Listener.Info.PID := Garlic.Contexts.Current.Local_Id;
                  return Listener.Info.PID;
               end if;
            end loop;

            declare
               Promise : constant not null access
                 Garlic.Contexts.RCI_Promises.Promise :=
                   Garlic.Contexts.Current.Get_RCI_Information (RCI_Name);
            begin
               Promise.Add_Listener (Listener'Access);
               Garlic.Contexts.Current.Wait_Promise (Promise.all);
            end;
         end if;

         return Listener.Info.PID;
      end Get_Active_Partition_ID;

   end RCI_Locator;

   ---------
   -- Run --
   ---------

   procedure Run (Main : Main_Subprogram_Type := null) is
   begin
      raise Program_Error with "Unimplemented procedure Run";
   end Run;

begin
   System.IO.Put_Line ("System.Partition_Interface");
end System.Partition_Interface;
