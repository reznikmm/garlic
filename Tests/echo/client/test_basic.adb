--
--  test_basic.adb,v 1.4 1996/04/16 15:25:43 tardieu Exp
--

with Remote; use Remote;
pragma Warnings (Off);
with System.IO; use System.IO;
pragma Warnings (On);

procedure Test_Basic is
begin
   Put_Line ("Local partition is" & Integer'Image (System.IO'Partition_ID));
   Put_Line ("Remote partition is" & Integer'Image (Remote'Partition_ID));
   Put_Line (Revert ("!!! enif dekrow sihT"));
end Test_Basic;
