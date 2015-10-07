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

/*$("div.graph > img").bind("load",function()
		{
			graph_init();
		}
   );*/
var configTimeout=null;
var timeoutVar=null;
var controlsVar=null;
var dashboardVar=null;
function graph_init_() {
    $('div.graph').each(function()
    		{
				var img_width = $(this).next('img').width();
				var rrd_width = parseInt($(this).css('width'));
				var left = img_width - rrd_width - 30;
				$(this).css('left', left);
				$(this).css('cursor', 'e-resize');
				$(this).attr('title', 'Click to zoom in');
    		});

   $('img.goto').css('visibility', 'visible');
   $('div.graph').imgAreaSelect({ handles: true, autoHide: true,
        fadeSpeed: 500, onSelectEnd: redirect, minHeight: '100'});
   
}

function graph_init(img) {
	var rrd_div=$(img).prev("div.graph");
	var img_width = img.width;
	var rrd_width = parseInt(rrd_div.css('width'));
	var left = img.offsetLeft+img_width - rrd_width-30;
	rrd_div.css('left', left);
	rrd_div.css('cursor', 'e-resize');
	rrd_div.attr('title', 'Click to zoom in');
		
   $('img.goto').css('visibility', 'visible');
   rrd_div.imgAreaSelect({ handles: true, autoHide: true,
        fadeSpeed: 500, onSelectEnd: redirect, minHeight: '100'});
   
}

function redirect(img, selection) {
	if (!selection.width || !selection.height)
    	return;

var graph_width = parseInt(jQuery(img).css('width'));
var link   = $(img).attr('id');
var ostart = $(img).attr('zoom_start')?Math.abs($(img).attr('zoom_start')):Math.abs($(img).attr('start'));
var oend   = $(img).attr('zoom_end')?Math.abs($(img).attr('zoom_end')):Math.abs($(img).attr('end'));
var delta  = (oend - ostart);
if( delta < 600 )
    delta = 600;
var sec_per_px = parseInt( delta / graph_width);
var start = ostart + Math.ceil( selection.x1 * sec_per_px );  
var end   = ostart + ( selection.x2 * sec_per_px );  
   // window.location = link + '/' + start + '/' + end ;
$('img[name="'+link+'"]').addClass("zoomed");
$('img[name="'+link+'"]').attr("src",link + '/' + start + '/' + end);
$(img).attr('zoom_start',start);
$(img).attr('zoom_end',end);
}

function graphZoom (g_url) {
	var start = Math.abs($('div[id="'+g_url+'"]').attr('start'));
	var end   = Math.abs($('div[id="'+g_url+'"]').attr('end'));
	GzoomWindow = window.open("", "PNP4Nagios", "width=640,height=330");
	GzoomWindow.document.write("<html><head></head>");
	var imgurl= document.baseURI+g_url+ '/' + start + '/' + end;
	
	GzoomWindow.document.write("<body><img width='100%' src='"+imgurl+"' /></body></html>");
	GzoomWindow.document.close();
	GzoomWindow.focus();
}

function graphReload (g_url) {
	var start = Math.abs($('div[id="'+g_url+'"]').attr('start'));
	var end   = Math.abs($('div[id="'+g_url+'"]').attr('end'));
	var timestamp = new Date().getTime();
	$('img[name="'+g_url+'"]').removeClass("zoomed");
	$('img[name="'+g_url+'"]').attr('src',g_url+ '/' + start + '/' + end + '?' +timestamp );
}


function graph_add_dashboard(ele,d_url)
{
	$(ele).find("img").attr("src","img/loader.gif");
	$.post(d_url,function()
			{
				$(ele).remove();
			});
}


function graph_remove(g_url)
{
	$.ajax({
		   url: g_url,
		   type: 'DELETE',
		   success: function(response) {
			   var name = g_url.replace("dashboard","graph");
			   $('div[name="'+name+'"]').remove();
		   }
		});
	
}

function graph_delete(g_url)
{
	graph_remove(g_url);
}

