------------------------------------------------------------------------------
--                                                                          --
--                            GLADE COMPONENTS                              --
--                                                                          --
--                           S Y S T E M . R P C                            --
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

with Ada.Streams;

package System.RPC is

   pragma Preelaborate;

   type Partition_ID is range 0 .. 2 ** 63 - 1;

   Communication_Error : exception;

   type Params_Stream_Type
     (Initial_Size : Ada.Streams.Stream_Element_Count) is new
       Ada.Streams.Root_Stream_Type with private;

   procedure Read
     (Stream : in out Params_Stream_Type;
      Item   : out Ada.Streams.Stream_Element_Array;
      Last   : out Ada.Streams.Stream_Element_Offset);

   procedure Write
     (Stream : in out Params_Stream_Type;
      Item   : Ada.Streams.Stream_Element_Array);

   function Length
     (Stream : Params_Stream_Type) return Ada.Streams.Stream_Element_Count;

   procedure Do_RPC
     (Partition  : Partition_ID;
      Params     : access Params_Stream_Type;
      Result     : access Params_Stream_Type);
   --  Synchronous call

   procedure Do_APC
     (Partition  : Partition_ID;
      Params     : access Params_Stream_Type);
   --  Asynchronous call

   type RPC_Receiver is
     access procedure
       (Params : access Params_Stream_Type;
        Result : access Params_Stream_Type);
   --  Handled used for incoming RPC
   --
   --  In GNAT generated stubs Params contains:
   --  * Unsigned_64 - result of Get_RCI_Package_Receiver
   --  * Integer - Index of subprogram in the unit
   --  * <arguments>
   --
   --  In GNAT generated stubs Result contains:
   --  * Exception_Occurence - exception to raise, (represented as String)
   --  * <result> if Null_Occurrence

   procedure Establish_RPC_Receiver
     (Partition : Partition_ID;
      Receiver  : RPC_Receiver);
   --  ARM E.5(21). The procedure System.RPC.Establish_RPC_Receiver is called
   --  once, immediately after elaborating the library units of an active
   --  partition (that is, right after the elaboration of the partition) if the
   --  partition includes an RCI library unit, but prior to invoking the main
   --  subprogram, if any. The Partition parameter is the Partition_Id of the
   --  active partition being elaborated. The Receiver parameter designates
   --  an implementation-provided procedure called the RPC-receiver
   --  which will handle all RPCs received by the partition from the PCS.
   --  Establish_RPC_Receiver saves a reference to the RPC-receiver; when a
   --  message is received at the called partition, the RPC-receiver is called
   --  with the Params stream containing the message. When the RPC-receiver
   --  returns, the contents of the stream designated by Result is placed in
   --  a message and sent back to the calling partition.

   --  For now use static configuration:
   Max_Paritions : constant := 2;
   --  No more then Max_Parition alive at the same time
   Max_Local_Units : constant := 10;
   --  No more then Max_Local_Units in the partition
   Max_Concurent_Requests : constant := 1;
   --  No more then Max_Concurent_Requests from one partition

private

   Node_Size : constant := 512;

   type Node;
   type Node_Access is access Node;

   type Node is record
      Data : Ada.Streams.Stream_Element_Array (1 .. Node_Size);
      Next : Node_Access;
   end record;

   type Params_Stream_Type
     (Initial_Size : Ada.Streams.Stream_Element_Count) is
        new Ada.Streams.Root_Stream_Type with
   record
      Last      : Ada.Streams.Stream_Element_Count := 0;
      Position  : Ada.Streams.Stream_Element_Count := 1;
      Node_List : Node_Access;
      Initial   : Ada.Streams.Stream_Element_Array (1 .. Initial_Size);
   end record;

end System.RPC;
