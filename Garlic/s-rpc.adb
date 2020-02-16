with Garlic.Contexts;
--  with System.IO;

package body System.RPC is

   Global_Receiver : RPC_Receiver;
   pragma Unreferenced (Global_Receiver);
   --  Per partition RPC_Receiver to be called by on income request
   This_Partition  : Partition_ID;
   --  Id of current partition

   type Stream_Promise_Listener is
     new Garlic.Contexts.Stream_Promises.Listener with record
      Result : Garlic.Contexts.Stream_Access;
   end record;

   overriding procedure On_Resolve
     (Self  : in out Stream_Promise_Listener;
      Value : Garlic.Contexts.Stream_Access);

   overriding procedure On_Reject (Self : in out Stream_Promise_Listener);

   ---------------
   -- On_Reject --
   ---------------

   overriding procedure On_Reject (Self : in out Stream_Promise_Listener) is
   begin
      raise Program_Error;
   end On_Reject;

   ----------------
   -- On_Resolve --
   ----------------

   overriding procedure On_Resolve
     (Self  : in out Stream_Promise_Listener;
      Value : Garlic.Contexts.Stream_Access) is
   begin
      Self.Result := Value;
   end On_Resolve;

   ----------
   -- Read --
   ----------

   procedure Read
     (Stream : in out Params_Stream_Type;
      Item   : out Ada.Streams.Stream_Element_Array;
      Last   : out Ada.Streams.Stream_Element_Offset)
   is
      use Ada.Streams;
      procedure Read_From_Initial;
      procedure Read_From_Node
        (Done : in out Stream_Element_Count;
         Node : not null Node_Access);

      -----------------------
      -- Read_From_Initial --
      -----------------------

      procedure Read_From_Initial is
         Have : constant Stream_Element_Offset :=
           Stream_Element_Offset'Min (Stream.Initial_Size, Stream.Last)
             - Stream.Position + 1;
      begin
         if Have <= 0 then
            Last := Item'First - 1;
         elsif Have >= Item'Length then
            Last := Item'Last;
            Item := Stream.Initial
              (Stream.Position .. Stream.Position + Item'Length - 1);
            Stream.Position := Stream.Position + Item'Length;
         else
            Last := Item'First + Have - 1;
            Item (Item'First .. Last) := Stream.Initial
              (Stream.Position .. Stream.Position + Have - 1);
            Stream.Position := Stream.Position + Have;
         end if;
      end Read_From_Initial;

      --------------------
      -- Read_From_Node --
      --------------------

      procedure Read_From_Node
        (Done : in out Stream_Element_Count;
         Node : not null Node_Access)
      is
         Have : constant Stream_Element_Offset :=
           Stream_Element_Offset'Min (Done + Node_Size, Stream.Last)
             - Stream.Position + 1;
         Need : constant Stream_Element_Count := Item'Last - Last;
      begin
         if Have <= 0 then
            Done := Done + Node_Size;  --  Skip this node
         elsif Have >= Need then
            Item (Last + 1 .. Item'Last) := Node.Data
              (Stream.Position - Done .. Stream.Position - Done + Need - 1);
            Last := Item'Last;
            Stream.Position := Stream.Position + Need;
            Done := Done + Need;
         else
            Item (Last + 1 .. Last + Have) := Node.Data
              (Stream.Position - Done .. Stream.Position - Done + Have - 1);
            Last := Last + Have;
            Stream.Position := Stream.Position + Have;
            Done := Done + Have;
         end if;
      end Read_From_Node;

      Done : Stream_Element_Count := Stream.Initial_Size;
      Node : Node_Access;
   begin
      Read_From_Initial;

      if Stream.Node_List /= null then
         --  Last node in the list points to the first node.
         Node := Stream.Node_List.Next;
      end if;

      while Last < Item'Last and Stream.Position <= Stream.Last loop
         Read_From_Node (Done, Node);
         Node := Node.Next;
      end loop;
   end Read;

   -----------
   -- Write --
   -----------

   procedure Write
     (Stream : in out Params_Stream_Type;
      Item   : Ada.Streams.Stream_Element_Array)
   is
      use Ada.Streams;

      procedure Write_To_Initial;

      procedure Write_To_Node
        (Done : in out Stream_Element_Count;
         Node : not null Node_Access);

      Last : Stream_Element_Offset;

      ----------------------
      -- Write_To_Initial --
      ----------------------

      procedure Write_To_Initial is
         Have : constant Stream_Element_Offset :=
           Stream.Initial_Size - Stream.Last;
         Need : constant Stream_Element_Count := Item'Length;
      begin
         if Have <= 0 then
            Last := Item'First - 1;
            return;
         elsif Have >= Need then
            Stream.Initial (Stream.Last + 1 .. Stream.Last + Need) := Item;
            Stream.Last := Stream.Last + Need;
            Last := Item'Last;
         else
            Stream.Initial (Stream.Last + 1 .. Stream.Last + Have) :=
              Item (Item'First .. Item'First + Have - 1);
            Stream.Last := Stream.Last + Have;
            Last := Item'First + Have - 1;
         end if;
      end Write_To_Initial;

      -------------------
      -- Write_To_Node --
      -------------------

      procedure Write_To_Node
        (Done : in out Stream_Element_Count;
         Node : not null Node_Access)
      is
         Have : constant Stream_Element_Offset :=
           Node_Size - (Stream.Last - Done);
         Need : constant Stream_Element_Count := Item'Last - Last;
      begin
         if Have <= 0 then
            Done := Done + Node_Size;  --  Skip this node
         elsif Have >= Need then
            Node.Data (Stream.Last - Done + 1 .. Stream.Last - Done + Need)
              := Item (Last + 1 .. Item'Last);
            Last := Item'Last;
            Stream.Last := Stream.Last + Need;
            Done := Done + Need;
         else
            Node.Data (Stream.Last - Done + 1 .. Stream.Last - Done + Have)
              := Item (Last + 1 .. Last + Have);
            Last := Last + Have;
            Stream.Last := Stream.Last + Have;
            Done := Done + Have;
         end if;
      end Write_To_Node;

      Done : Stream_Element_Count := Stream.Initial_Size;
      Node : Node_Access;
   begin
      Write_To_Initial;

      if Last < Item'Last then
         if Stream.Node_List = null then
            Node := new RPC.Node;
            Node.Next := Node;
            Stream.Node_List := Node;
         else
            --  Last node in the list points to the first node.
            Node := Stream.Node_List.Next;
         end if;

         loop
            Write_To_Node (Done, Node);

            exit when Last = Item'Last;

            if Node = Stream.Node_List then
               Node := new RPC.Node'(Next => Node.Next, Data => <>);
               Stream.Node_List := Node;
            else
               Node := Node.Next;
            end if;
         end loop;
      end if;
   end Write;

   ------------
   -- Do_RPC --
   ------------

   procedure Do_RPC
     (Partition : Partition_ID;
      Params    : access Params_Stream_Type;
      Result    : access Params_Stream_Type)
   is
   begin
      if Partition = This_Partition then
         raise Program_Error with "Unimplemented procedure Do_RPC";
      else
         declare
            Promise : constant access Garlic.Contexts.Stream_Promises.Promise
              := Garlic.Contexts.Current.Send
                (Partition,
                 Params.all);
            Listener : aliased Stream_Promise_Listener;
         begin
            Garlic.Contexts.Current.Wait_Promise (Promise.all);
            Promise.Add_Listener (Listener'Unchecked_Access);
            Result.Write (Listener.Result.all);
         end;
      end if;
   end Do_RPC;

   ------------
   -- Do_APC --
   ------------

   procedure Do_APC
     (Partition : Partition_ID; Params : access Params_Stream_Type)
   is
   begin
      raise Program_Error with "Unimplemented procedure Do_APC";
   end Do_APC;

   ----------------------------
   -- Establish_RPC_Receiver --
   ----------------------------

   procedure Establish_RPC_Receiver
     (Partition : Partition_ID;
      Receiver  : RPC_Receiver) is
   begin
      This_Partition := Partition;
      Global_Receiver := Receiver;
   end Establish_RPC_Receiver;

   ------------
   -- Length --
   ------------

   function Length
     (Stream : Params_Stream_Type) return Ada.Streams.Stream_Element_Count is
   begin
      return Stream.Last;
   end Length;

end System.RPC;
