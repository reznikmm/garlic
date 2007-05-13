------------------------------------------------------------------------------
--                                                                          --
--                            GLADE COMPONENTS                              --
--                                                                          --
--           S Y S T E M . G A R L I C . S T O R A G E S . D F S            --
--                                                                          --
--                                 S p e c                                  --
--                                                                          --
--         Copyright (C) 1996-2006 Free Software Foundation, Inc.           --
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
--
--
--
--
--
--
--
--               GLADE  is maintained by ACT Europe.                        --
--               (email: glade-report@act-europe.fr)                        --
--                                                                          --
------------------------------------------------------------------------------

with Ada.Streams;
with Ada.Streams.Stream_IO;

with GNAT.OS_Lib;

with System.Global_Locks;
with System.Garlic.Exceptions;
with System.Garlic.Soft_Links;

package System.Garlic.Storages.Dfs is

   package SIO renames Ada.Streams.Stream_IO;

   package SGL renames System.Global_Locks;
   package SGS renames System.Garlic.Soft_Links;

   package OS renames GNAT.OS_Lib;

   type DFS_Data_Type is new Shared_Data_Type with private;

   --  Management subprograms

   procedure Create_Storage
     (Master   : in out DFS_Data_Type;
      Location : String;
      Storage  : out Shared_Data_Access;
      Error    : in out Exceptions.Error_Type);

   procedure Create_Package
     (Storage  : in out DFS_Data_Type;
      Pkg_Name : String;
      Pkg_Data : out    Shared_Data_Access;
      Error    : in out Exceptions.Error_Type);

   procedure Create_Variable
     (Pkg_Data : in out DFS_Data_Type;
      Var_Name : String;
      Var_Data : out    Shared_Data_Access;
      Error    : in out Exceptions.Error_Type);

   procedure Initialize;

   procedure Initiate_Request
     (Var_Data : access DFS_Data_Type;
      Request  : Request_Type;
      Success  : out    Boolean);

   procedure Complete_Request
     (Var_Data : access DFS_Data_Type);

   procedure Shutdown (Storage : DFS_Data_Type);

   procedure Read
     (Data : in out DFS_Data_Type;
      Item : out Ada.Streams.Stream_Element_Array;
      Last : out Ada.Streams.Stream_Element_Offset);

   procedure Write
     (Data : in out DFS_Data_Type;
      Item : Ada.Streams.Stream_Element_Array);

private

   type DFS_Data_Access is access DFS_Data_Type;

   type DFS_Data_Type is
     new Shared_Data_Type with
      record
         Name  : OS.String_Access;
         File  : SIO.File_Type;
         Lock  : SGL.Lock_Type;
         Mutex : SGS.Adv_Mutex_Access;
         Count : Natural;
         Dir   : OS.String_Access;
         Prev  : DFS_Data_Access;
         Next  : DFS_Data_Access;
         Self  : DFS_Data_Access;
      end record;

end System.Garlic.Storages.Dfs;
