<?php 
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

$used_space=chop(shell_exec("df -h / | grep -v Filesystem | awk '{print $5}'"));

switch ($used_space) {
	case "$used_space" < "85%":
		print "OK - $used_space of disk space used.";
		exit(0);

		case "$used_space" == "85%":
		print "WARNING - $used_space of disk space used.";
		exit(1);

		case $used_space > "85%":
		print "CRITICAL - $used_space of disk space used.";
		exit(2);

		default:
		print "UNKNOWN - $used_space of disk space used.";
		exit(3);
	}
/*	print 'This check passed';
	exit(2);*/
?>