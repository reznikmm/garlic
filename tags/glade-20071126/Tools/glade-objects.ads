------------------------------------------------------------------------------
--                                                                          --
--                              GLADE TOOLS                                 --
--                                                                          --
--                        G L A D E . O B J E C T S                         --
--                                                                          --
--                                 S p e c                                  --
--                                                                          --
--         Copyright (C) 1996-2006 Free Software Foundation, Inc.           --
--                                                                          --
-- GLADE  is free software;  you can redistribute it and/or modify it under --
-- terms of the  GNU General Public License  as published by the Free Soft- --
-- ware Foundation;  either version 2,  or (at your option)  any later ver- --
-- sion.  GLADE  is distributed  in the hope that  it will be  useful,  but --
-- WITHOUT ANY WARRANTY;  without even the implied warranty of MERCHANTABI- --
-- LITY or  FITNESS FOR A PARTICULAR PURPOSE.  See the  GNU General Public  --
-- License  for more details.  You should have received  a copy of the GNU  --
-- General Public License  distributed  with GLADE;  see file COPYING.  If  --
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
--               GLADE  is maintained by ACT Europe.                        --
--               (email: glade-report@act-europe.fr)                        --
--                                                                          --
------------------------------------------------------------------------------

package GLADE.Objects is

   pragma Remote_Types;

   type Object is abstract tagged limited private;
   type Object_Ref is access all Object'Class;

   procedure Set_Image
     (Obj : access Object;
      Img : String) is abstract;

   function Get_Image
     (Obj : access Object)
     return String is abstract;

private

   type Object is abstract tagged limited null record;

end GLADE.Objects;
