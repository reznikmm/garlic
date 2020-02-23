--
--  test_basic.adb,v 1.4 1996/04/16 15:25:43 tardieu Exp
--

with Remote; use Remote;
pragma Warnings (Off);
with System.IO; use System.IO;
pragma Warnings (On);

with Web.HTML.Elements;
with Web.HTML.Inputs;
with Web.Window;
with Web.Strings;

procedure Test_Basic is
   function "+" (Item : Wide_Wide_String) return Web.Strings.Web_String
     renames Web.Strings.To_Web_String;
   function "-" (Item : String) return Web.Strings.Web_String;

   ---------
   -- "-" --
   ---------

   function "-" (Item : String) return Web.Strings.Web_String is
      Wide : Wide_Wide_String (Item'Range);
   begin
      for J in Item'Range loop
         Wide (J) := Wide_Wide_Character'Val (Character'Pos (Item (J)));
      end loop;

      return +Wide;
   end "-";
begin
   Put_Line ("Local partition is" & Integer'Image (System.IO'Partition_ID));
   Put_Line ("Remote partition is" & Integer'Image (Remote'Partition_ID));
   declare
      Text   : String := Revert ("!!! enif dekrow sihT");
      Result : Web.HTML.Inputs.HTML_Input_Element :=
        Web.Window.Document.Get_Element_By_Id (+"result").As_HTML_Input;
   begin
      Result.Set_Value (-Text);
      Put_Line (Text);
   end;
end Test_Basic;
