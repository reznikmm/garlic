------------------------------------------------------------------------------
--                              Ada Web Server                              --
--                                                                          --
--                     Copyright (C) 2000-2012, AdaCore                     --
--                                                                          --
--  This is free software;  you can redistribute it  and/or modify it       --
--  under terms of the  GNU General Public License as published  by the     --
--  Free Software  Foundation;  either version 3,  or (at your option) any  --
--  later version.  This software is distributed in the hope  that it will  --
--  be useful, but WITHOUT ANY WARRANTY;  without even the implied warranty --
--  of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU     --
--  General Public License for  more details.                               --
--                                                                          --
--  You should have  received  a copy of the GNU General  Public  License   --
--  distributed  with  this  software;   see  file COPYING3.  If not, go    --
--  to http://www.gnu.org/licenses for a complete copy of the license.      --
------------------------------------------------------------------------------

--  The famous Hello Word demo, using AWS framework.

with Ada.Text_IO;

with AWS.Default;
with AWS.Server;
with AWS.Net.WebSocket.Registry.Control;

with HTTP_Callback;
with WS_Callback;

--  with Remote; pragma Unreferenced (Remote);

--  with System.Partition_Interface;

procedure AWS_Server is

   WS : AWS.Server.HTTP;

begin
--   System.Partition_Interface.Initialize (PID => 1);

   Ada.Text_IO.Put_Line
     ("Call me on port" & Positive'Image (AWS.Default.Server_Port));

   AWS.Net.WebSocket.Registry.Control.Start;

   AWS.Net.WebSocket.Registry.Register ("/echo", WS_Callback.Create'Access);

   AWS.Server.Start
     (WS, "Hello World",
      Max_Connection => 1,
      Callback       => HTTP_Callback'Access);

   AWS.Server.Wait (AWS.Server.Q_Key_Pressed);

   AWS.Server.Shutdown (WS);
end AWS_Server;
