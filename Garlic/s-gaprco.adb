with System.Garlic.Protocols.Tcp;
with System.Garlic.Protocols.Tcp.Server;
pragma Elaborate_All (System.Garlic.Protocols.Tcp.Server);
pragma Warnings (Off, System.Garlic.Protocols.Tcp.Server);
package body System.Garlic.Protocols.Config is
   procedure Initialize is
   begin
      Register (System.Garlic.Protocols.Tcp.Create);
   end Initialize;
end System.Garlic.Protocols.Config;
