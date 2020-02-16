with Ada.Text_IO;
with Ada.Streams;

with AWS.Utils.Streams;
with Interfaces;

package body WS_Callback is

   ------------
   -- Create --
   ------------

   function Create
     (Socket : AWS.Net.Socket_Access; Request : AWS.Status.Data)
      return AWS.Net.WebSocket.Object'Class
   is
   begin
      return Result : Object :=
        (AWS.Net.WebSocket.Object
          (AWS.Net.WebSocket.Create (Socket, Request)) with 0);
   end Create;

   ----------------
   -- On_Message --
   ----------------

   overriding procedure On_Message
     (Self : in out Object; Message : String)
   is
      S : aliased AWS.Utils.Streams.Strings;
      R : aliased AWS.Utils.Streams.Strings;
   begin
      S.Open (Message);

      declare
         use type Ada.Streams.Stream_Element_Count;
         Reciver : Interfaces.Unsigned_64 :=
           Interfaces.Unsigned_64'Input (S'Access);
         Index   : Integer := Integer'Input (S'Access);
         Text    : String := String'Input (S'Access);
         Data    : Ada.Streams.Stream_Element_Array (1 .. Text'Length + 16);
         Last    : Ada.Streams.Stream_Element_Count;
      begin
         Ada.Text_IO.Put_Line ("On_Message: " & Text);
         String'Output (R'Access, "");  --  Null_Occurence
         String'Output (R'Access, Text);
         R.Read (Data, Last);
         Self.Send (Data, Is_Binary => True);
      end;
   end On_Message;

   -------------
   -- On_Open --
   -------------

   overriding procedure On_Open (Self : in out Object; Message : String) is
      S : aliased AWS.Utils.Streams.Strings;
      Data : Ada.Streams.Stream_Element_Array (1 .. 30);
      Last : Ada.Streams.Stream_Element_Count;
   begin
      Ada.Text_IO.Put_Line ("On_Open:  " & Message);
      Interfaces.Unsigned_64'Write (S'Access, 1);  --  PID
      Interfaces.Unsigned_64'Write (S'Access, 1);  --  Units
      String'Output (S'Access, "REMOTE");  --  Units

      S.Read (Data, Last);
      Self.Send (Data, Is_Binary => True);
   end On_Open;

   --------------
   -- On_Close --
   --------------

   overriding procedure On_Close (Self : in out Object; Message : String) is
   begin
      Ada.Text_IO.Put_Line ("On_Close: " & Message);
   end On_Close;

   --------------
   -- On_Error --
   --------------

   overriding procedure On_Error (Self : in out Object; Message : String) is
   begin
      Ada.Text_IO.Put_Line ("On_Error: " & Message);
   end On_Error;

end WS_Callback;
