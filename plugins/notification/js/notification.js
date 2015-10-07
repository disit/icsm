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

 var unseen_id_array = new Array();  
 function CheckUpdates()  
 {  
   jQuery.ajax({url:"notification/update", dataType:"json", success: function(msg) {  
       if (msg) {  
         //var result = msg.split("|");  
         var unseen = parseInt(msg.newcount);  
         var notifications = msg.html;  
         var unseen_ids =  msg.unseen_ids;  
         if (unseen > 0) {  
           $('#notification-badge').css("display", "inline");  
           $('#notification-badge').html(unseen);  
           for (var i = 0; i < unseen_ids.length; i++) {  
             unseen_id_array.push(unseen_ids[i]);  
           }  
         }  
         $('#notifications').html(notifications);  
       } else {
    	   $('#notifications').html("<b>&nbsp;&nbsp;No notifications...</b>");
    	   }  
     }  
   }); 
 }  
 
 function DeleteNotification(id) {  
	 jQuery.ajax({url:"notification/delete",  type:"POST", data:JSON.stringify(id), dataType:"json", success: function(msg) {  
       $("#notification_"+id).remove();  
       if($('#notifications li').length==0)
    	   $('#notifications').html("<b>&nbsp;&nbsp;No notifications...</b>");
     }  
   });  
 }  
 
 function DeleteTBWSelectedNotification(id,endCallback) { 
	 jQuery.ajax({url:"notification/delete",  type:"POST", data:JSON.stringify(id), dataType:"json", success: function(msg) 
	 {  
		 if(endCallback!=undefined)
		 {
			 endCallback("Deleted item "+id);
		 }
     }  
	});  
 }  
 
 function SeenNotification() {  
	if(unseen_id_array.length>0)
	{
	   jQuery.ajax({url:"notification/seen", type:"POST", data:JSON.stringify(unseen_id_array), dataType:"json", success: function(msg) {  
	       setTimeout(function() {  
	         $('#notification-badge').css("display", "none");  
	         $('#notification-badge').html("");  
	         unseen_id_array=[];
	       },1000);  
	     }  
	   });  
	}
 }  
 
 function ViewNotification(id) {  
		if(id!=undefined)
		{
		   jQuery.ajax({url:"notification/view", type:"POST", data:JSON.stringify(id), dataType:"json", success: function(data) {  
			   bootbox.dialog({
				   title: "Alert",
				   message: data.message,
				   id:"NotificationDialog"
				 });
		     }  
		   });  
		}
	 }  
 
 $(document).ready(function() {  
   $('.notifications').click(function() {  
     //TODO: stop CheckUpdates interval and restart menu closes  
     SeenNotification();  
   });  
   $('.dropdown-menu').click(function(event){  
      event.stopPropagation();  
   });  
   CheckUpdates();  
   var intervalId = setInterval(CheckUpdates,60000); 
 });  
  