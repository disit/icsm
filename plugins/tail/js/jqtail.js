/*   Icaro Supervisor & Monitor (ICSM).
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
   Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.*/

var tailRefreshUrl='tail/refresh';


function pageScroll() {
       // document.getElementById('tailLogTexto').scrollTop=10000; // horizontal and vertical scroll increments
	var n = $("#tailLogTexto")[0].scrollHeight - $("#tailLogTexto").height();
    $("#tailLogTexto").scrollTop(n);
}



function blink(){
   if($("#tailLogCursor")){
	   	var cur = $("#tailLogCursor").html();
	    if (cur == "_") 
	    	$("#tailLogCursor").html("");
	    else 
	    	$("#tailLogCursor").html("_");
   }
   setTimeout('blink()',500);
}


function tailUpdate()
{
	$.ajax({
		dataType:'json',
		url:tailRefreshUrl,
		success:function(data){
			 if(data.refresh){   
				 $("#tailLogTexto").html(data.content);
		          pageScroll();
		        }
			 setTimeout("tailUpdate()",1000);
		}
		
	});
}

$(document).ready(function(){
	setTimeout("tailUpdate()",100);
});
