with Ada.Streams;
with Interfaces;

with System.RPC;

with Garlic.Connections;
with Garlic.Contexts;
with Garlic.Promises;

with Web.DOM.Event_Listeners;
with Web.DOM.Events;
with Web.Sockets;
with Web.Strings;

package Garlic.Web_Contexts is
   pragma Preelaborate;

   type Context is limited new Garlic.Contexts.Context with private;
--   pragma Preelaborable_Initialization (Context);

   type Context_Access is access all Context;

   procedure Initialize
     (Self : in out Context'Class;
      URL  : Web.Strings.Web_String;
      PID  : System.RPC.Partition_ID);

   type Procedure_Access is access procedure;
   --  The procedure to be executed at some point.

   procedure Defered_Execution
     (Self : in out Context'Class;
      Run  : not null Procedure_Access)
        with No_Return;

private

   type Stream_Promise_Access is
     access Garlic.Contexts.Stream_Promises.Promise;
   type Request is record
      Promise : Stream_Promise_Access;
   end record;

   type Request_Array is array (Positive range <>) of Request;

   type Connection (Context : not null access Web_Contexts.Context) is
     new Garlic.Connections.Connection
       and Web.DOM.Event_Listeners.Event_Listener
       with
   record
      PID      : System.RPC.Partition_ID := 0;
      WS       : Web.Sockets.Web_Socket;
      Open     : Boolean := False;
      Requests : Request_Array (1 .. System.RPC.Max_Concurent_Requests);
   end record;

   overriding procedure Handle_Event
    (Self  : in out Connection;
     Event : in out Web.DOM.Events.Event'Class);

   type String_Access is access all String;

   type Unit is record
      Name : String_Access;
      Info : aliased Garlic.Contexts.RCI_Promises.Promise;
   end record;

   type Unit_Array is
     array (Interfaces.Unsigned_64 range <>) of Unit;

   type Context is limited new Garlic.Contexts.Context with record
      C     : aliased Connection (Context'Unchecked_Access);
      URL   : Web.Strings.Web_String;
      PID   : System.RPC.Partition_ID := 0;
      Units : Unit_Array (1 .. System.RPC.Max_Local_Units);
      Run   : Procedure_Access;
   end record;

   overriding function Local_Id
     (Self : Context) return System.RPC.Partition_ID;
   --  Identifier of the local partition.

   overriding function Get_RCI_Information
     (Self : in out Context;
      Name : String)
      return not null access Garlic.Contexts.RCI_Promises.Promise;
   --  Find information about RCI unit

   overriding procedure Wait_Promise
     (Self    : in out Context;
      Promise : Garlic.Promises.Abstract_Promise'Class);

   overriding function Send
     (Self   : in out Context;
      PID    : System.RPC.Partition_ID;
      Args   : in out System.RPC.Params_Stream_Type)
      return not null access Garlic.Contexts.Stream_Promises.Promise;
   --  Send content of the Args stream, write response into Result Stream and
   --  return the promise to notify when response arrives or is rejected.

end Garlic.Web_Contexts;
