with League.Strings;
with League.String_Vectors;

package Driver.Tests is

   type Test is limited interface;

   not overriding procedure Run
     (Self   : in out Test;
      Result : out Test_Status) is abstract;

   not overriding function Name
     (Self : Test) return League.Strings.Universal_String is abstract;

   not overriding function Diagnostic
     (Self : Test) return League.String_Vectors.Universal_String_Vector
       is abstract;
   --  Error messages if test fails.

end Driver.Tests;
