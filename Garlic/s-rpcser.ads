------------------------------------------------------------------------------
--                                                                          --
--                            GLADE COMPONENTS                              --
--                                                                          --
--                    S Y S T E M . R P C . S E R V E R                     --
--                                                                          --
--                                 S p e c                                  --
--                                                                          --
--         Copyright (C) 1996-2020 Free Software Foundation, Inc.           --
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
-- As a special exception,  if other files  instantiate  generics from this --
-- unit, or you link  this unit with other files  to produce an executable, --
-- this  unit  does not  by itself cause  the resulting  executable  to  be --
-- covered  by the  GNU  General  Public  License.  This exception does not --
-- however invalidate  any other reasons why  the executable file  might be --
-- covered by the  GNU Public License.                                      --
--                                                                          --
------------------------------------------------------------------------------

with Interfaces;
with Promises;

with System.Partition_Interface;

package System.RPC.Server is
   --  I guess, compiler doesn't use this package by itself, so we can reuse
   --  it to implement RPC server

   pragma Preelaborate;

   type Context is limited interface;
   --  An RPC implementation. It manages set of connection, one per partition.

   not overriding function Local_Id
     (Self : Context) return Partition_ID is abstract;
   --  Identifier of the local partition.

   type Sender is limited interface;
   --  The interface to send requests to a remote partition.

   type Sender_Access is access all Sender'Class with Storage_Size => 0;

   type Params_Stream_Type_Access is access all System.RPC.Params_Stream_Type;

   package Stream_Promises is new Promises.Generic_Promises
     (Params_Stream_Type_Access);

   subtype Stream_Promise is Stream_Promises.Promise;
   --  Promise to get response from the local or remote partition.

   type Connection is limited interface;
   --  A connection to a remote partition.

   type Connection_Access is access all Connection'Class
     with Storage_Size => 0;

   not overriding function Local_Id
     (Self : Connection) return Partition_ID is abstract;
   --  Identifier of the local partition.

   not overriding function Remote_Id
     (Self : Connection) return Partition_ID is abstract;
   --  Identifier of the remote partition.

   not overriding function New_Connection
     (Self   : access Context;
      Remote : Partition_ID;
      Sender : not null Sender_Access) return Connection_Access is abstract;
   --  Create a new connection with a remote partition. Remote is remote
   --  partition identifier. No other connection to this partition should be
   --  already open. Sender is an object to send request to the partition.
   --  Connectin will exist until Close is called.

   not overriding function Send
     (Self     : in out Connection;
      Stream   : Params_Stream_Type) return Stream_Promise is abstract;
   --  Send content of the Stream and use the promise to notify when response
   --  arrives or is rejected.

   not overriding procedure Send
     (Self     : in out Connection;
      Stream   : Params_Stream_Type) is abstract;
   --  Send content of the Stream without any response.

   not overriding procedure Close (Self : in out Connection) is abstract;
   --  Close connection.

   not overriding function Send
     (Self     : in out Sender;
      Stream   : Params_Stream_Type) return Stream_Promise is abstract;
   --  Send content of the Stream and use the promise to notify when response
   --  arrive or rejected.

   not overriding procedure Send
     (Self     : in out Sender;
      Stream   : Params_Stream_Type) is abstract;
   --  Send content of the Stream without any response.

   not overriding procedure Close (Self : in out Sender) is abstract;
   --  Close corresponding connection.

   --  Support for Partition_Interface --

   type RCI_Information is record
      PID      : Partition_ID;
      Receiver : Interfaces.Unsigned_64;
   end record;

   package RCI_Promises is new Promises.Generic_Promises (RCI_Information);

   function Get_Active_Partition_ID
     (Self : in out Context;
      Name : System.Partition_Interface.Unit_Name)
      return RCI_Promises.Promise is abstract;
   --  Find information about RCI unit

   procedure Wait_Promise
     (Self    : in out Context;
      Promise : Promises.Abstract_Promise'Class) is abstract;

end System.RPC.Server;
