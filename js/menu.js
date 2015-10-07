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

function DeleteTBWSelectedMenuItems(id,endCallback) { 
	 jQuery.ajax({url:"menu/item/delete",  type:"POST", data:id, dataType:"json", success: function(msg) 
	 {  
		 if(endCallback!=undefined && msg)
		 {
			 endCallback("Deleted item "+id);
		 }
     }  
	});  
 }  

$(document).ready(function()
		{
			$('form .confirm-toggle').click(function(event) {
		        event.preventDefault();});
			$('#menu_editor_menu').on('click', function(e)
		    {
		        var target = $(e.target),
		            action = target.data('action');
		        if (action === 'expand-all') {
		            $('#menu_editor').nestable('expandAll');
		        }
		        if (action === 'collapse-all') {
		            $('#menu_editor').nestable('collapseAll');
		        }
		    });
	
			$(".modal-dialog .btn").addClass("button light-gray btn-sm");
			$("#menu_config_container .button, #menu_config_container button, #menu_config_container .btn").not(".close").not('.action_form_cmd').addClass("button light-gray btn-sm");
			$('#menu_editor').nestable({
		     });
				$('.dd').nestable('collapseAll');
				
			$('#menu_reorder').submit(function()
			{
				
				$('#menu_reorder input[name=json]').val(JSON.stringify($('#menu_editor').nestable('serialize')));
				
			});
			
			$('#MenuDeleteItemDlg').on('click','#btnYes',function(event) {
			    // handle deletion here
			  	var id = $('#MenuDeleteItemDlg').data('id');
			  	$('#menu_delete_item input[name=mid]').val(id);
			  	$('#MenuDeleteItemDlg').modal('hide');
			  	$('#menu_delete_item').submit();
			  	return false;
			});
			
			$('#MenuDeleteDlg').on('click','#btnYes',function(event) {
			    // handle deletion here
			  	$('#MenuDeleteDlg').modal('hide');
			  	$('#menu_data input[name=cmd]').val("delete");
			  	$('#menu_data').submit();
			  	 
			  	return false;
			});
			
			$('.confirm-delete').on('click', function(e) {
			    e.preventDefault();

			    var id = $(this).data('id');
			    $('#MenuDeleteItemDlg').data('id', id);
			});
			
			var myBackup = $('#MenuEditDlg').clone();
			$('body').on('show.bs.modal', '#MenuEditDlg', function () {
			    $("#MenuEditDlg .modal-body").html("<div><i class='sm-icon sm-loader'></i> Loading....</div>");
			});
			
			$('body').on('hidden.bs.modal', '#MenuEditDlg', function () {
				 $('#MenuEditDlg').modal('hide').remove();
			        var myClone = myBackup.clone();
			        $('body').append(myClone);
			});
			
			$('body').on('loaded.bs.modal', '#MenuEditDlg', function () {
				 $("#MenuEditDlg .btn").addClass("button light-gray btn-sm");
				 $('#MenuEditDlg').on('click','#btnSave',function(event) {
						$('#MenuEditDlg').modal('hide');
						//$('#menu_edit_item').submit();
						$('#MenuEditDlg form').submit();
					});
			});
						
			$('#MenuEditDlg').on('click','#btnSave',function(event) {
				$('#MenuEditDlg').modal('hide');
				$('#MenuEditDlg form').submit();
			});
			$('#AddMenuDlg').on('click','#btnSave',function(event) {
				$('#AddMenuDlg').modal('hide');
				$('#menu_new').submit();
			});
		});


