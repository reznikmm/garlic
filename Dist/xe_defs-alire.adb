------------------------------------------------------------------------------
--                                                                          --
--                            GLADE COMPONENTS                              --
--                                                                          --
--                              X E _ D E F S                               --
--                                                                          --
--                                 S p e c                                  --
--                                                                          --
--         Copyright (C) 2025-2025 Free Software Foundation, Inc.           --
--                                                                          --
-- GNATDIST is  free software;  you  can redistribute  it and/or  modify it --
-- under terms of the  GNU General Public License  as published by the Free --
-- Software  Foundation;  either version 2,  or  (at your option) any later --
-- version. GNATDIST is distributed in the hope that it will be useful, but --
-- WITHOUT ANY WARRANTY;  without even the implied warranty of MERCHANTABI- --
-- LITY or FITNESS  FOR A PARTICULAR PURPOSE.  See the  GNU General  Public --
-- License  for more details.  You should  have received a copy of the  GNU --
-- General Public License distributed with  GNATDIST; see file COPYING.  If --
-- not, write to the Free Software Foundation, 59 Temple Place - Suite 330, --
-- Boston, MA 02111-1307, USA.                                              --
--                                                                          --
------------------------------------------------------------------------------

with Ada.Directories;
with Ada.Environment_Variables;
with Ada.Strings.Fixed;
with Ada.Strings.Unbounded;

package body XE_Defs.Alire is

   function Get_Garlic_Prefix return String is
      use all type Ada.Directories.File_Kind;

      Found : Ada.Strings.Unbounded.Unbounded_String;

      DSA_Crate_Prefix : constant String := "DSA_RTS";
      DSA_Crate_Suffix : constant String := "_ALIRE_PREFIX";

      -----------------
      -- Find_Prefix --
      -----------------

      procedure Find_Prefix (Name, Value : String) is
         use Ada.Strings.Fixed;
      begin
         if Head (Name, DSA_Crate_Prefix'Length) = DSA_Crate_Prefix
           and then Tail (Name, DSA_Crate_Suffix'Length) = DSA_Crate_Suffix
         then
            Found := Ada.Strings.Unbounded.To_Unbounded_String (Value);
         end if;
      end Find_Prefix;

      Garlic : constant String :=
        Ada.Environment_Variables.Value ("GARLIC_ALIRE_PREFIX");
   begin
      if Garlic /= "" then
         return Garlic;
      end if;

      Ada.Environment_Variables.Iterate (Find_Prefix'Access);

      return Ada.Strings.Unbounded.To_String (Found);
   end Get_Garlic_Prefix;

end XE_Defs.Alire;
