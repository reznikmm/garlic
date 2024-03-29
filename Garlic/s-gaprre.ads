------------------------------------------------------------------------------
--                                                                          --
--                            GLADE COMPONENTS                              --
--                                                                          --
--       S Y S T E M . G A R L I C . P R O T O C O L S . R E P L A Y        --
--                                                                          --
--                                 S p e c                                  --
--                                                                          --
--         Copyright (C) 1996-2023 Free Software Foundation, Inc.           --
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

with System.Garlic.Exceptions;
with System.Garlic.Types;

package System.Garlic.Protocols.Replay is

   --  See System.Garlic.Protocols.

   type Replay_Protocol is
      new System.Garlic.Protocols.Protocol_Type with private;

   procedure Activate
     (Protocol : access Replay_Protocol;
      Error    : in out Exceptions.Error_Type);

   function Create return System.Garlic.Protocols.Protocol_Access;
   --  Return always the same self reference.

   function Get_Data
     (Protocol : access Replay_Protocol)
     return Garlic.Utils.String_List_Access;

   function Get_Name
     (Protocol : access Replay_Protocol)
     return String;

   procedure Initialize
     (Protocol  : access Replay_Protocol;
      Self_Data : String;
      Required  : Boolean;
      Performed : out Boolean;
      Error     : in out Exceptions.Error_Type);

   function Receive
     (Protocol  : access Replay_Protocol;
      Timeout   : Duration)
     return Boolean;

   procedure Send
      (Protocol  : access Replay_Protocol;
       Partition : Types.Partition_ID;
       Data      : access Ada.Streams.Stream_Element_Array;
       Error     : in out Exceptions.Error_Type);

   procedure Set_Boot_Data
     (Protocol  : access Replay_Protocol;
      Boot_Data : String;
      Error     : in out Exceptions.Error_Type);

   procedure Shutdown (Protocol : access Replay_Protocol);

private

   type Replay_Protocol is new System.Garlic.Protocols.Protocol_Type
     with null record;

end System.Garlic.Protocols.Replay;
