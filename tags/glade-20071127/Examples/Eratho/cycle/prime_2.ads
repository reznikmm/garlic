with Common; use Common;

package Prime_2 is
   pragma Remote_Call_Interface;

   procedure Test_Primarity (Number  : in  Natural);
   pragma Asynchronous (Test_Primarity);

end Prime_2;
