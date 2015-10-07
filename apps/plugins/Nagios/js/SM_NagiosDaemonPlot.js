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

var plot={};
var plotdata={};
var plotRefreshUrl="nagios/configurator/daemon/refresh/plot";
var n=20; //Max number of data to plot
var refreshTime = 5000; //5 secs;
var x = (new Date()).getTime(); // current time
//buffer of n samples
var buffer = [];
for(var i=0; i<n; i++){
	buffer.push([x - (n-1-i)*refreshTime,0]);
} 

var options = {
  axes: {
	   xaxis: {
	   	  numberTicks: 4,
        renderer:$.jqplot.DateAxisRenderer,
        tickOptions:{formatString:'%H:%M:%S'},
        min : buffer[0][0],
        //max : data[19][0]
        max: buffer[buffer.length-1][0]
	   },
	   yaxis: {min:0, max: 100,numberTicks: 6,
	        tickOptions:{formatString:'%d'} 
	   }
  },
  seriesDefaults: {
	   rendererOptions: { smooth: true},
  },
  highlighter: {
      show: true,
      sizeAdjust: 7.5
    },
    cursor: {
        show: false
      }
  
};

function renderNagiosDaemonGraph(id,title,options) {
	    if (plot[id]) {
	        plot[id].destroy();
	    }
	    if(plotdata[id]==undefined)
    	{
	    	plotdata[id]=[];
	    	for(var i=0; i<n; i++){
	    		plotdata[id].push([x - (n-1-i)*refreshTime,0]);
	    	} 
    	}
	    storedData=plotdata[id];
	    options.title={
				   text:title,
				   fontSize:"14px"// title for the plot,
			   };
	    plot[id] = $.jqplot(id, [storedData], options);
	    
	}

function doNagiosDaemonGraphUpdate() {
	if(!plotRefreshUrl)
		return;
	$.getJSON(plotRefreshUrl,function(data){
    if (data) {
    	var x = (new Date()).getTime();
    	for (p in data) {
    		if(plotdata[p].length > n-1){
    			plotdata[p].shift();
    	    }
    		plotdata[p].push([x,data[p]]);
    		plot[p].series[0].data = plotdata[p]; 
    	    //il problema � che adesso i valori su y delle ticks non sono pi� statici
    	    //e cambiano ad ogni aggiornamento, quindi cambia la logica sottostante
    	    // devo intervenire sui valori all'interno di options.
    	    options.axes.xaxis.min = plotdata[p][0][0];
    	    options.axes.xaxis.max = plotdata[p][plotdata[p].length-1][0];
             /* update storedData array*/
    		
            renderNagiosDaemonGraph(p,plot[p].title.text,options);
    	}  
        
    } 
	});
	setTimeout(doNagiosDaemonGraphUpdate, refreshTime);
}

function plotResize()
{
	for(p in plot)
	{
		 renderNagiosDaemonGraph(p,plot[p].title.text,options);
	}
}

$(document).ready(function(){
	 $( window ).resize(function() {
		 plotResize();
	 });
	setTimeout(doNagiosDaemonGraphUpdate, refreshTime);
	});