function graph_refresh()
{
	if($('div.graph').length==0)
		return;
/*	var timestamp = new Date().getTime();
	timestamp=Math.floor( timestamp/1000 );*/
	var s = new Date( $('#graphs_start_time').text().replace( /(\d{2})\/(\d{2})\/(\d{4}) (\d{2}):(\d{2}):(\d{2})/, "$2/$1/$3 $4:$5:$6") ).getTime()/1000+60; 
	var e = new Date( $('#graphs_end_time').text().replace( /(\d{2})\/(\d{2})\/(\d{4}) (\d{2}):(\d{2}):(\d{2})/, "$2/$1/$3 $4:$5:$6") ).getTime()/1000+60;	
	
	$('#graphs_start_time').text(new Date(s*1000).toLocaleString());
	$('#graphs_end_time').text(new Date(e*1000).toLocaleString());
	
	$('div.graph').each(function()
    		{
				var img= $(this).next('img');
				if(img.hasClass("zoomed"))
					return;
				var start=$(this).attr('start');
				var end=$(this).attr('end');
				
				var link = $(this).attr('id');
				start = parseInt(start)+60;
				end = parseInt(end)+60;
				$(this).attr('start',start);
				$(this).attr('end',end);
				link=link+"/"+start+"/"+end;
				img.attr('src',link); // + '?' +timestamp );
			});
	timeoutVar=setTimeout(function(){graph_refresh();},1000*60);
}

function graphXML(xml_url,g_url)
{
	var start = Math.abs($('div[id="'+g_url+'"]').attr('start'));
	var end   = Math.abs($('div[id="'+g_url+'"]').attr('end'));
	var timestamp = new Date().getTime();
	var hiddenIFrameID = 'hiddenDownloader',
    iframe = document.getElementById(hiddenIFrameID);
	if (iframe === null) {
	    iframe = document.createElement('iframe');
	    iframe.id = hiddenIFrameID;
	    iframe.style.display = 'none';
	    document.body.appendChild(iframe);
	}
	iframe.src = xml_url+"/"+start+"/"+end+"?"+timestamp;
}


function graphs_init(){
	$("#graphs_container-rotor.well").hide();
	$("div.graph").next("img").bind("load",function()
	{
		graph_init(this);
	}
	);
	if(timeoutVar)
		clearTimeout(timeoutVar);
	timeoutVar=setTimeout(function(){graph_refresh();},1000*60);
	$('#Graph_Menu ul.tree.closed').toggle();
	$('#Graph_Menu #tree_menu a').unbind("click");
	$('#Graph_Menu #tree_menu a').on('click',function(event)
	{
		event.preventDefault();
		var link = $(this).attr("href");
		if(link!="#" )
		{
			if($("div[name='"+link+"']").length==0)
			{
				$("#graphs_container-rotor.well").show();
				$.ajax(
						{
							"url":link,
							"success":function(data){
								$("#graphs_container-rotor.well").hide();
								if($("div[name='"+link+"']").length>0)
									return;
								$('#graphs_container-left div#graphs').append(data);
								$('div.graph').imgAreaSelect({ handles: true, autoHide: true,
								        fadeSpeed: 500, onSelectEnd: redirect, minHeight: '100'});
								$("div.graph").next("img").bind("load",function()
										{
											graph_init(this);
										});
							},
							"complete":function(){
								$("#graphs_container-rotor.well").hide();
								}
						});
			}
			else 
				$('html, body').animate({
			        scrollTop: $("div[name='"+link+"']").offset().top-60
			    }, 2000);
		}
		else 
			{
				event.preventDefault();
				$('#Graph_Menu .tree-toggle').removeClass("active");
		    	$(this).parent().children('ul.tree').toggle(200);
		    	$(this).addClass("active");
			}
    	return false;
		
	});
	
	if($('#Graphs_container form#graphs_address_selector').length>0)
		$('#Graphs_container form#graphs_address_selector').submit(function(event)
			{
				event.preventDefault();
				$("form#graphs_address_selector input.button").val("Loading...");
				graphs_panel_refresh();
				//
				return false;
				
			});
	if($('#Graphs_container form#graphs_calendar').length>0)
		$('#Graphs_container form#graphs_calendar').submit(function(event)
			{
				event.preventDefault();
				$("form#graphs_calendar input[type=submit]").val("Loading...");
				var from=$("form#graphs_calendar input#graph_calendar_from").val();
				if(from!="")
					from=Date.parse(from)/1000;
				var to=$("form#graphs_calendar input#graph_calendar_to").val();
				if(to!="")
					to=Date.parse(to)/1000;
				graphs_panel_refresh(from,to);
				//
				return false;
				
			});
};

