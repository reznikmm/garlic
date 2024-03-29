------------------------------------------------------------------------------
--                                                                          --
--                            GLADE COMPONENTS                              --
--                                                                          --
--           S Y S T E M . P A R T I T I O N _ I N T E R F A C E            --
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

with Ada.Unchecked_Deallocation;
with Ada.Unchecked_Conversion;

with System.HTable;            use System.HTable;

with System.Garlic.Debug;      use System.Garlic.Debug;
with System.Garlic.Exceptions; use System.Garlic.Exceptions;
with System.Garlic.Heart;      use System.Garlic.Heart;
with System.Garlic.Options;    use System.Garlic.Options;
with System.Garlic.Partitions; use System.Garlic.Partitions;
with System.Garlic.Storages;   use System.Garlic.Storages;
with System.Garlic.Types;      use System.Garlic.Types;
with System.Garlic.Soft_Links;

with System.Garlic.Startup;
pragma Elaborate_All (System.Garlic.Startup);
pragma Warnings (Off, System.Garlic.Startup);

with System.Garlic.Utils;     use System.Garlic.Utils;

package body System.Partition_Interface is

   use Ada.Exceptions, Interfaces;
   use System.Garlic.Units;

   package SG  renames System.Garlic;

   Private_Debug_Key : constant Debug_Key :=
     Debug_Initialize ("S_PARINT", "(s-parint): ");

   procedure D
     (Message : String;
      Key     : Debug_Key := Private_Debug_Key)
     renames Print_Debug_Info;

   function Convert is
      new Ada.Unchecked_Conversion
     (RPC_Receiver, System.Address);

   procedure Setup_RAS_Proxies
     (Subprograms : RCI_Subp_Info_Array;
      Receiver    : System.Address);
   --  Initialize the Receiver and Subp_Id information stored in local
   --  RCI subprogram proxy objects.

   procedure Complete_Termination (Termination : Termination_Type);
   --  Select the correct soft link

   function Different (V1, V2 : String) return Boolean;
   --  Compare two version ids. If one of these version ids is a string
   --  of blank characters then they will be considered as identical.

   function Get_Unit_Version (Name : String; RCI : Boolean) return String;
   --  When RCI, get active version. Otherwise, get passive version.

   type Hash_Index is range 0 .. 100;
   function Hash (K : RACW_Stub_Type_Access) return Hash_Index;

   function Compare_Content (Left, Right : RACW_Stub_Type_Access)
     return Boolean;

   package Objects_HTable is
      new Simple_HTable (Header_Num => Hash_Index,
                         Element    => RACW_Stub_Type_Access,
                         No_Element => null,
                         Key        => RACW_Stub_Type_Access,
                         Hash       => Hash,
                         Equal      => Compare_Content);

   --  This is a list of caller units whose Version_ID needs to check.

   type Caller_Node;
   type Caller_List is access Caller_Node;
   type Caller_Node is
      record
         Name    : String_Access;
         Version : String_Access;
         RCI     : Boolean;
         Next    : Caller_List;
      end record;
   Callers : Caller_List;

   procedure Free is new Ada.Unchecked_Deallocation (Caller_Node, Caller_List);

   procedure Raise_Communication_Error (S : String);
   procedure Raise_Communication_Error (E : access Error_Type);
   --  Raise System.RPC.Communication_Error with an explicit error message
   --  or an Error_Type variable.

   -----------
   -- Check --
   -----------

   procedure Check
     (Name    : Unit_Name;
      Version : String;
      RCI     : Boolean := True)
   is
      Caller : constant Caller_List := new Caller_Node;
   begin
      Caller.Name    := new String'(Name);
      Caller.Version := new String'(Version);
      Caller.RCI     := RCI;
      Caller.Next    := Callers;
      Callers        := Caller;
   end Check;

   ---------------------
   -- Compare_Content --
   ---------------------

   function Compare_Content (Left, Right : RACW_Stub_Type_Access)
     return Boolean
   is
   begin
      return Left /= null and then Right /= null and then Left.all = Right.all;
   end Compare_Content;

   --------------------------
   -- Complete_Termination --
   --------------------------

   procedure Complete_Termination (Termination : Termination_Type) is
   begin
      case Termination is
         when Local_Termination  =>
            SG.Soft_Links.Local_Termination;
         when Global_Termination =>
            SG.Soft_Links.Global_Termination;
         when others => null;
      end case;
      Delete_Termination_Sanity_File;
   end Complete_Termination;

   ---------------
   -- Different --
   ---------------

   function Different (V1, V2 : String) return Boolean is

      function Not_Null_Version (V : String) return Boolean;
      --  Return true when V is not a string of blank characters

      ----------------------
      -- Not_Null_Version --
      ----------------------

      function Not_Null_Version (V : String) return Boolean is
         Null_String : constant String (V'Range) := (others => ' ');
      begin
         return V /= Null_String;
      end Not_Null_Version;

   begin
      return     Not_Null_Version (V1)
        and then Not_Null_Version (V2)
        and then V1 /= V2;
   end Different;

   ---------------------------------
   -- Elaborate_Passive_Partition --
   ---------------------------------

   procedure Elaborate_Passive_Partition
     (Partition : RPC.Partition_ID)
   is
      Error : aliased Error_Type;

   begin
      Register_Units_On_Boot_Server (Partition_ID (Partition), Error);
      if Found (Error) then
         Raise_Communication_Error (Error'Access);
      end if;
   end Elaborate_Passive_Partition;

   -----------------------------
   -- Get_Active_Partition_ID --
   -----------------------------

   function Get_Active_Partition_ID
     (Name : Unit_Name) return RPC.Partition_ID
   is
      N : String := Name;
      E : aliased Error_Type;
      P : Partition_ID;

   begin
      pragma Debug (D ("Request Get_Active_Partition_ID"));

      To_Lower (N);
      Get_Partition (Get_Unit_Id (N), P, E);
      if Found (E) then
         Raise_Communication_Error (E'Access);
      end if;

      D ("Unit " & N & " is configured on" & P'Img);

      return RPC.Partition_ID (P);
   end Get_Active_Partition_ID;

   ------------------------
   -- Get_Active_Version --
   ------------------------

   function Get_Active_Version (Name : Unit_Name) return String is
      N : String := Name;
      V : Version_Type;
      E : aliased Error_Type;
   begin
      pragma Debug (D ("Request Get_Active_Version"));

      To_Lower (N);
      Get_Version (Get_Unit_Id (N), V, E);
      if Found (E) then
         Raise_Communication_Error (E'Access);
      end if;

      return String (V);
   end Get_Active_Version;

   ----------------------------
   -- Get_Local_Partition_ID --
   ----------------------------

   function Get_Local_Partition_ID return RPC.Partition_ID is
   begin
      return RPC.Partition_ID (Self_PID);
   end Get_Local_Partition_ID;

   ------------------------
   -- Get_Partition_Name --
   ------------------------

   function Get_Partition_Name (Partition : Integer) return String is
      Name  : String_Access;
      Error : aliased Error_Type;
   begin
      SG.Partitions.Get_Name (Partition_ID (Partition), Name, Error);
      if Found (Error) then
         Raise_Communication_Error (Error'Access);
      end if;
      return Name.all;
   end Get_Partition_Name;

   ------------------------------
   -- Get_Passive_Partition_ID --
   ------------------------------

   function Get_Passive_Partition_ID
     (Name : Unit_Name) return RPC.Partition_ID is
   begin
      return Get_Active_Partition_ID (Name);
   end Get_Passive_Partition_ID;

   -------------------------
   -- Get_Passive_Version --
   -------------------------

   function Get_Passive_Version (Name : Unit_Name) return String is
   begin
      return Get_Active_Version (Name);
   end Get_Passive_Version;

   ------------------
   -- Get_RAS_Info --
   ------------------

   procedure Get_RAS_Info
     (Name          : Unit_Name;
      Subp_Id       : Subprogram_Id;
      Proxy_Address : out Interfaces.Unsigned_64)
   is
      subtype U64 is Interfaces.Unsigned_64;

      Subp_Info : RCI_Subp_Info_Access;
      Unit      : constant Unit_Id := Get_Unit_Id (Name);
      Origin    : Partition_ID;
      Error     : Error_Type;
   begin
      Proxy_Address := U64 (System.Null_Address);

      Get_Partition (Unit, Origin, Error);
      if Found (Error) then
         raise Constraint_Error;
      end if;
      if Origin = Self_PID then
         Subp_Info := Get_Subprogram_Info (Unit, Subp_Id);
         if Subp_Info /= null then
            Proxy_Address := U64 (Subp_Info.Addr);
         end if;
      else
         declare
            use System.RPC;

            Receiver : U64;
            Error : Error_Type;

            Params  : aliased Params_Stream_Type (0);
            Returns : aliased Params_Stream_Type (0);
            Except  : Ada.Exceptions.Exception_Occurrence;
         begin
            Get_Receiver (Unit, Receiver, Error);
            if Found (Error) then
               raise Constraint_Error;
            end if;

            U64'Write (Params'Access, Receiver);
            Subprogram_Id'Write (Params'Access, 1);
            --  Special subprogram id: Lookup RAS information

            Subprogram_Id'Write (Params'Access, Subp_Id);

            Do_RPC (RPC.Partition_ID (Origin), Params'Access, Returns'Access);
            Ada.Exceptions.Exception_Occurrence'Read (Returns'Access, Except);
            Ada.Exceptions.Reraise_Occurrence (Except);
            U64'Read (Returns'Access, Proxy_Address);
         end;
      end if;
   end Get_RAS_Info;

   ------------------------------
   -- Get_RCI_Package_Receiver --
   ------------------------------

   function Get_RCI_Package_Receiver (Name : Unit_Name) return Unsigned_64 is
      N : String := Name;
      R : Unsigned_64;
      E : aliased Error_Type;
   begin
      pragma Debug (D ("Request Get_Package_Receiver"));

      To_Lower (N);
      Get_Receiver (Get_Unit_Id (N), R, E);
      if Found (E) then
         Raise_Communication_Error (E'Access);
      end if;

      return R;
   end Get_RCI_Package_Receiver;

   -------------------------------
   -- Get_Unique_Remote_Pointer --
   -------------------------------

   procedure Get_Unique_Remote_Pointer
     (Handler : in out RACW_Stub_Type_Access)
   is
      Answer : RACW_Stub_Type_Access;
   begin
      System.Garlic.Soft_Links.Enter_Critical_Section;
      Answer := Objects_HTable.Get (Handler);
      if Answer = null then
         Answer := new RACW_Stub_Type'(Handler.all);
         Objects_HTable.Set (Answer, Answer);
      end if;
      Handler := Answer;
      System.Garlic.Soft_Links.Leave_Critical_Section;
   end Get_Unique_Remote_Pointer;

   ----------------------
   -- Get_Unit_Version --
   ----------------------

   function Get_Unit_Version (Name : String; RCI : Boolean) return String is
   begin
      if RCI then
         return Get_Active_Version (Name);
      else
         return Get_Passive_Version (Name);
      end if;
   end Get_Unit_Version;

   ----------
   -- Hash --
   ----------

   function Hash (K : RACW_Stub_Type_Access) return Hash_Index is
   begin
      return
        Hash_Index (K.Addr mod Interfaces.Unsigned_64 (Hash_Index'Last + 1));
   end Hash;

   -------------------------------
   -- Raise_Communication_Error --
   -------------------------------

   procedure Raise_Communication_Error (S : String) is
   begin
      Ada.Exceptions.Raise_Exception (RPC.Communication_Error'Identity, S);
   end Raise_Communication_Error;

   -------------------------------
   -- Raise_Communication_Error --
   -------------------------------

   procedure Raise_Communication_Error (E : access Error_Type) is
   begin
      Raise_Communication_Error (Content (E));
   end Raise_Communication_Error;

   ------------------------------------
   -- Raise_Program_Error_For_E_4_18 --
   ------------------------------------

   procedure Raise_Program_Error_For_E_4_18 is
   begin
      Ada.Exceptions.Raise_Exception (Program_Error'Identity,
        "Illegal usage of remote access to class-wide type. See RM E.4(18)");
   end Raise_Program_Error_For_E_4_18;

   -------------------------------------
   -- Raise_Program_Error_Unknown_Tag --
   -------------------------------------

   procedure Raise_Program_Error_Unknown_Tag
     (E : Ada.Exceptions.Exception_Occurrence)
   is
   begin
      Ada.Exceptions.Raise_Exception
        (Program_Error'Identity, Ada.Exceptions.Exception_Message (E));
   end Raise_Program_Error_Unknown_Tag;

   ------------------------------
   -- Register_Passive_Package --
   ------------------------------

   procedure Register_Passive_Package
     (Name    : Unit_Name;
      Version : String := "")
   is
      N : String       := Name;
      V : constant Version_Type := Version_Type (Version);
      E : aliased Error_Type;

   begin
      To_Lower (N);

      pragma Debug (D ("Register local shared passive unit " & N));

      Register_Unit (Self_PID, N, 0, V, System.Null_Address, 0);
      Register_Package (N, Self_PID, E);
      if Found (E) then
         Raise_Communication_Error (E'Access);
      end if;
   end Register_Passive_Package;

   ---------------------------------------------------
   -- Register_Passive_Package_On_Passive_Partition --
   ---------------------------------------------------

   procedure Register_Passive_Package_On_Passive_Partition
     (Partition : RPC.Partition_ID;
      Name      : Unit_Name;
      Version   : String := "")
     is
      N : String       := Name;
      V : constant Version_Type := Version_Type (Version);
      P : constant Partition_ID := Partition_ID (Partition);

   begin
      To_Lower (N);

      pragma Debug (D ("Register " & N & " on passive partition" & P'Img));

      Register_Unit (P, N, 0, V, System.Null_Address, 0);
   end Register_Passive_Package_On_Passive_Partition;

   --------------------------------
   -- Register_Passive_Partition --
   --------------------------------

   procedure Register_Passive_Partition
     (Partition : out RPC.Partition_ID;
      Name      : String;
      Location  : String)
   is
      Error : aliased Error_Type;

   begin
      Register_Passive_Partition
        (Partition_ID (Partition), Name, Location, Error);
      if Found (Error) then
         Raise_Communication_Error (Error'Access);
      end if;

      Register_Partition (Partition_ID (Partition), Location, Error);
      if Found (Error) then
         Raise_Communication_Error (Error'Access);
      end if;
   end Register_Passive_Partition;

   -----------------------------
   -- Register_Receiving_Stub --
   -----------------------------

   procedure Register_Receiving_Stub
     (Name          : Unit_Name;
      Receiver      : RPC_Receiver;
      Version       : String := "";
      Subp_Info     : System.Address;
      Subp_Info_Len : Integer)
   is
      Receiver_Address : constant System.Address :=
        Convert (Receiver);
      N : String       := Name;
      V : constant Version_Type := Version_Type (Version);
      R : constant Unsigned_64  := Unsigned_64 (Receiver_Address);

      subtype Subprogram_Array is RCI_Subp_Info_Array
        (System.Garlic.Units.First_RCI_Subprogram_Id ..
         System.Garlic.Units.First_RCI_Subprogram_Id + Subp_Info_Len - 1);
      Subprograms : Subprogram_Array;
      for Subprograms'Address use Subp_Info;
      pragma Import (Ada, Subprograms);
   begin
      pragma Debug (D ("Register receiving stub"));

      To_Lower (N);
      Register_Unit (Self_PID, N, R, V, Subp_Info, Subp_Info_Len);
      Setup_RAS_Proxies (Subprograms, Receiver_Address);
   end Register_Receiving_Stub;

   -----------------
   -- RCI_Locator --
   -----------------

   package body RCI_Locator is

      Name : String := RCI_Name;
      Unit : Unit_Id;
      Init : Boolean := False;

      -----------------------------
      -- Get_Active_Partition_ID --
      -----------------------------

      function Get_Active_Partition_ID return RPC.Partition_ID is
         Partition : Partition_ID;
         Error     : aliased Error_Type;
      begin
         if not Init then
            Init := True;
            To_Lower (Name);
            Unit := Get_Unit_Id (Name);
         end if;
         Get_Partition (Unit, Partition, Error);
         if Found (Error) then
            Raise_Communication_Error (Error'Access);
         end if;
         return RPC.Partition_ID (Partition);
      end Get_Active_Partition_ID;

      ------------------------------
      -- Get_RCI_Package_Receiver --
      ------------------------------

      function Get_RCI_Package_Receiver return Unsigned_64 is
         Receiver : Unsigned_64;
         Error    : aliased Error_Type;
      begin
         if not Init then
            Init := True;
            To_Lower (Name);
            Unit := Get_Unit_Id (Name);
         end if;
         Get_Receiver (Unit, Receiver, Error);
         if Found (Error) then
            Raise_Communication_Error (Error'Access);
         end if;
         return Receiver;
      end Get_RCI_Package_Receiver;

   end RCI_Locator;

   ---------
   -- Run --
   ---------

   procedure Run (Main : Main_Subprogram_Type := null) is
      Caller   : Caller_List := Callers;
      Dummy    : Caller_List;
      Error    : aliased Error_Type;
      Pkg_Data : Shared_Data_Access;

   begin
      SG.Heart.Complete_Elaboration;
      D ("Complete elaboration");

      Register_Units_On_Boot_Server (Self_PID, Error);
      if Found (Error) then
         Raise_Communication_Error (Error'Access);
      end if;

      RPC.Establish_RPC_Receiver (RPC.Partition_ID (Self_PID), null);

      while Caller /= null loop
         D ("Check " & Caller.Name.all & " version consistency");
         if Different (Caller.Version.all,
                       Get_Unit_Version (Caller.Name.all, Caller.RCI))
         then

            --  If not boot partition, then terminate without waiting for
            --  boot partition request.

            if not Is_Boot_Server then
               Set_Termination (Local_Termination);
            end if;

            Ada.Exceptions.Raise_Exception
              (Program_Error'Identity,
               "Versions differ for unit """ &
               Caller.Name.all & """");

         end if;

         --  For shared passive units, check that their storage support
         --  has been correctly setup.

         if not Caller.RCI then
            Lookup_Package (Caller.Name.all, Pkg_Data, Error);
            if Found (Error)  then
               Ada.Exceptions.Raise_Exception
                 (Program_Error'Identity, Content (Error'Access));
            end if;
         end if;

         Destroy (Caller.Version);
         Destroy (Caller.Name);
         Dummy  := Caller;
         Caller := Caller.Next;
         Free (Dummy);
      end loop;

      D ("Execute partition main subprogram");
      if Main /= null then
         Main.all;
      end if;

      D ("Watch for termination");
      Complete_Termination (SG.Options.Termination);
   exception
      when Occurrence : others =>
         D ("Handle exception " & Exception_Name (Occurrence) &
            " in partition main subprogram");
      Complete_Termination (SG.Options.Termination);
      raise;
   end Run;

   --------------------
   -- Same_Partition --
   --------------------

   function Same_Partition
      (Left  : access RACW_Stub_Type;
       Right : access RACW_Stub_Type) return Boolean
   is
      use type System.RPC.Partition_ID;
   begin
      return Left.Origin = Right.Origin;
   end Same_Partition;

   -----------------------
   -- Setup_RAS_Proxies --
   -----------------------

   procedure Setup_RAS_Proxies
     (Subprograms : RCI_Subp_Info_Array;
      Receiver    : System.Address)
   is
      function To_Proxy is
         new Ada.Unchecked_Conversion (System.Address, RAS_Proxy_Type_Access);
   begin
      for Subp_Id in Subprograms'Range loop
         To_Proxy (Subprograms (Subp_Id).Addr).Receiver := Receiver;
         To_Proxy (Subprograms (Subp_Id).Addr).Subp_Id  :=
           Subprogram_Id (Subp_Id);
      end loop;
   end Setup_RAS_Proxies;

end System.Partition_Interface;
