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

function onPieClick(ev,data)
{
	
		var target = ev.target.id.split("_");
		var dlg="#DashboardDlg";
		if($(dlg).length!=0)
		{
			var title = $(dlg).find(".modal-title").html()+" ("+target[0]+")";
			$(dlg).find(".modal-title").html(title);
			$(dlg).modal('show');
		
			var params = target[0]+"/"+target[1]+"/"+data[0];
			
			$(dlg).find(".modal-body").load("monitor/dashboard/details/"+params,null,function()
					{
				 		$(this).css({width:'auto',height:'auto', 'max-height':'100%'});
					});
		}
}

$(document).ready(function()
{
	if($("#DashboardDlg").length==0)
		return;
	
	var myDashDlgBackup = $('#DashboardDlg').clone();
	$('body').on('show.bs.modal', '#DashboardDlg', function () {
	    $("#DashboardDlg .modal-body").html("<div><i class='sm-icon sm-loader'></i> Loading....</div>");
	});
	
	$('body').on('hidden.bs.modal', '#DashboardDlg', function () {
		 $('#DashboardDlg').modal('hide').remove();
	        var myClone = myDashDlgBackup.clone();
	        $('body').append(myClone);
	});

});