function graphs_panel_refresh(from,to)
{
	var link=$('a#Graphs').attr("href");
	if(from!=undefined)
	{
		link=link+"/"+from;
		if(to!=undefined)
		{
			link=link+"/"+to;
		}	
	}
	if($('#Graphs_container form#graphs_address_selector').length>0)
	{
		var ip=$("form#graphs_address_selector select").val();
		link=link+"?ip="+ip;
	}
	
	$.ajax({
		//async:false,
		url:link,
		success:function(data){
			var node=$.parseHTML(data,document,true);
			//$('#Graphs_container').html($(node[5]).html());
			for(var i=0; i<node.length; i++)
			{
				if($(node[i]).attr('id')=="Graphs_container")
				{
					$('#Graphs_container').html($(node[i]).html());
					graphs_init();
					break;
				}
			}
			
		},	
		complete:function(){
			//setTimeout(function(){monitor_cmd_control_refresh();},1000*15);
		}
	});
}

function monitor_cmd_control_refresh()
{
	if($('#Controls_container').length==0 || $('#Controls_container').is(':hidden'))
	{
		return;
	}
	var link=$('a#Controls').attr("href");
	if($('#Controls_container form#monitor_address_selector').length>0)
	{
		var filter=$("form#monitor_address_selector select").val();
		link=link+"?filter="+filter;
	}
	
	$.ajax({
		//async:false,
		url:link,
		success:function(data){
			var node=$(data);
			for(var i=0; i<node.length; i++)
			{
				if($(node[i]).attr('id')=="Controls_container")
				{
					$('#Controls_container').html($(node[i]).html());
					
					break;
				}
			}
			monitor_cmd_control_init();
		},	
		complete:function(){
			//setTimeout(function(){monitor_cmd_control_refresh();},1000*15);
			
		}
	});
	
}

function monitor_cmd_link(link)
{
	
	var data="<div><p>Contacting Monitor Server ...</p>" +
			"<p><img src='img/loader.gif' /></p></div>";
	var dlg= $( "<div/>", {"class": "dialog",html: data}).dialog(
			  {
				  title:'Monitor Comand Control',
				  resizable: false,
				  modal: true,
				  buttons: {
					  Ok: function() {
					  		$( this ).dialog( "close" );
					  		},
					  	
				  },
				  close: function( event, ui ) {
					  monitor_cmd_control_refresh();
				  }
		      });
		$.getJSON( link, function( data )
		{		 
			dlg.html(data);
		});
		return false;
	
	
	
}


function _monitor_cmd_control_init()
{
	$('select.monitor_control_cmd').switchify({cssDefault:false});

	$('select.monitor_control_cmd').each(function(){
		var n=$(this);
		$(this).data('switch').bind('switch:slide', function(e, type) {
			var link=n.find("."+type).attr("text");
			monitor_cmd_link(link);
	});
	});
	if($('#Controls_container form#monitor_address_selector').length>0)
		$('#Controls_container form#monitor_address_selector').submit(function(event)
			{
				event.preventDefault();
				$("form#monitor_address_selector input.button").val("Loading...");
				monitor_cmd_control_refresh();
				//
				return true;
				
			});
}

function monitor_check_configuration(urn,id,reload)
{	
	
	if(configTimeout)
		clearTimeout(configTimeout);
	$.getJSON( "configuration/isChanged/"+urn+"/"+id, function( data )
	{		 
		if(data && reload)
		{
			location.reload();
		}
		else
			configTimeout=setTimeout(function(){monitor_check_configuration(urn,id,reload);},1000*60);
	});
	
	//setTimeout(function(){monitor_cmd_control_refresh();},1000*15);
}


function monitor_cmd_control_init()
{	
	_monitor_cmd_control_init();
	$('#Controls_container').bind("show",function(){
		monitor_cmd_control_refresh();
		});
	if(controlsVar)
		clearTimeout(controlsVar);
	controlsVar=setTimeout(function(){monitor_cmd_control_refresh();},1000*60);
	//setTimeout(function(){monitor_cmd_control_refresh();},1000*15);
}

function monitor_local_dashboard_init()
{	
	if(dashboardVar)
		clearTimeout(dashboardVar);
	dashboardVar=setTimeout(function(){
		if($('#MonitorDashboard').length==0 || $('#MonitorDashboard').is(':hidden'))
			return;
		var i = $('#MonitorDashboard_tabs ul li.active').index();
		var href = $("a#Dashboard").attr('href');
		$.get(href, function( data ) {
			$( "#configuration_data" ).html( data );
			$('#MonitorDashboard_tabs ul li').removeClass("active")
			$('#MonitorDashboard_tabs ul li').eq( i ).find("a").click();
		});			
	},1000*60);
	//setTimeout(function(){monitor_cmd_control_refresh();},1000*15);
}

$(document).ready(function(){
	$('[data-toggle="tooltip"]').tooltip();
});