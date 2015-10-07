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

$(document).ready(function()
		{
	if($('#NagiosDaemonSettings form#settings').length>0)
		$('#NagiosDaemonSettings form#settings').submit(function(event)
			{
				event.preventDefault();
				var label = $("form#settings input.button").val();
				$("form#settings input.button").val("Saving...");
				var postData = $(this).serializeArray();
			    var formURL = $(this).attr("action");
			    $.ajax(
			    {
			        url : formURL,
			        type: "POST",
			        data : postData,
			        success:function(data, textStatus, jqXHR) 
			        {
			        	$("form#settings input.button").removeAttr("disabled");
			        	$("form#settings input.button").val(label);
			        	$(".message").hide();
			        	$(".message").html("<div class=\"alert alert-info\" role=\"alert\">"+data.result+"</div>");
			        	$(".message").show().animate({opacity: 1.0}, 3000).fadeOut(1000);
			        },
			        error: function(jqXHR, textStatus, errorThrown) 
			        {
			            //if fails      
			        }
			    });
				return true;
				
			});
		});