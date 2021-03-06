------------------------------------------------------------------------------
--                                                                          --
--                            GLADE COMPONENTS                              --
--                                                                          --
--                 S Y S T E M . S H A R E D _ M E M O R Y                  --
--                                                                          --
--                                 B o d y                                  --
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

with System.Garlic.Storages; use System.Garlic.Storages;

package body System.Shared_Memory is

   -----------------------
   -- Shared_Mem_RFile --
   -----------------------

   function Shared_Mem_RFile (Var : String) return SIO.Stream_Access is
   begin
      return SIO.Stream_Access (Lookup_Variable (Var, Read));
   end Shared_Mem_RFile;

   ----------------------
   -- Shared_Mem_Wfile --
   ----------------------

   function Shared_Mem_WFile (Var : String) return SIO.Stream_Access is
   begin
      return SIO.Stream_Access (Lookup_Variable (Var, Write));
   end Shared_Mem_WFile;

   ---------------------
   -- Shared_Mem_Lock --
   ---------------------

   procedure Shared_Mem_Lock (Var : String) is
   begin
      Enter_Variable (Lookup_Variable (Var, Lock).all);
   end Shared_Mem_Lock;

   -----------------------
   -- Shared_Mem_Unlock --
   -----------------------

   procedure Shared_Mem_Unlock (Var : String) is
   begin
      Leave_Variable (Lookup_Variable (Var, Unlock).all);
   end Shared_Mem_Unlock;

end System.Shared_Memory;
