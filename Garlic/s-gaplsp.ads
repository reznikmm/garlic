------------------------------------------------------------------------------
--                                                                          --
--                            GLADE COMPONENTS                              --
--                                                                          --
--      S Y S T E M . G A R L I C . P L A T F O R M _ S P E C I F I C       --
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

package System.Garlic.Platform_Specific is

   --  This package contains the system specific characteristics that have
   --  been checked at configuration time.

   pragma Pure;

   Support_RPC_Abortion   : constant Boolean :=
     True;

   Supports_Local_Launch  : constant Boolean :=
     True;

   Default_Protocol_Name  : constant String  :=
     "tcp";

   Default_Protocol_Data  : constant String  :=
     "localhost:5111";

   Default_Storage_Data : constant String  :=
     "";

   Default_Storage_Name : constant String  :=
     "dfs";

   Rsh_Command          : constant String  :=
     "rsh -n";

   Rsh_Options          : constant String  :=
     "";

end System.Garlic.Platform_Specific;
