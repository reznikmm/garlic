with Driver.Sync_Call_Tests;
with Driver.Tests;

package Driver.Test_Lists is

   Sync_Call : aliased Driver.Sync_Call_Tests.Sync_Call_Test;

   All_Tests : constant array (Positive range <>) of
     access Driver.Tests.Test'Class :=
       (1 => Sync_Call'Access);

end Driver.Test_Lists;
