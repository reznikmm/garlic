------------------------------------------------------------------------------
--                                                                          --
--                            GLADE COMPONENTS                              --
--                                                                          --
--             S Y S T E M . G A R L I C . P R I O R I T I E S              --
--                                                                          --
--                                 S p e c                                  --
--                                                                          --
--         Copyright (C) 1996-2006 Free Software Foundation, Inc.           --
--                                                                          --
-- GARLIC is free software;  you can redistribute it and/or modify it under --
-- terms of the  GNU General Public License  as published by the Free Soft- --
-- ware Foundation;  either version 2,  or (at your option)  any later ver- --
-- sion.  GARLIC is distributed  in the hope that  it will be  useful,  but --
-- WITHOUT ANY WARRANTY;  without even the implied warranty of MERCHANTABI- --
-- LITY or  FITNESS FOR A PARTICULAR PURPOSE.  See the  GNU General Public  --
-- License  for more details.  You should have received  a copy of the GNU  --
-- General Public License  distributed with GARLIC;  see file COPYING.  If  --
-- not, write to the Free Software Foundation, 59 Temple Place - Suite 330, --
-- Boston, MA 02111-1307, USA.                                              --
--                                                                          --
--
--
--
--
--
--
--
--               GLADE  is maintained by ACT Europe.                        --
--               (email: glade-report@act-europe.fr)                        --
--                                                                          --
------------------------------------------------------------------------------

package System.Garlic.Priorities is

   --  This package implements the suggestion from IRTAW99 and some
   --  enhancements taken from CORBA.

   --  Portable priorities
   type Global_Priority is range 0 .. 255;

   --  During a RPC, set rpc handler priority either to default
   --  priority (server_declared) or to client priority
   --  (client_propagated).
   type Priority_Policy is (Server_Declared, Client_Propagated);

   Default_RPC_Handler_Priority_Policy : constant Priority_Policy
     := Client_Propagated;

   Default_RPC_Handler_Priority : constant Global_Priority
     := Global_Priority'Last;

   RPC_Handler_Priority : Global_Priority
     := Default_RPC_Handler_Priority;

   RPC_Handler_Priority_Policy : Priority_Policy
     := Default_RPC_Handler_Priority_Policy;

   procedure Set_RPC_Handler_Priority
     (A_Priority : Global_Priority);

   procedure Set_RPC_Handler_Priority_Policy
     (A_Policy : Priority_Policy);

end System.Garlic.Priorities;
