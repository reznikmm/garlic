with Ada.Unchecked_Deallocation;

package body Garlic.Promises is

   package body Generic_Promises is

      procedure Free is new Ada.Unchecked_Deallocation
        (List_Node, List_Node_Access);

      function Has_Listener
        (Self  : Promise'Class;
         Value : not null Listener_Access) return Boolean;

      ------------------
      -- Add_Listener --
      ------------------

      not overriding procedure Add_Listener
        (Self  : in out Promise;
         Value : not null Listener_Access) is
      begin
         case Self.Data.State is
            when Pending =>
               if not Self.Has_Listener (Value) then
                  Self.Data.Listeners := new List_Node'
                    (Item => Value,
                     Next => Self.Data.Listeners);
               end if;
            when Resolved =>
               Value.On_Resolve (Self.Data.Value);
            when Rejected =>
               Value.On_Reject;
         end case;
      end Add_Listener;

      ------------------
      -- Has_Listener --
      ------------------

      function Has_Listener
        (Self  : Promise'Class;
         Value : not null Listener_Access) return Boolean
      is
         Next : List_Node_Access := Self.Data.Listeners;
      begin
         while Next /= null loop
            if Next.Item = Value then
               return True;
            end if;

            Next := Next.Next;
         end loop;

         return False;
      end Has_Listener;

      -------------
      -- Resolve --
      -------------

      not overriding procedure Resolve
        (Self : in out Promise; Value : Element)
      is
         Next : List_Node_Access := Self.Data.Listeners;
      begin
         if Self.Data.Callback /= null then
            Self.Data.Callback.On_Settle;
         end if;

         Self.Data := (Resolved, Value);

         while Next /= null loop
            declare
               Item : List_Node_Access := Next;
            begin
               Item.Item.On_Resolve (Self.Data.Value);
               Next := Item.Next;
               Free (Item);
            end;
         end loop;
      end Resolve;

      ------------
      -- Reject --
      ------------

      not overriding procedure Reject (Self : in out Promise) is
         Next : List_Node_Access := Self.Data.Listeners;
      begin
         if Self.Data.Callback /= null then
            Self.Data.Callback.On_Settle;
         end if;

         Self.Data := (State => Rejected);

         while Next /= null loop
            declare
               Item : List_Node_Access := Next;
            begin
               Item.Item.On_Reject;
               Next := Item.Next;
               Free (Item);
            end;
         end loop;
      end Reject;

      overriding procedure Set_Listener
        (Self  : in out Promise;
         Value : not null Abstract_Listener_Access) is
      begin
         case Self.Data.State is
            when Pending =>
               if Self.Data.Callback = null then
                  Self.Data.Callback := Value;
               else
                  raise Constraint_Error with "Listener has been set already";
               end if;
            when Resolved =>
               Value.On_Settle;
            when Rejected =>
               Value.On_Settle;
         end case;
      end Set_Listener;

      -----------
      -- State --
      -----------

      overriding function State (Self : Promise) return Promise_State is
      begin
         return Self.Data.State;
      end State;

   end Generic_Promises;
end Garlic.Promises;
