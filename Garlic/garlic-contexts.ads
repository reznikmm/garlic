with Ada.Streams;
with Garlic.Promises;
with System.RPC;
with Interfaces;

package Garlic.Contexts is

   pragma Preelaborate;

   type Context is limited interface;

   not overriding function Local_Id
     (Self : Context) return System.RPC.Partition_ID is abstract;
   --  Identifier of the local partition.

   type Stream_Access is access all Ada.Streams.Stream_Element_Array;

   package Stream_Promises is new Promises.Generic_Promises (Stream_Access);

   not overriding function Send
     (Self   : in out Context;
      PID    : System.RPC.Partition_ID;
      Args   : in out System.RPC.Params_Stream_Type)
        return not null access Stream_Promises.Promise is abstract;
   --  Send content of the Args stream and return the promise to notify when
   --  response arrives or is rejected.

   type RCI_Information is record
      PID      : System.RPC.Partition_ID;
      Receiver : Interfaces.Unsigned_64;
   end record;

   package RCI_Promises is new Promises.Generic_Promises (RCI_Information);

   not overriding function Get_RCI_Information
     (Self : in out Context;
      Name : String)
      return not null access RCI_Promises.Promise is abstract;
   --  Find information about RCI unit

   not overriding procedure Wait_Promise
     (Self    : in out Context;
      Promise : Promises.Abstract_Promise'Class) is abstract;

   Current : access Context'Class;

end Garlic.Contexts;
