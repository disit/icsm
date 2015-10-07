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

$(document).ready(
		function()
		{
			/*if($("#ConfigApp").length!=0)
			{
				$('.cApp_module').hide();
				
				$("#ConfigApp ul li a").unbind("click");
				$("#ConfigApp ul li a").on("click",function(event)
						{
							event.preventDefault();
							$('.cApp_module').hide();
							var show = $(this).attr("href");
							$("div"+show).show();
							return false;
						});
				$("#ConfigApp ul li a")[0].click();
			}*/
			if($('#cView_navbar').length!=0)
				cView_init();
			
		}
);


function cView_init()
{
	
	$('#cView_navbar a').unbind("click");
	$('#cView_navbar a').on("click",function(event)
	{
		event.preventDefault();
		var id = $(this).attr("href");
		$('#cView_navbar a').removeClass("active");
		$(this).addClass("active");

			$(".cView_panel").hide();
			$(id).show();		
		return false;
	});	
	$('#cView_navbar a')[0].click();
}