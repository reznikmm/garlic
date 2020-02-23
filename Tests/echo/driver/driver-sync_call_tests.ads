with Driver.Tests;

with Spawn.Processes;

with League.Stream_Element_Vectors;
with League.String_Vectors;
with League.Strings;

with WebDriver.Sessions;

package Driver.Sync_Call_Tests is

   type Sync_Call_Test is limited new Driver.Tests.Test
     and Spawn.Processes.Process_Listener with private;

private
   type Sync_Call_Test is limited new Driver.Tests.Test
     and Spawn.Processes.Process_Listener
   with record
      Process : Spawn.Processes.Process;
      Input   : League.Stream_Element_Vectors.Stream_Element_Vector;
      Started : Boolean := False;
      Failed  : Boolean := False;
      Errors  : League.String_Vectors.Universal_String_Vector;
   end record;

   overriding procedure Run
     (Self   : in out Sync_Call_Test;
      Result : out Test_Status);

   overriding function Name
     (Self : Sync_Call_Test) return League.Strings.Universal_String;

   overriding function Diagnostic (Self : Sync_Call_Test)
     return League.String_Vectors.Universal_String_Vector;

   not overriding procedure Run_Server (Self : in out Sync_Call_Test);
   not overriding procedure Stop_Server (Self : in out Sync_Call_Test);

   not overriding procedure Open_Page
     (Self    : in out Sync_Call_Test;
      Session : not null WebDriver.Sessions.Session_Access;
      Result  : out League.Strings.Universal_String);

   overriding procedure Standard_Output_Available
     (Self : in out Sync_Call_Test);

   overriding procedure Error_Occurred
     (Self          : in out Sync_Call_Test;
      Process_Error : Integer);

end Driver.Sync_Call_Tests;
