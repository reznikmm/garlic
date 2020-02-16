with Test_Basic;

with Garlic.Contexts;
with Garlic.Web_Contexts;

with Web.Strings;

with System.Partition_Interface;
pragma Warnings (Off);
with System.IO;
pragma Warnings (On);

procedure Main is
   URL : constant Wide_Wide_String :=
     "ws://localhost:8080/echo";
   W : Garlic.Web_Contexts.Context_Access := new Garlic.Web_Contexts.Context;
begin
   System.IO.Put_Line ("Here");
   W.Initialize
     (URL => Web.Strings.To_Web_String (URL),
      PID => 2);
   System.Partition_Interface.Initialize (W);
   W.Defered_Execution (Test_Basic'Access);
end Main;
