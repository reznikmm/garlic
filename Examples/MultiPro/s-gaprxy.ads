------------------------------------------------------------------------------
--                                                                          --
--                            GLADE COMPONENTS                              --
--                                                                          --
--          S Y S T E M . G A R L I C . P R O T O C O L S . T C P           --
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

with GNAT.Sockets;

with System.Garlic.Exceptions;
with System.Garlic.Types;
with System.Garlic.Utils;

package System.Garlic.Protocols.Xyz is

   --  This package needs documentation ???

   type XYZ_Protocol is new Protocol_Type with private;

   function Create return Protocol_Access;

   overriding procedure Activate
     (Protocol : access XYZ_Protocol;
      Error    : in out Exceptions.Error_Type);

   overriding function Get_Data
     (Protocol  : access XYZ_Protocol)
     return System.Garlic.Utils.String_List_Access;

   overriding function Get_Name
     (Protocol : access XYZ_Protocol)
     return String;

   overriding procedure Initialize
     (Protocol  : access XYZ_Protocol;
      Self_Data : String;
      Required  : Boolean;
      Performed : out Boolean;
      Error     : in out Exceptions.Error_Type);

   overriding function Receive
     (Protocol : access XYZ_Protocol;
      Timeout  : Duration)
     return Boolean;

   overriding procedure Send
     (Protocol  : access XYZ_Protocol;
      Partition : Types.Partition_ID;
      Data      : access Ada.Streams.Stream_Element_Array;
      Error     : in out Exceptions.Error_Type);

   overriding procedure Set_Boot_Data
     (Protocol  : access XYZ_Protocol;
      Boot_Data : String;
      Error     : in out Exceptions.Error_Type);

   overriding procedure Shutdown (Protocol : access XYZ_Protocol);

   Shutdown_Completed : Boolean := False;

   procedure Accept_Until_Closed
     (Incoming : Natural);

   procedure Receive_Until_Closed
     (Peer : GNAT.Sockets.Socket_Type;
      PID  : in out Types.Partition_ID);

   type Allocate_Acceptor_Procedure is access procedure
     (Incoming : Natural);

   type Allocate_Connector_Procedure is access procedure
     (Peer : GNAT.Sockets.Socket_Type;
      PID  : Types.Partition_ID);

   procedure Register_Task_Pool
     (Allocate_Acceptor  : Allocate_Acceptor_Procedure;
      Allocate_Connector : Allocate_Connector_Procedure);

private

   type XYZ_Protocol is new Protocol_Type with null record;

end System.Garlic.Protocols.Xyz;
