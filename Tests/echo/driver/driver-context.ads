with League.Strings;

with WebDriver.Sessions;

package Driver.Context is

   function "+"
     (Text : Wide_Wide_String) return League.Strings.Universal_String
      renames League.Strings.To_Universal_String;

   Session    : WebDriver.Sessions.Session_Access;

end Driver.Context;
