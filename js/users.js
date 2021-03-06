/* Icaro Supervisor & Monitor (ICSM).
   Copyright (C) 2015 DISIT Lab http://www.disit.org - University of Florence

   This program is free software; you can redistribute it and/or
   modify it under the terms of the GNU General Public License
   as published by the Free Software Foundation; either version 2
   of the License, or (at your option) any later version.
   This program is distributed in the hope that it will be useful,
   but WITHOUT ANY WARRANTY; without even the implied warranty of
   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
   GNU General Public License for more details.
   You should have received a copy of the GNU General Public License
   along with this program; if not, write to the Free Software
   Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA. */

function DeleteTBWSelectedUser(id,endCallback) { 
	 jQuery.ajax({url:"user/delete",  type:"POST", data:id, dataType:"json", success: function(msg) 
	 {  
		 if(endCallback!=undefined)
		 {
			 endCallback("Deleted user "+id);
		 }
     }  
	});  
 }  
 
 function BanTBWSelectedUser(id,endCallback) { 
	 jQuery.ajax({url:"user/ban",  type:"POST", data:id, dataType:"json", success: function(msg) 
	 {  
		 if(endCallback!=undefined)
		 {
			 endCallback("Banned user "+id);
		 }
     }  
	});  
 }  
 
 function ActivateTBWSelectedUser(id,endCallback) { 
	 jQuery.ajax({url:"user/activate",  type:"POST", data:id, dataType:"json", success: function(msg) 
	 {  
		 if(endCallback!=undefined)
		 {
			 endCallback("Activated user "+id);
		 }
     }  
	});  
 }  