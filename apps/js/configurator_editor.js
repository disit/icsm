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
			$('form .confirm-toggle').click(function(event) {
		        event.preventDefault();});
			
	
			$(".modal-dialog .btn").addClass("button light-gray btn-sm");
			$("#ConfigurationEditContainer .button, #ConfigurationEditContainer button, #ConfigurationEditContainer .btn").not(".close").addClass("button light-gray btn-sm");
			
			
			$('#DeleteServiceDlg').on('click','#btnYes',function(event) {
			    // handle deletion here
			  	var id = $('#DeleteServiceDlg').data('id');
			  	$('#app_delete_service input[name=sid]').val(id);
			  	$('#DeleteServiceDlg').modal('hide');
			  	$('#app_delete_service').submit();
			  	return false;
			});
			
			$('#DeleteAppDlg').on('click','#btnYes',function(event) {
			    // handle deletion here
			  	$('#DeleteAppDlg').modal('hide');
			  	$('#Applications input[name=cmd]').val("delete");
			  	$('#Applications').submit();
			  	 
			  	return false;
			});
			
			$('.confirm-delete').on('click', function(e) {
			    e.preventDefault();

			    var id = $(this).data('id');
			    $('#DeleteServiceDlg').data('id', id);
			});
			
			var myBackup = $('#AddServiceDlg').clone();
			$('body').on('show.bs.modal', '#AddServiceDlg', function () {
			    $("#AddServiceDlg .modal-body").html("<div><i class='sm-icon sm-loader'></i> Loading....</div>");
			});
			
			$('body').on('hidden.bs.modal', '#AddServiceDlg', function () {
				 $('#AddServiceDlg').modal('hide').remove();
			        var myClone = myBackup.clone();
			        $('body').append(myClone);
			});
			
			$('body').on('loaded.bs.modal', '#AddServiceDlg', function () {
				 $("#AddServiceDlg .btn").addClass("button light-gray btn-sm");
				 $('#AddServiceDlg').on('click','#btnSave',function(event) {
						$('#AddServiceDlg').modal('hide');
						$('#AddServiceDlg form').submit();
					});
			});
						
			/*$('#AddServiceDlg').on('click','#btnSave',function(event) {
				$('#AddServiceDlg').modal('hide');
				$('#AddServiceDlg form').submit();
			});*/
			
			$('.AddItemtDlg').on('click','#btnSave',function(event) {
				$('.AddItemtDlg').modal('hide');
				$('.AddItemtDlg form').submit();
			});
		});


