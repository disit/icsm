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

function NagiosDaemonMenu_command(item,cmdUrl)
{
	$.getJSON(cmdUrl,function(data){
		var target=$(item).attr("data-target");
	/*	var toggle=$(item).attr("data-toggle");
		if(toggle)
		{
			var cmds = toggle.split(",");
			for(var i in cmds)
				$(""+cmds[i]).show();
			$(item).hide();
		}*/
		$('#NagiosDaemonMenu').html($(data.menu).html());
		if(target)
		{
			$(""+target).hide();
			$(""+target).html("<div class=\"alert alert-info\" role=\"alert\">"+data.result+"</div>");
			$(""+target).show().animate({opacity: 1.0}, 3000).fadeOut(1000);
		}
		NagiosDaemonMenu_init();
	});
};

function NagiosDaemonMenu_init()
{
	$('#NagiosDaemonMenu a').unbind("click");
	$('#NagiosDaemonMenu  a').on("click",function(event)
	{
		event.preventDefault();
		var cmdUrl=$(this).attr("href");
		NagiosDaemonMenu_command(this,cmdUrl);
		return false;
	});
}

$(document).ready(function()
{
	NagiosDaemonMenu_init();
});
	
	