with Ada.Directories;
with Ada.Exceptions;
with Ada.Streams;

with League.Text_Codecs;

with Spawn.Processes.Monitor_Loop;

with WebDriver.Elements;

with Driver.Context;

package body Driver.Sync_Call_Tests is

   function "+"
     (Text : Wide_Wide_String) return League.Strings.Universal_String
      renames League.Strings.To_Universal_String;

   Expect : constant Wide_Wide_String := "Call me on port";

   ----------------
   -- Diagnostic --
   ----------------

   overriding function Diagnostic (Self : Sync_Call_Test)
     return League.String_Vectors.Universal_String_Vector
   is
      Codec : constant League.Text_Codecs.Text_Codec :=
        League.Text_Codecs.Codec_For_Application_Locale;
   begin
      return Result : League.String_Vectors.Universal_String_Vector :=
        Self.Errors
      do
         Result.Append (+"Server output:");
         Result.Append (Codec.Decode (Self.Input));
      end return;
   end Diagnostic;

   --------------------
   -- Error_Occurred --
   --------------------

   overriding procedure Error_Occurred
     (Self          : in out Sync_Call_Test;
      Process_Error : Integer)
   is
      Error : League.Strings.Universal_String;
   begin
      Error.Append ("Server start fails:");
      Error.Append (Integer'Wide_Wide_Image (Process_Error));
      Self.Errors.Append (Error);
      Self.Failed := True;
   end Error_Occurred;

   ----------
   -- Name --
   ----------

   overriding function Name
     (Self : Sync_Call_Test) return League.Strings.Universal_String
   is
      pragma Unreferenced (Self);
   begin
      return +"Sync_Call";
   end Name;

   ---------------
   -- Open_Page --
   ---------------

   not overriding procedure Open_Page
     (Self    : in out Sync_Call_Test;
      Session : not null WebDriver.Sessions.Session_Access;
      Result  : out League.Strings.Universal_String)
   is
      pragma Unreferenced (Self);
   begin
      Session.Go (+"http://localhost:8080/index.html");

      declare
         Element : constant WebDriver.Elements.Element_Access :=
           Session.Find_Element
             (Strategy => WebDriver.Tag_Name,
              Selector => +"input");
      begin
         Result := Element.Get_Attribute (+"value");
      end;
   end Open_Page;

   ---------
   -- Run --
   ---------

   overriding procedure Run
     (Self   : in out Sync_Call_Test;
      Result : out Test_Status)
   is
      use type League.Strings.Universal_String;

      Text : League.Strings.Universal_String;
   begin
      Self.Run_Server;

      if Self.Failed then
         Result := Failed;
      else
         Self.Open_Page (Context.Session, Text);

         if Text /= +"This worked fine !!!" then
            Self.Errors.Append (+"Unexpected result:");
            Self.Errors.Append (Text);
            Result := Failed;
         end if;

         Self.Stop_Server;
      end if;
   exception
      when E : others =>
         Self.Errors.Append
           (+("Exception:" & Ada.Exceptions.Wide_Wide_Exception_Name (E)));
         Self.Errors.Append
           (League.Strings.From_UTF_8_String
              (Ada.Exceptions.Exception_Information (E)));
         Result := Failed;
   end Run;

   ----------------
   -- Run_Server --
   ----------------

   not overriding procedure Run_Server (Self : in out Sync_Call_Test) is
      PWD : constant String := Ada.Directories.Current_Directory;
      Dir : constant String := PWD & "/Tests/echo";
   begin
      Self.Process.Set_Program
        (PWD & "/.objs/test/sync_call_server/aws_server");
      Self.Process.Set_Working_Directory (Dir);
      Self.Process.Set_Listener (Self'Unchecked_Access);
      Self.Process.Start;

      while not Self.Started and not Self.Failed loop
         Spawn.Processes.Monitor_Loop (1);
      end loop;
   end Run_Server;

   -------------------------------
   -- Standard_Output_Available --
   -------------------------------

   overriding procedure Standard_Output_Available
     (Self : in out Sync_Call_Test)
   is
      Codec : constant League.Text_Codecs.Text_Codec :=
        League.Text_Codecs.Codec_For_Application_Locale;
      Data  : Ada.Streams.Stream_Element_Array (1 .. 512);
      Last  : Ada.Streams.Stream_Element_Count;
      Text  : League.Strings.Universal_String;
   begin
      Self.Process.Read_Standard_Output (Data, Last);
      Self.Input.Append (Data (1 .. Last));
      Text := Codec.Decode (Data (1 .. Last));

      if Text.Starts_With (Expect) then
         Self.Started := True;
      end if;
   end Standard_Output_Available;

   -----------------
   -- Stop_Server --
   -----------------

   not overriding procedure Stop_Server (Self : in out Sync_Call_Test) is
      Data  : constant Ada.Streams.Stream_Element_Array (1 .. 2) :=
        (Character'Pos ('q'), 10);
      Last  : Ada.Streams.Stream_Element_Count;
   begin
      Self.Process.Write_Standard_Input (Data, Last);

      while Self.Process.Status in Spawn.Processes.Running loop
         Spawn.Processes.Monitor_Loop (1);
      end loop;
   end Stop_Server;

end Driver.Sync_Call_Tests;
