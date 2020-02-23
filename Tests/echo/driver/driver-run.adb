with Ada.Wide_Wide_Text_IO;
with Ada.Command_Line;

with League.Application;
with League.JSON.Arrays;
with League.JSON.Objects;
with League.JSON.Values;
with League.String_Vectors;
with League.Strings;

with WebDriver.Drivers;
with WebDriver.Remote;

with Driver.Context;
with Driver.Test_Lists;

procedure Driver.Run is

   function "+"
     (Text : Wide_Wide_String) return League.Strings.Universal_String
      renames League.Strings.To_Universal_String;

   function Options return League.JSON.Values.JSON_Value;
   --  Pass all command line option to the WebDriver's session

   -------------
   -- Options --
   -------------

   function Options return League.JSON.Values.JSON_Value is
      Arguments : constant League.String_Vectors.Universal_String_Vector :=
        League.Application.Arguments;

      Result   : League.JSON.Objects.JSON_Object;
      Options  : League.JSON.Objects.JSON_Object;
      Args     : League.JSON.Arrays.JSON_Array;
   begin
      if not Arguments.Is_Empty then

         Options.Insert
           (+"binary",
            League.JSON.Values.To_JSON_Value
              (Arguments (1)));

         for J in 2 .. Arguments.Length loop
            Args.Append (League.JSON.Values.To_JSON_Value (Arguments (J)));
         end loop;

         if not Args.Is_Empty then
            Options.Insert (+"args", Args.To_JSON_Value);
         end if;

         Result.Insert (+"chromeOptions", Options.To_JSON_Value);

      end if;

      return Result.To_JSON_Value;
   end Options;

   Status : Test_Status := Passed;

   Web_Driver : aliased WebDriver.Drivers.Driver'Class :=
     WebDriver.Remote.Create (+"http://127.0.0.1:9515");

begin
   Context.Session := Web_Driver.New_Session (Options);

   for Test of Driver.Test_Lists.All_Tests loop
      declare
         Next : Test_Status := Passed;
      begin
         Test.Run (Next);
         Ada.Wide_Wide_Text_IO.Put (Test.Name.To_Wide_Wide_String);
         Ada.Wide_Wide_Text_IO.Put (" ");
         Ada.Wide_Wide_Text_IO.Put_Line (Test_Status'Wide_Wide_Image (Next));

         if Next /= Passed then
            declare
               List : constant League.String_Vectors.Universal_String_Vector :=
                 Test.Diagnostic;
            begin
               for J in 1 .. List.Length loop
                  Ada.Wide_Wide_Text_IO.Put_Line
                    (List (J).To_Wide_Wide_String);
               end loop;
            end;
         end if;

         Status := Test_Status'Max (Status, Next);
      end;
   end loop;

   if Status = Failed then
      Ada.Command_Line.Set_Exit_Status (Ada.Command_Line.Failure);
   end if;
end Driver.Run;
