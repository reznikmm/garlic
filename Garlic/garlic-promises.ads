package Garlic.Promises is
   pragma Preelaborate;

   type Abstract_Promise is limited interface;

   type Promise_State is (Pending, Resolved, Rejected);

   type Abstract_Listener is limited interface;
   type Abstract_Listener_Access is
     access all Abstract_Listener'Class with Storage_Size => 0;

   not overriding procedure Set_Listener
     (Self  : in out Abstract_Promise;
      Value : not null Abstract_Listener_Access) is abstract;

   not overriding procedure On_Settle
     (Self : in out Abstract_Listener) is abstract;

   not overriding function State
     (Self : Abstract_Promise) return Promise_State is abstract;

   generic
      type Element is private;
   package Generic_Promises is
      type Listener is limited interface;
      --  Listener on stream promise
      type Listener_Access is access all Listener'Class with Storage_Size => 0;

      not overriding procedure On_Resolve
        (Self  : in out Listener;
         Value : Element) is abstract;
      --  The promise is resolved and Value is ready.

      not overriding procedure On_Reject
        (Self : in out Listener) is abstract;
      --  The promise is rejected.

      type Promise is new Abstract_Promise with private;
      --  Promise to get response from the local or remote partition.

      not overriding procedure Add_Listener
        (Self  : in out Promise;
         Value : not null Listener_Access);
      --  Let a listener to be notified when the promise is settled

      not overriding procedure Resolve
        (Self  : in out Promise;
         Value : Element)
        with Pre => Self.State = Pending,
        Post => Self.State = Resolved;
      --  Resolve the promise and notify listeners

      not overriding procedure Reject (Self : in out Promise)
        with Pre => Self.State = Pending,
        Post => Self.State = Rejected;
      --  Reject the promise. This will notify the listener

      overriding function State (Self : Promise) return Promise_State;

   private
      type List_Node;
      type List_Node_Access is access List_Node;

      type List_Node is record
         Item : not null Listener_Access;
         Next : List_Node_Access;
      end record;

      type Promise_Data (State : Promise_State := Pending) is record
         case State is
            when Pending =>
               Listeners : List_Node_Access;
               Callback  : Abstract_Listener_Access;
            when Resolved =>
               Value     : Element;
            when Rejected =>
               null;
         end case;
      end record;

      type Promise is new Abstract_Promise with record
         Data : Promise_Data;
      end record;

      overriding procedure Set_Listener
        (Self  : in out Promise;
         Value : not null Abstract_Listener_Access);

   end Generic_Promises;

end Garlic.Promises;
