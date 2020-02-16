package body System.Shared_Storage is

   ---------------------
   -- Shared_Var_Lock --
   ---------------------

   procedure Shared_Var_Lock (Var : String) is
   begin
      raise Program_Error with "Unimplemented procedure Shared_Var_Lock";
   end Shared_Var_Lock;

   -----------------------
   -- Shared_Var_Unlock --
   -----------------------

   procedure Shared_Var_Unlock (Var : String) is
   begin
      raise Program_Error with "Unimplemented procedure Shared_Var_Unlock";
   end Shared_Var_Unlock;

   ----------------------
   -- Shared_Var_Procs --
   ----------------------

   package body Shared_Var_Procs is
      pragma Unreferenced (V);
      pragma Unreferenced (Full_Name);

      ----------
      -- Read --
      ----------

      procedure Read is
      begin
         raise Program_Error with "Unimplemented procedure Read";
      end Read;

      -----------
      -- Write --
      -----------

      procedure Write is
      begin
         raise Program_Error with "Unimplemented procedure Write";
      end Write;

   end Shared_Var_Procs;

end System.Shared_Storage;
