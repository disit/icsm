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
			if($("nav ul.nav.side-nav.minimized").length>0)
			{
				$("ul.nav.side-nav.minimized").hover(function(){
					clearTimeout($(this).data('timeout'));
					var tmp = setTimeout(function(){
						$("ul.nav.side-nav.minimized").toggleClass("opened");
					},500);
					 $(this).data('timeout', tmp);
				},
				function(){
					clearTimeout($(this).data('timeout'));
					$("ul.nav.side-nav.minimized.opened").toggleClass("opened");
					$("ul.nav.side-nav.minimized li.dropdown.open").toggleClass("open");
				});
				return;
			}
			
		});	