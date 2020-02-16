with AWS.Net.WebSocket;
with AWS.Status;

package WS_Callback is

   function Create
     (Socket  : AWS.Net.Socket_Access;
      Request : AWS.Status.Data) return AWS.Net.WebSocket.Object'Class;

private

   type Object is new AWS.Net.WebSocket.Object with record
      Count : Natural := 0;
   end record;

   overriding procedure On_Message (Self : in out Object; Message : String);
   --  Message received from the server

   overriding procedure On_Open (Self : in out Object; Message : String);
   --  Open event received from the server

   overriding procedure On_Close (Self : in out Object; Message : String);
   --  Close event received from the server

   overriding procedure On_Error (Self : in out Object; Message : String);
   --  Close event received from the server

end WS_Callback;
