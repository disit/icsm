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

var sm_Dialog={};

$(document).ready(function()
		{
	
			  if(sm_Dialog.id!=undefined)
			  {
				  $('#'+sm_Dialog.id+" .btn").addClass("button light-gray btn-sm");
				  $('#'+sm_Dialog.id).on('show.bs.modal', function (e) {
			  
				      $message = $(e.relatedTarget).attr('data-message');
				      $(this).find('.modal-body').text($message);
				      $title = $(e.relatedTarget).attr('data-title');
				      $(this).find('.modal-title').text($title);
			
				      // Pass form reference to modal for submission on yes/ok
				      var form = $(e.relatedTarget).closest('form');
				      $(this).find('.modal-footer #'+sm_Dialog.btnId).data('form', form);
				  });
			
				
				  $('#'+sm_Dialog.id).find('.modal-footer #'+sm_Dialog.btnId).on('click', function(){			  
					  $(this).data('form').submit();
					  return false;
				      
				  });
						
				  $('form.'+sm_Dialog.formClass+" button").attr("type","button");
			  }
		});
