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

function monitor_resize_pie(plot)
{
	plot.legend.numberRows=$( window ).width()<480?2:null;
    plot.legend.location=$( window ).width()<480?'s':'e';
    plot.legend.marginTop= $( window ).width()<480?'5px':'15px';
    plot.replot();
}

function monitor_do_pie(id,title,data,colors)
{
		   var plot1 = $.jqplot(id, data , {
			   title: {
				   text:title,
				   fontSize:"14px"// title for the plot,
			   },
			cursor: {
			      // style: 'crosshair',
			       show: true,
			    	 },
	    	seriesColors:colors,
	        gridPadding: {top:24, bottom:16, left:0, right:0},
	        seriesDefaults:{
	            renderer:$.jqplot.PieRenderer, 
	         //   trendline:{ show:false }, 
	            rendererOptions: { 
	            	padding: 8, 
	            	showDataLabels: true,
	                // By default, data labels show the percentage of the donut/pie.
	                // You can show the data 'value' or data 'label' instead.
	                dataLabels: 'value',
	                }
	        },
	        legend:{
	            show:true, 
	          /* placement: 'outside',
	             rendererOptions:$( window ).width()>400?null:{
	                numberRows: 2
	            },*/
	            location:'e',
	            marginTop: '15px'
	        }       
	   });
		   
		   
	   $('#'+id).bind("jqplotDataMouseOver",function(ev, seriesIndex, pointIndex, data){
		   $(this).css('cursor','pointer');
	   });
	   $('#'+id).bind("jqplotDataUnhighlight",function(ev, seriesIndex, pointIndex, data){
		   $(this).css('cursor','auto');
	   });
	   
	   $('#'+id).bind("jqplotDataClick",function(ev, seriesIndex, pointIndex, data){
		   onPieClick(ev,data);
		});
	   
	   monitor_resize_pie(plot1);
		   $( window ).resize(function() {
			   		monitor_resize_pie(plot1);
			 });
}