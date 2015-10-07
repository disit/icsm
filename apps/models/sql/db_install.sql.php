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

$sql["configuration"]="CREATE TABLE `configuration` (
  `cid` int(11) NOT NULL AUTO_INCREMENT,
  `identifier` char(255) NOT NULL,
  `description` char(255) NOT NULL,
  `name` char(255) NOT NULL,
  `contacts` char(255) NOT NULL,
  `type` char(255) NOT NULL,
  `bid` char(255) NOT NULL default '',
  PRIMARY KEY (`cid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;";

$sql["monitor_info"]="CREATE TABLE `monitor_info` (
  `ref` int(11) DEFAULT NULL,
  `type` char(255) DEFAULT NULL,
  `minfo_id` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`minfo_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;";

$sql["metric"]="CREATE TABLE `metric` (
  `name` char(255) NOT NULL,
  `critical_value` char(255) NOT NULL,
  `warning_value` char(255) NOT NULL,
  `args` char(255) NOT NULL,
  `max_check_attempts` int(11) NOT NULL DEFAULT '5',
  `check_interval` int(11) NOT NULL DEFAULT '5',
  `status` char(255) NOT NULL DEFAULT 'running',
  `mode` char(255) NOT NULL,
  `minfo_id` int(11) DEFAULT NULL,
  `mid` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`mid`),
  KEY `minfo_id` (`minfo_id`),
  CONSTRAINT `metric_ibfk_1` FOREIGN KEY (`minfo_id`) REFERENCES `monitor_info` (`minfo_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;";


$sql["application"]="CREATE TABLE `application` (
  `cid` int(11) DEFAULT NULL,
  `aid` int(11) NOT NULL AUTO_INCREMENT,
  `name` char(255) NOT NULL,
  `id` char(255) NOT NULL,
  `type` char(255) NOT NULL,
  `description` char(255) NOT NULL,
  `contacts` char(255) NOT NULL,
  PRIMARY KEY (`aid`),
  KEY `cid` (`cid`),
  CONSTRAINT `application_ibfk_1` FOREIGN KEY (`cid`) REFERENCES `configuration` (`cid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;";

$sql["device"]="CREATE TABLE `device` (
  `id` char(255) NOT NULL,
  `device_type` char(255) NOT NULL,
  `type` char(255) NOT NULL,
  `ip_address` char(255) NOT NULL,
  `monitor_ip_address` char(255) NOT NULL,
  `name` char(255) NOT NULL,
  `model` char(255) NOT NULL,
  `description` char(255) NOT NULL,
  `port` int(11) NOT NULL,
  `alias` char(255) NOT NULL,
  `parent_device` char(255) NOT NULL,
  `device_group` char(255) NOT NULL,
  `auth_user` char(255) NOT NULL,
  `auth_pwd` char(255) NOT NULL,
  `domain_name` char(255) NOT NULL,
  `minfo_id` int(11) DEFAULT NULL,
  `cid` int(11) DEFAULT NULL,
  `did` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`did`),
  KEY `cid` (`cid`),
  KEY `minfo_id` (`minfo_id`),
  CONSTRAINT `device_ibfk_2` FOREIGN KEY (`minfo_id`) REFERENCES `monitor_info` (`minfo_id`) ON DELETE CASCADE,
  CONSTRAINT `device_ibfk_1` FOREIGN KEY (`cid`) REFERENCES `configuration` (`cid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;";

$sql["host"]="CREATE TABLE `host` (
  `id` char(255) NOT NULL,
  `name` char(255) NOT NULL,
  `os` char(255) NOT NULL,
  `type` char(255) NOT NULL DEFAULT 'host',
  `ip_address` char(255) NOT NULL,
  `monitor_ip_address` char(255) NOT NULL,
  `alias` char(255) NOT NULL,
  `parent_host` char(255) NOT NULL,
  `host_group` char(255) NOT NULL,
  `domain_name` char(255) NOT NULL,
  `auth_pwd` char(255) NOT NULL,
  `auth_user` char(255) NOT NULL,
  `description` char(255) NOT NULL,
  `minfo_id` int(11) DEFAULT NULL,
  `cid` int(11) DEFAULT NULL,
  `hid` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`hid`),
  KEY `cid` (`cid`),
  KEY `minfo_id` (`minfo_id`),
  CONSTRAINT `host_ibfk_2` FOREIGN KEY (`minfo_id`) REFERENCES `monitor_info` (`minfo_id`) ON DELETE CASCADE,
  CONSTRAINT `host_ibfk_1` FOREIGN KEY (`cid`) REFERENCES `configuration` (`cid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;";


$sql["service"]="CREATE TABLE `service` (
  `id` char(255) NOT NULL,
  `name` char(255) NOT NULL,
  `type` char(255) NOT NULL,
  `description` char(255) NOT NULL,
  `ip_address` char(255) NOT NULL,
  `service_group` char(255) NOT NULL,
  `run_on` char(255) NOT NULL,
  `port` int(11) NOT NULL DEFAULT '0',
  `process_name` char(255) NOT NULL,
  `alias` char(255) NOT NULL,
  `parent` char(255) NOT NULL,
  `auth_user` char(255) NOT NULL,
  `auth_pwd` char(255) NOT NULL,
  `minfo_id` int(11) DEFAULT NULL,
  `sid` int(11) NOT NULL AUTO_INCREMENT,
  `aid` int(11) DEFAULT NULL,
  PRIMARY KEY (`sid`),
  KEY `minfo_id` (`minfo_id`),
  KEY `aid` (`aid`),
  CONSTRAINT `service_ibfk_2` FOREIGN KEY (`aid`) REFERENCES `application` (`aid`) ON DELETE CASCADE,
  CONSTRAINT `service_ibfk_1` FOREIGN KEY (`minfo_id`) REFERENCES `monitor_info` (`minfo_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;";


$sql["tenant"]="CREATE TABLE `tenant` (
  `name` char(255) NOT NULL,
  `id` char(255) NOT NULL,
  `description` char(255) NOT NULL,
  `contacts` char(255) NOT NULL,
  `runOn` char(255) NOT NULL,
  `minfo_id` int(11) DEFAULT NULL,
  `cid` int(11) DEFAULT NULL,
  `tid` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`tid`),
  KEY `minfo_id` (`minfo_id`),
  KEY `cid` (`cid`),
  CONSTRAINT `tenant_ibfk_2` FOREIGN KEY (`cid`) REFERENCES `configuration` (`cid`) ON DELETE CASCADE,
  CONSTRAINT `tenant_ibfk_1` FOREIGN KEY (`minfo_id`) REFERENCES `monitor_info` (`minfo_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;";