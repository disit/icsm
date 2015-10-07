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
			
			$("#ACL-Roles form.ACLEdit").submit(function(){return false;});
			$("#ACL-Roles form.ACLEdit").each(function(){
				$(this).find("button[type=submit]").attr("href",$(this).attr("action"));
			});
			
			$(".modal-dialog .btn").addClass("button light-gray btn-sm");
			var myBackup = $('#EditRoleDlg').clone();
			$('body').on('show.bs.modal', '#EditRoleDlg', function () {
			    $("#EditRoleDlg .modal-body").html("<div><i class='sm-icon-24 sm-loader'></i> Loading....</div>");
			});
			
			$('body').on('hidden.bs.modal', '#EditRoleDlg', function () {
				 $('#EditRoleDlg').modal('hide').remove();
			        var myClone = myBackup.clone();
			        $('body').append(myClone);
			});
			
			$('body').on('loaded.bs.modal', '#EditRoleDlg', function () {
				 $("#EditRoleDlg .btn").addClass("button light-gray btn-sm");
				 $('#EditRoleDlg').on('click','#btnSave',function(event) {
						$('#EditRoleDlg').modal('hide');
						$('#EditRoleDlg form#acl_edit_role').submit();
					});
			});
			
			$('#ACL-Roles form.ACLDelete button[type=submit]').on('click', function(e) {
			    e.preventDefault();

			    var id = $(this).parent().attr('id');
			    $('#ACLDeleteItemDlg').data('id', id);
			});
			
			$('#ACLDeleteItemDlg').on('click','#btnYes',function(event) {
						$('#ACLDeleteItemDlg').modal('hide');
						var id = $('#ACLDeleteItemDlg').data('id');
						$('#ACL-Roles form#'+id).submit();
						return false;
					});
				
			$('#AddRoleDlg').on('click','#btnSave',function(event) {
				$('#AddRoleDlg').modal('hide');
				$('#AddRoleDlg form#acl_edit_role').submit();
					return false;
			});
			
						
		});
