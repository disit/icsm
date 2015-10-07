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

var meterTimeoutVar=null;
function meters_init()
{
	var maxheight = Math.max.apply( null, $( '#Meters_container .meters-group .ui-widget-header' ).map( function () {
	    return $( this ).outerHeight( true );
	}).get() );
	
	$( '#Meters_container .meters-group .ui-widget-header' ).css("height",maxheight);
	
/*	$("#Meters_container .gauge").each(function()
			{
				var name = $(this).attr("id");
				var min =$(this).attr("min")/1;
				var max = $(this).attr("max")/1;
				var warn = $(this).attr("warning")/100;
				var crit = $(this).attr("critical")/100;
				var label = $(this).attr("unit");
				var value = $(this).attr("value")/1.;
				meters_createGauge(name, label, min, max, warn, crit, value);
				
			});*/
	/*$(".value").each(function()
			{
				var name = $(this).attr("id");
				var min =$(this).attr("min")/1;
				var max = $(this).attr("max")/1;
				var warn = $(this).attr("warning")/100;
				var crit = $(this).attr("critical")/100;
				var label = $(this).attr("unit");
				var value = $(this).attr("value")/1;
				//var ndigit = $(this).attr("value").length;
				//$(this).sevenSegArray({digits:ndigit,value:value});
			
				
			});*/
	if($('#Meters_container form#meters_address_selector').length>0)
		$('#Meters_container form#meters_address_selector').submit(function(event)
			{
				event.preventDefault();
				$("form#meters_address_selector input.button").val("Loading...");
				meters_reload();
				//
				return true;
				
			});
	$('#Meters_container').bind("show",function(){
		meters_refresh();
		});
	if(meterTimeoutVar)
		clearTimeout(meterTimeoutVar);
	meterTimeoutVar=setTimeout(function(){meters_refresh();},1000*10);
}

function meters_reload()
{
	var _url=$('a#Meters').attr("href");
	if($('#Meters_container form#meters_address_selector').length>0)
	{
		var filter=$("form#meters_address_selector select").val();
		_url=_url+"?filter="+filter;
	}
	
	$.ajax({
		//async:false,
		url:_url,
		success:function(data){
			
			var node=$(data);
			for(var i=0; i<node.length; i++)
			{
				if($(node[i]).attr('id')=="Meters_container")
				{
					$('#Meters_container').html($(node[i]).html());
					_monitor_cmd_control_init();
					break;
				}
			}
			meters_init();
		},	
		complete:function(){
			//setTimeout(function(){monitor_cmd_control_refresh();},1000*15);
		}
	});
}

function meters_refresh()
{
	if($('#Meters_container').length==0 || $('#Meters_container').is(':hidden'))
	{
		//setTimeout(function(){meters_refresh();},1000*10);
		return;
	}
	var url=refreshUrl;
	if($('#Meters_container form#Meters_container').length>0)
	{
		var filter=$("form#Meters_container select").val();
		url=url+"?filter="+filter;
	}
	$.getJSON(url,function(data)
		{
			var time = new Date(data.time*1000);
			var meters = data.meters;
			$('#meters_time').html(meters_formatDate(time));
			for(var i in meters)
			{
				meters_updateGauge(meters[i].id,meters[i].value);
			}
			meterTimeoutVar=setTimeout(function(){meters_refresh();},1000*10);
		});
}

var gauges = [];

function meters_createGauge(name, label, min, max, warn, crit, value)
{
	var config = 
	{
		value:value,
		size: 120,
		label: label,
		min: undefined != min ? min : 0,
		max: undefined != max ? max : 100,
		warn: undefined != warn ? warn : 0.75,
		crit: undefined != crit ? crit : 0.90,
		minorTicks: 5
	};
	
	var range = config.max - config.min;
	if(config.warn<config.crit)
	{
		config.yellowZones = [{ from: config.min + range*config.warn, to: config.min + range*config.crit }];
		config.redZones = [{ from: config.min + range*config.crit, to: config.max }];
	}
	else
	{
		config.yellowZones = [{ from: range*config.crit, to: config.min + range*config.warn }];
		config.redZones = [{ from: config.min , to: range*config.crit }];
	}
	gauges[name] = new Gauge(name, config);
	gauges[name].render();
	gauges[name].redraw(value);
}

function meters_updateGauge(name,value)
{
	
	if(gauges[name]!=undefined && gauges[name].config.value!=value)
	{
		
		gauges[name].config.value=value;
		gauges[name].redraw(value);
		originalColor = $('#'+name).css("backgroundColor");
		$('#'+name).css("backgroundColor","red");
		//$('#'+name).animate({backgroundColor:"red"},2000,function(){$('#'+name).css("backgroundColor","fff");});
		setTimeout(function(){
			$('#'+name).css("backgroundColor", originalColor);
			  }, 2000);
	}
}


function meters_formatDate(d) {

	  var dd = d.getDate();
	  if ( dd < 10 ) dd = '0' + dd;

	  var mm = d.getMonth()+1;
	  if ( mm < 10 ) mm = '0' + mm;

	  var yy = d.getFullYear() % 100;
	  if ( yy < 10 ) yy = '0' + yy;

	  var hh = d.getHours();
	  if ( hh < 10 ) hh = '0' + hh;
	  
	  var MM = d.getMinutes();
	  if ( MM < 10 ) MM = '0' + MM;
	
	  var ss = d.getSeconds();
	  if ( ss < 10 ) ss = '0' + ss;
	  
	  return dd+'/'+mm+'/'+yy+" "+hh+":"+MM+":"+ss;
	}