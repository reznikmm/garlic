------------------------------------------------------------------------------
--                                                                          --
--                            GLADE COMPONENTS                              --
--                                                                          --
--             S Y S T E M . G A R L I C . E X C E P T I O N S              --
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

with Ada.Exceptions;      use Ada.Exceptions;

with System.Garlic.Debug; use System.Garlic.Debug;
pragma Elaborate (System.Garlic.Debug);

package body System.Garlic.Exceptions is

   Private_Debug_Key : constant Debug_Key :=
     Debug_Initialize ("S_GAREXC", "(s-garexc): ");

   procedure D
     (Message : String;
      Key     : Debug_Key := Private_Debug_Key)
     renames Print_Debug_Info;

   -----------
   -- Catch --
   -----------

   procedure Catch (Error : in out Error_Type) is
   begin
      if Error /= null then
         pragma Debug (D ("*** Catch *** " & Error.all));
         Free (Error);
      end if;
   end Catch;

   -------------
   -- Content --
   -------------

   function Content (Error : access Error_Type) return String is
      pragma Assert (Error.all /= null);
      Error_Message : constant String := Error.all.all;
   begin
      Free (Error.all);
      return Error_Message;
   end Content;

   -----------
   -- Found --
   -----------

   function Found (Error : Error_Type) return Boolean is
   begin
      return Error /= null;
   end Found;

   -------------------------------
   -- Raise_Communication_Error --
   -------------------------------

   procedure Raise_Communication_Error (Error : in out Error_Type) is
      pragma Assert (Error /= null);
      Error_Message : constant String := Error.all;
   begin
      Free (Error);
      Raise_Exception (Communication_Error'Identity, Error_Message);
   end Raise_Communication_Error;

   -------------------------------
   -- Raise_Communication_Error --
   -------------------------------

   procedure Raise_Communication_Error (Msg : String := "") is
   begin
      Raise_Exception (Communication_Error'Identity, Msg);
   end Raise_Communication_Error;

   -----------
   -- Throw --
   -----------

   procedure Throw (Error : in out Error_Type; Message : String) is
   begin
      if Error /= null then
         pragma Debug (D ("*** Abort *** " & Error.all));

         Free (Error);
      end if;

      Error := new String'(Message);

      pragma Debug (D ("*** Throw *** " & Error.all));
   end Throw;

end System.Garlic.Exceptions;
