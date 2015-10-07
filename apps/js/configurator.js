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

function cView_load(url)
{
	var panels = $("#cView_container");
	//alert(panels);
	var n = $('<div id=configuration_data_rotor><div class=well><center><img src="img/wait.gif" /> Loading...</center></div></div>').appendTo(panels);
	
	$.ajax({
		"url":url,
		"success":function(data){
			
			panels.append(data);
			
		},
		"complete":function(){
			n.remove();
		}
	
	});
	
}

function cView_init()
{
	
	$('#cView_navbar  a').not(".extern").unbind("click");
	$('#cView_navbar  a').not(".extern").on("click",function(event)
	{
		event.preventDefault();
		var id = $(this).attr("id")+"_container";
		$('#cView_navbar a').removeClass("active");
		$(this).addClass("active");
		if($("#"+id).length==0)
		{
			var url=$(this).attr("href");
			cView_load(url);
		}
	//	else
		{
			$(".cView_panel").hide();
			$("#"+id).show();
			$("#"+id).trigger("show");
		}
		
		
		return false;
	});	
}

function cView_dlg_open(url)
{
	var dlg=$("<div id=configuration_data_dlg></div>").dialog({title:"History Data",width:600,height:400,
		buttons: {
	       Close: function() {
            $( this ).dialog( "close" );
            }
		}});
	dlg.html('<div id=configuration_data_rotor><div class=well><center><img src="img/wait.gif" /> Loading...</center></div></div>');
	$.get(url, function( data ) {
		dlg.html("<div id=configuration_history_data><textarea id=history_xml>"+data+"</textarea></div>");
	});	
}

function cView_open(url)
{
	$("#configuration_data" ).hide();
	$("#configuration_container-rotor").show();
	$.get(url, function( data ) {
		$("#configuration_container-rotor").hide();
		$( "#configuration_data" ).html( data );
		$( "#configuration_data" ).show();
	});	
}

var configurationLink=null;
$(document).ready(function(){
	
	$('#cView_menu  a').not(".extern").unbind("click");
	$('#cView_menu  a').not(".extern").on("click",function(event)
	{
		event.preventDefault();
		var url=$(this).attr("href");
		cView_open(url);
		return false;
	});
	if(!configurationLink)
		$('#cView_menu a:first-child').click();
	else
	{
		$("#configuration_tree").bind("loaded.jstree",function(){
			$("a[href='"+configurationLink+"']").click();
		});
	}
});