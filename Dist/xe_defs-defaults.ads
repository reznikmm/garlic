pragma Style_Checks (Off);
package XE_Defs.Defaults is
   Default_RSH_Command : constant String :=
     "rsh -n";

   Default_RSH_Options : constant String :=
     "";

   Default_PCS_Name : constant String :=
     "garlic";

   Default_Storage_Data : constant String :=
     "";

   Default_Storage_Name : constant String :=
     "dfs";

   Default_Protocol_Data : constant String :=
     "localhost:5111";

   Default_Protocol_Name : constant String :=
     "tcp";

   Default_Dist_Flags : constant String :=
     " -O0";

   Default_Prefix : constant String :=
     "/usr/share/garlic";

end XE_Defs.Defaults;
