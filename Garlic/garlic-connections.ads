package Garlic.Connections is
   pragma Preelaborate;

   type Connection is limited interface;
   --  A connection to a remote partition.

   type Connection_Access is access all Connection'Class
     with Storage_Size => 0;

end Garlic.Connections;
