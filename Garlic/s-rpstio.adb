package body System.RPC.Stream_IO is

   ----------
   -- Open --
   ----------

   procedure Open
     (Stream    : in out Partition_Stream_Type;
      Partition :        System.RPC.Partition_ID; Mode : Stream_Mode)
   is
   begin
      raise Program_Error with "Unimplemented procedure Open";
   end Open;

   -----------
   -- Close --
   -----------

   procedure Close (Stream : in out Partition_Stream_Type) is
   begin
      raise Program_Error with "Unimplemented procedure Close";
   end Close;

   ----------
   -- Read --
   ----------

   procedure Read
     (Stream : in out Partition_Stream_Type;
      Item   :    out Ada.Streams.Stream_Element_Array;
      Last   :    out Ada.Streams.Stream_Element_Offset)
   is
   begin
      raise Program_Error with "Unimplemented procedure Read";
   end Read;

   -----------
   -- Write --
   -----------

   procedure Write
     (Stream : in out Partition_Stream_Type;
      Item   :        Ada.Streams.Stream_Element_Array)
   is
   begin
      raise Program_Error with "Unimplemented procedure Write";
   end Write;

   ----------------
   -- Initialize --
   ----------------

   procedure Initialize is
   begin
      raise Program_Error with "Unimplemented procedure Initialize";
   end Initialize;

end System.RPC.Stream_IO;
