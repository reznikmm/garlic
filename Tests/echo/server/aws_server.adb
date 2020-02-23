with Ada.Text_IO;

with AWS.Default;
with AWS.Server;
with AWS.Net.WebSocket.Registry.Control;

with HTTP_Callback;
with WS_Callback;

procedure AWS_Server is

   WS : AWS.Server.HTTP;

begin
   AWS.Net.WebSocket.Registry.Control.Start;

   AWS.Net.WebSocket.Registry.Register ("/echo", WS_Callback.Create'Access);

   AWS.Server.Start
     (WS, "Echo WS Server",
      Max_Connection => 1,
      Callback       => HTTP_Callback'Access);

   Ada.Text_IO.Put_Line
     ("Call me on port" & Positive'Image (AWS.Default.Server_Port));

   AWS.Server.Wait (AWS.Server.Q_Key_Pressed);

   AWS.Server.Shutdown (WS);
end AWS_Server;
