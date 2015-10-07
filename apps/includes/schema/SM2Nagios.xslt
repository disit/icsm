<?xml version='1.0' encoding='UTF-8' ?>
<!--
   Icaro Supervisor & Monitor (ICSM).
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
   Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
-->
<xsl:stylesheet version="2.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:xs="http://www.w3.org/2001/XMLSchema" xmlns:fn="http://www.w3.org/2005/xpath-functions" exclude-result-prefixes="xs fn">
	<xsl:output method="xml" encoding="UTF-8" byte-order-mark="no" indent="yes"/>
	<xsl:template match="/">
		<nagios>
			<xsl:attribute name="xsi:noNamespaceSchemaLocation" namespace="http://www.w3.org/2001/XMLSchema-instance" select="'E:/Icaro/data/nagios.xsd'"/>
			<xsl:for-each select="configuration">
				<xsl:variable name="var1_hosts" as="node()?" select="hosts"/>
				<xsl:variable name="var2_tenants" as="node()?" select="tenants"/>
				<xsl:variable name="var3_applications" as="node()?" select="applications"/>
				<xsl:variable name="var4_devices" as="node()?" select="devices"/>
				<xsl:variable name="var5_resultof_concat" as="xs:string" select="fn:concat(fn:concat(fn:string(name), '@'), fn:string(identifier))"/>
				<hostgroup>
					<hostgroup_name>
						<xsl:sequence select="$var5_resultof_concat"/>
					</hostgroup_name>
					<alias>
						<xsl:sequence select="fn:string(description)"/>
					</alias>
				</hostgroup>
				<xsl:for-each select="$var1_hosts/host">
					<xsl:variable name="var7_cur" as="node()" select="."/>
					<xsl:for-each select="ip_address">
						<xsl:variable name="var6_resultof_cast" as="xs:string" select="fn:string(.)"/>
						<host>
						 <xsl:attribute name="hid">
   							 <xsl:value-of select="$var7_cur/@hid" />
 						 </xsl:attribute>
							<host_name>
								<xsl:sequence select="fn:concat(fn:concat(fn:string($var7_cur/name), '@'), $var6_resultof_cast)"/>
							</host_name>
							<xsl:for-each select="$var7_cur/description">
								<alias>
									<xsl:sequence select="fn:string(.)"/>
								</alias>
							</xsl:for-each>
							<address>
								<xsl:sequence select="$var6_resultof_cast"/>
							</address>
							<display_name>
								<xsl:sequence select="fn:string($var7_cur/id)"/>
							</display_name>
							<xsl:for-each select="$var7_cur/parent_host">
							<xsl:if test="fn:string(.)!=''">	
								<parents>
									<xsl:sequence select="fn:string(.)"/>
								</parents>
							</xsl:if>
							</xsl:for-each>
							<hostgroups>
								<xsl:sequence select="$var5_resultof_concat"/>
							</hostgroups>
							<use_template>
								<xsl:sequence select="fn:string($var7_cur/os)"/>
							</use_template>
							<use_variables>
								<hid>
									<xsl:sequence select="fn:string($var7_cur/@hid)"/>
								</hid>
								<xsl:if test="fn:exists($var7_cur/auth_user)">	
								<user>
									<xsl:sequence select="fn:string($var7_cur/auth_user)"/>
								</user>
								</xsl:if>
								<xsl:if test="fn:exists($var7_cur/auth_pwd)">	
								<pwd>
									<xsl:sequence select="fn:string($var7_cur/auth_pwd)"/>
								</pwd>
								</xsl:if>
								<xsl:if test="fn:exists($var7_cur/domain_name)">	
								<domain>
									<xsl:sequence select="fn:string($var7_cur/domain_name)"/>
								</domain>
								</xsl:if>
							</use_variables>
							<notes>
								<xsl:sequence select="fn:string($var7_cur/type)"/>
							</notes>
						</host>
					</xsl:for-each>
				</xsl:for-each>
				<xsl:for-each select="$var4_devices/device">
					<xsl:variable name="var9_cur" as="node()" select="."/>
					<xsl:for-each select="ip_address">
						<xsl:variable name="var8_resultof_cast" as="xs:string" select="fn:string(.)"/>
						<host>
							 <xsl:attribute name="did">
   								 <xsl:value-of select="$var9_cur/@did" />
 							 </xsl:attribute>
							<host_name>
								<xsl:sequence select="fn:concat(fn:concat(fn:string($var9_cur/name), '@'), $var8_resultof_cast)"/>
							</host_name>
							<xsl:for-each select="$var9_cur/description">
								<alias>
									<xsl:sequence select="fn:string(.)"/>
								</alias>
							</xsl:for-each>
							<address>
								<xsl:sequence select="$var8_resultof_cast"/>
							</address>
							<display_name>
								<xsl:sequence select="fn:string($var9_cur/id)"/>
							</display_name>
							<xsl:for-each select="$var9_cur/parent_device">
								<parents>
									<xsl:sequence select="fn:string(.)"/>
								</parents>
							</xsl:for-each>
							<xsl:for-each select="$var9_cur/device_group">
								<xsl:if test="fn:string(.) != ''">
									<hostgroups>
										<xsl:sequence select="fn:string(.)"/>
									</hostgroups>
								</xsl:if>
							</xsl:for-each>
							<hostgroups>
								<xsl:sequence select="$var5_resultof_concat"/>
							</hostgroups>
							<use_template>
								<xsl:sequence select="fn:string($var9_cur/device_type)"/>
							</use_template>
							<notes>
								<xsl:choose>
									<xsl:when test="fn:string($var9_cur/type) = 'physical'">
										<xsl:sequence select="'device'"/>
									</xsl:when>
									<xsl:otherwise>
										<xsl:sequence select="'vmdevice'"/>
									</xsl:otherwise>
								</xsl:choose>
							</notes>
						</host>
					</xsl:for-each>
				</xsl:for-each>
				<xsl:for-each select="$var3_applications/application">
					<servicegroup>
						<xsl:for-each select="(description/node())[fn:boolean(self::text())]">
							<alias>
								<xsl:sequence select="fn:string(.)"/>
							</alias>
						</xsl:for-each>
						<servicegroup_name>
							<xsl:sequence select="fn:concat(fn:concat(fn:string(name), '@'), fn:string(id))"/>
						</servicegroup_name>
						<xsl:for-each select="$var2_tenants/tenant">
							<members>
								<xsl:sequence select="fn:concat(fn:concat(fn:string(name), '@'), fn:string(id))"/>
							</members>
						</xsl:for-each>
					</servicegroup>
				</xsl:for-each>
				<xsl:for-each select="$var2_tenants/tenant">
					<servicegroup>
						<alias>
							<xsl:sequence select="fn:string(description)"/>
						</alias>
						<servicegroup_name>
							<xsl:sequence select="fn:concat(fn:concat(fn:string(name), '@'), fn:string(id))"/>
						</servicegroup_name>
					</servicegroup>
				</xsl:for-each>
				<xsl:for-each select="$var3_applications/application">
					<xsl:variable name="var11_cur" as="node()" select="."/>
					<xsl:for-each select="services/service">
						<xsl:variable name="cur_serv" as="node()" select="."/>
							<service>
							 <xsl:attribute name="sid">
   								 <xsl:value-of select="$cur_serv/@sid" />
 							 </xsl:attribute>
							<xsl:for-each select="$var1_hosts/host">
								<xsl:variable name="var10_cur" as="node()" select="."/>
								<xsl:for-each select="ip_address">
									
									<xsl:if test="$var10_cur/id = $cur_serv/run_on"> 
									<xsl:choose>
										<xsl:when test="string-length($cur_serv/ip_address)>0"> 
											<xsl:if test="$cur_serv/ip_address=fn:string(.)">
												<host_name>
														<xsl:value-of select="fn:concat(fn:concat(fn:string($var10_cur/name), '@'), fn:string(.))"/> 
												</host_name>
											</xsl:if>
										</xsl:when>
										<xsl:otherwise>
											<host_name>
														<xsl:value-of select="fn:concat(fn:concat(fn:string($var10_cur/name), '@'), fn:string(.))"/> 
											</host_name>
										</xsl:otherwise>
									</xsl:choose>
									</xsl:if> 
									
								</xsl:for-each>
							</xsl:for-each>
							<display_name>
								<xsl:sequence select="fn:string(name)"/>
							</display_name>
							<config_name>
								<xsl:sequence select="fn:string(name)"/>
							</config_name>
							<service_description>
								<xsl:sequence select="fn:string(description)"/>
							</service_description>
							<servicegroups>
								<xsl:sequence select="fn:concat(fn:concat(fn:string($var11_cur/name), '@'), fn:string($var11_cur/id))"/>
							</servicegroups>
							<use_template>
								<xsl:sequence select="fn:string(type)"/>
							</use_template>
						</service>
							<xsl:for-each select="$cur_serv/monitor_info/metrics/metric">
								<xsl:variable name="var12_warningvalue" as="node()?" select="warning_value"/>
								<xsl:variable name="var13_criticalvalue" as="node()?" select="critical_value"/>
								<xsl:variable name="var14_args" as="node()?" select="args"/>
								<xsl:variable name="var15_status" as="node()?" select="status"/>
								<xsl:variable name="var16_resultof_exists" as="xs:boolean" select="fn:exists($var13_criticalvalue)"/>
								<xsl:variable name="var17_resultof_exists" as="xs:boolean" select="fn:exists($var12_warningvalue)"/>
								<xsl:variable name="var18_val" as="xs:string">
									<xsl:choose>
										<xsl:when test="$var17_resultof_exists">
											<xsl:sequence select="fn:concat('w=', fn:string($var12_warningvalue))"/>
										</xsl:when>
										<xsl:otherwise>
											<xsl:sequence select="'w=0'"/>
										</xsl:otherwise>
									</xsl:choose>
								</xsl:variable>
								<xsl:variable name="var19_val" as="xs:string">
									<xsl:choose>
										<xsl:when test="$var16_resultof_exists">
											<xsl:sequence select="fn:concat('c=', fn:string($var13_criticalvalue))"/>
										</xsl:when>
										<xsl:otherwise>
											<xsl:sequence select="'c=0'"/>
										</xsl:otherwise>
									</xsl:choose>
								</xsl:variable>
								<xsl:variable name="var20_resultof_concat" as="xs:string" select="fn:concat($var18_val, &apos;&amp;&apos;)"/>
								<xsl:variable name="var21_resultof_concat" as="xs:string" select="fn:concat($var20_resultof_concat, $var19_val)"/>
								<service>
									<xsl:for-each select="$var1_hosts/host">
										<xsl:variable name="var22_cur" as="node()" select="."/>
										<xsl:for-each select="ip_address">
											<xsl:if test="$var22_cur/id = $cur_serv/run_on"> 
												<host_name>
														<xsl:value-of select="fn:concat(fn:concat(fn:string($var22_cur/name), '@'), fn:string(.))"/> 
												</host_name>
												</xsl:if>
										</xsl:for-each>
									</xsl:for-each>
									<display_name>
										<xsl:sequence select="fn:concat(fn:concat(fn:string(name), '@'), fn:string($cur_serv/id))"/>
									</display_name>
									<config_name>
										<xsl:sequence select="fn:concat(fn:concat(fn:string(name), '@'), fn:string($cur_serv/id))"/>
									</config_name>
									<servicegroups>
										<xsl:sequence select="fn:concat(fn:concat(fn:string($var11_cur/name), '@'), fn:string($var11_cur/id))"/>
									</servicegroups>
									<service_description>
										<xsl:sequence select="fn:string(name)"/>
									</service_description>
									<check_command>
										<xsl:choose>
											<xsl:when test="fn:exists($var14_args)">
												<xsl:sequence select="fn:concat(fn:concat($var21_resultof_concat, &apos;&amp;&apos;), replace(replace(fn:string($var14_args), &apos;,&apos;, &apos;&amp;&apos;), &apos;:&apos;, &apos;=&apos;))"/>
											</xsl:when>
											<xsl:otherwise>
												<xsl:sequence select="$var21_resultof_concat"/>
											</xsl:otherwise>
										</xsl:choose>
									</check_command>
									<xsl:for-each select="check_interval">
										<check_interval>
											<xsl:sequence select="xs:string(xs:integer(fn:string(.)))"/>
										</check_interval>
									</xsl:for-each>
									<xsl:for-each select="max_check_attempts">
										<max_check_attempts>
											<xsl:sequence select="xs:string(xs:integer(fn:string(.)))"/>
										</max_check_attempts>
									</xsl:for-each>
									<xsl:variable name="var23_result" as="xs:string">
										<xsl:choose>
											<xsl:when test="fn:exists($var15_status)">
												<xsl:sequence select="fn:string($var15_status)"/>
											</xsl:when>
											<xsl:otherwise>
												<xsl:sequence select="'Running'"/>
											</xsl:otherwise>
										</xsl:choose>
									</xsl:variable>
									<xsl:variable name="var24_result" as="xs:decimal">
										<xsl:choose>
											<xsl:when test="(fn:compare($var23_result, 'Stopped') = xs:decimal('0'))">
												<xsl:sequence select="xs:decimal('0')"/>
											</xsl:when>
											<xsl:otherwise>
												<xsl:sequence select="xs:decimal('1')"/>
											</xsl:otherwise>
										</xsl:choose>
									</xsl:variable>
									<active_checks_enabled>
										<xsl:sequence select="xs:string(xs:integer($var24_result))"/>
									</active_checks_enabled>
								</service>
							</xsl:for-each>
					</xsl:for-each>
				</xsl:for-each>
				<xsl:for-each select="$var1_hosts/host">
					<xsl:variable name="var38_cur" as="node()" select="."/>
					<xsl:for-each select="monitor_info/metrics/metric">
						<xsl:variable name="var25_warningvalue" as="node()?" select="warning_value"/>
						<xsl:variable name="var26_criticalvalue" as="node()?" select="critical_value"/>
						<xsl:variable name="var27_args" as="node()?" select="args"/>
						<xsl:variable name="var28_status" as="node()?" select="status"/>
						<xsl:variable name="var29_resultof_exists" as="xs:boolean" select="fn:exists($var26_criticalvalue)"/>
						<xsl:variable name="var30_resultof_exists" as="xs:boolean" select="fn:exists($var25_warningvalue)"/>
						<xsl:variable name="var31_resultof_cast" as="xs:string" select="fn:string(name)"/>
						<xsl:variable name="var32_val" as="xs:string">
							<xsl:choose>
								<xsl:when test="$var30_resultof_exists">
									<xsl:sequence select="fn:concat('w=', fn:string($var25_warningvalue))"/>
								</xsl:when>
								<xsl:otherwise>
									<xsl:sequence select="'w=0'"/>
								</xsl:otherwise>
							</xsl:choose>
						</xsl:variable>
						<xsl:variable name="var33_val" as="xs:string">
							<xsl:choose>
								<xsl:when test="$var29_resultof_exists">
									<xsl:sequence select="fn:concat('c=', fn:string($var26_criticalvalue))"/>
								</xsl:when>
								<xsl:otherwise>
									<xsl:sequence select="'c=0'"/>
								</xsl:otherwise>
							</xsl:choose>
						</xsl:variable>
						<xsl:variable name="var34_resultof_concat" as="xs:string" select="fn:concat($var32_val, &apos;&amp;&apos;)"/>
						<xsl:variable name="var35_resultof_concat" as="xs:string" select="fn:concat($var34_resultof_concat, $var33_val)"/>
						<service>
							<xsl:for-each select="$var38_cur/ip_address">
								<host_name>
									<xsl:sequence select="fn:concat(fn:concat(fn:string($var38_cur/name), '@'), fn:string(.))"/>
								</host_name>
							</xsl:for-each>
							<display_name>
										<xsl:sequence select="fn:concat(fn:concat(fn:string(name), '@'), fn:string($var38_cur/id))"/>
							</display_name>
							<config_name>
										<xsl:sequence select="fn:concat(fn:concat(fn:string(name), '@'), fn:string($var38_cur/id))"/>
							</config_name>
							<service_description>
								<xsl:sequence select="$var31_resultof_cast"/>
							</service_description>
							<check_command>
								<xsl:choose>
									<xsl:when test="fn:exists($var27_args)">
										<xsl:sequence select="fn:concat(fn:concat($var35_resultof_concat, &apos;&amp;&apos;), replace(replace(fn:string($var27_args), &apos;,&apos;, &apos;&amp;&apos;), &apos;:&apos;, &apos;=&apos;))"/>
									</xsl:when>
									<xsl:otherwise>
										<xsl:sequence select="$var35_resultof_concat"/>
									</xsl:otherwise>
								</xsl:choose>
							</check_command>
							<xsl:for-each select="check_interval">
								<check_interval>
									<xsl:sequence select="xs:string(xs:integer(fn:string(.)))"/>
								</check_interval>
							</xsl:for-each>
							<xsl:for-each select="max_check_attempts">
								<max_check_attempts>
									<xsl:sequence select="xs:string(xs:integer(fn:string(.)))"/>
								</max_check_attempts>
							</xsl:for-each>
							<xsl:variable name="var36_result" as="xs:string">
								<xsl:choose>
									<xsl:when test="fn:exists($var28_status)">
										<xsl:sequence select="fn:string($var28_status)"/>
									</xsl:when>
									<xsl:otherwise>
										<xsl:sequence select="'Running'"/>
									</xsl:otherwise>
								</xsl:choose>
							</xsl:variable>
							<xsl:variable name="var37_result" as="xs:decimal">
								<xsl:choose>
									<xsl:when test="(fn:compare($var36_result, 'Stopped') = xs:decimal('0'))">
										<xsl:sequence select="xs:decimal('0')"/>
									</xsl:when>
									<xsl:otherwise>
										<xsl:sequence select="xs:decimal('1')"/>
									</xsl:otherwise>
								</xsl:choose>
							</xsl:variable>
							<active_checks_enabled>
								<xsl:sequence select="xs:string(xs:integer($var37_result))"/>
							</active_checks_enabled>
							<name>
								<xsl:sequence select="$var31_resultof_cast"/>
							</name>
						</service>
					</xsl:for-each>
				</xsl:for-each>
				<xsl:for-each select="$var4_devices/device">
					<xsl:variable name="var51_cur" as="node()" select="."/>
					<xsl:for-each select="monitor_info/metrics/metric">
						<xsl:variable name="var39_warningvalue" as="node()?" select="warning_value"/>
						<xsl:variable name="var40_criticalvalue" as="node()?" select="critical_value"/>
						<xsl:variable name="var41_args" as="node()?" select="args"/>
						<xsl:variable name="var42_status" as="node()?" select="status"/>
						<xsl:variable name="var43_resultof_exists" as="xs:boolean" select="fn:exists($var40_criticalvalue)"/>
						<xsl:variable name="var44_resultof_exists" as="xs:boolean" select="fn:exists($var39_warningvalue)"/>
						<xsl:variable name="var45_val" as="xs:string">
							<xsl:choose>
								<xsl:when test="$var44_resultof_exists">
									<xsl:sequence select="fn:concat('w=', fn:string($var39_warningvalue))"/>
								</xsl:when>
								<xsl:otherwise>
									<xsl:sequence select="'w=0'"/>
								</xsl:otherwise>
							</xsl:choose>
						</xsl:variable>
						<xsl:variable name="var46_val" as="xs:string">
							<xsl:choose>
								<xsl:when test="$var43_resultof_exists">
									<xsl:sequence select="fn:concat('c=', fn:string($var40_criticalvalue))"/>
								</xsl:when>
								<xsl:otherwise>
									<xsl:sequence select="'c=0'"/>
								</xsl:otherwise>
							</xsl:choose>
						</xsl:variable>
						<xsl:variable name="var47_resultof_concat" as="xs:string" select="fn:concat($var45_val, &apos;&amp;&apos;)"/>
						<xsl:variable name="var48_resultof_concat" as="xs:string" select="fn:concat($var47_resultof_concat, $var46_val)"/>
						<service>
							<xsl:for-each select="$var51_cur/ip_address">
								<host_name>
									<xsl:sequence select="fn:concat(fn:concat(fn:string($var51_cur/name), '@'), fn:string(.))"/>
								</host_name>
							</xsl:for-each>
							<display_name>
										<xsl:sequence select="fn:concat(fn:concat(fn:string(name), '@'), fn:string($var51_cur/id))"/>
							</display_name>
							<service_description>
								<xsl:sequence select="fn:string(name)"/>
							</service_description>
							<config_name>
										<xsl:sequence select="fn:concat(fn:concat(fn:string(name), '@'), fn:string($var51_cur/id))"/>
							</config_name>
							<check_command>
								<xsl:choose>
									<xsl:when test="fn:exists($var41_args)">
										<xsl:sequence select="fn:concat(fn:concat($var48_resultof_concat, &apos;&amp;&apos;), replace(replace(fn:string($var41_args), &apos;,&apos;, &apos;&amp;&apos;), &apos;:&apos;, &apos;=&apos;))"/>
									</xsl:when>
									<xsl:otherwise>
										<xsl:sequence select="$var48_resultof_concat"/>
									</xsl:otherwise>
								</xsl:choose>
							</check_command>
							<xsl:for-each select="check_interval">
								<check_interval>
									<xsl:sequence select="xs:string(xs:integer(fn:string(.)))"/>
								</check_interval>
							</xsl:for-each>
							<xsl:for-each select="max_check_attempts">
								<max_check_attempts>
									<xsl:sequence select="xs:string(xs:integer(fn:string(.)))"/>
								</max_check_attempts>
							</xsl:for-each>
							<xsl:variable name="var49_result" as="xs:string">
								<xsl:choose>
									<xsl:when test="fn:exists($var42_status)">
										<xsl:sequence select="fn:string($var42_status)"/>
									</xsl:when>
									<xsl:otherwise>
										<xsl:sequence select="'Running'"/>
									</xsl:otherwise>
								</xsl:choose>
							</xsl:variable>
							<xsl:variable name="var50_result" as="xs:decimal">
								<xsl:choose>
									<xsl:when test="(fn:compare($var49_result, 'Stopped') = xs:decimal('0'))">
										<xsl:sequence select="xs:decimal('0')"/>
									</xsl:when>
									<xsl:otherwise>
										<xsl:sequence select="xs:decimal('1')"/>
									</xsl:otherwise>
								</xsl:choose>
							</xsl:variable>
							<active_checks_enabled>
								<xsl:sequence select="xs:string(xs:integer($var50_result))"/>
							</active_checks_enabled>
						</service>
					</xsl:for-each>
				</xsl:for-each>
				<xsl:for-each select="$var2_tenants/tenant">
					<xsl:variable name="tenant_cur" as="node()" select="."/>
					<xsl:for-each select="monitor_info/metrics/metric">
					<xsl:variable name="var52_status" as="node()?" select="status"/>
					<xsl:variable name="var53_warningvalue" as="node()?" select="warning_value"/>
					<xsl:variable name="var54_criticalvalue" as="node()?" select="critical_value"/>
					<xsl:variable name="var55_args" as="node()?" select="args"/>
					<xsl:variable name="var56_resultof_exists" as="xs:boolean" select="fn:exists($var54_criticalvalue)"/>
					<xsl:variable name="var57_resultof_exists" as="xs:boolean" select="fn:exists($var53_warningvalue)"/>
					<xsl:variable name="var58_val" as="xs:string">
						<xsl:choose>
							<xsl:when test="$var56_resultof_exists">
								<xsl:sequence select="fn:concat('c=', fn:string($var54_criticalvalue))"/>
							</xsl:when>
							<xsl:otherwise>
								<xsl:sequence select="'c=0'"/>
							</xsl:otherwise>
						</xsl:choose>
					</xsl:variable>
					<xsl:variable name="var59_val" as="xs:string">
						<xsl:choose>
							<xsl:when test="$var57_resultof_exists">
								<xsl:sequence select="fn:concat('w=', fn:string($var53_warningvalue))"/>
							</xsl:when>
							<xsl:otherwise>
								<xsl:sequence select="'w=0'"/>
							</xsl:otherwise>
						</xsl:choose>
					</xsl:variable>
					<xsl:variable name="var60_resultof_concat" as="xs:string" select="fn:concat($var59_val, &apos;&amp;&apos;)"/>
					<xsl:variable name="var61_resultof_concat" as="xs:string" select="fn:concat($var60_resultof_concat, $var58_val)"/>
					<service>
						<host_name>
							<xsl:sequence select="fn:string($tenant_cur/runOn)"/>
						</host_name>
						<display_name>
										<xsl:sequence select="fn:concat(fn:concat(fn:string(name), '@'), fn:string($tenant_cur/id))"/>
							</display_name>
							<config_name>
										<xsl:sequence select="fn:concat(fn:concat(fn:string(name), '@'), fn:string($tenant_cur/id))"/>
							</config_name>
						<service_description>
							<xsl:sequence select="fn:string(name)"/>
						</service_description>
						<servicegroups>
								<xsl:sequence select="fn:concat(fn:concat(fn:string($tenant_cur/name), '@'), fn:string($tenant_cur/id))"/>
							</servicegroups>
						<check_command>
							<xsl:choose>
								<xsl:when test="fn:exists($var55_args)">
									<xsl:sequence select="fn:concat(fn:concat($var61_resultof_concat, &apos;&amp;&apos;), replace(replace(fn:string($var55_args), &apos;,&apos;, &apos;&amp;&apos;), &apos;:&apos;, &apos;=&apos;))"/>
								</xsl:when>
								<xsl:otherwise>
									<xsl:sequence select="$var61_resultof_concat"/>
								</xsl:otherwise>
							</xsl:choose>
						</check_command>
						<xsl:for-each select="check_interval">
							<check_interval>
								<xsl:sequence select="xs:string(xs:integer(fn:string(.)))"/>
							</check_interval>
						</xsl:for-each>
						<xsl:for-each select="max_check_attempts">
							<max_check_attempts>
								<xsl:sequence select="xs:string(xs:integer(fn:string(.)))"/>
							</max_check_attempts>
						</xsl:for-each>
						<xsl:variable name="var62_result" as="xs:string">
							<xsl:choose>
								<xsl:when test="fn:exists($var52_status)">
									<xsl:sequence select="fn:string($var52_status)"/>
								</xsl:when>
								<xsl:otherwise>
									<xsl:sequence select="'Running'"/>
								</xsl:otherwise>
							</xsl:choose>
						</xsl:variable>
						<xsl:variable name="var63_result" as="xs:decimal">
							<xsl:choose>
								<xsl:when test="(fn:compare($var62_result, 'Stopped') = xs:decimal('0'))">
									<xsl:sequence select="xs:decimal('0')"/>
								</xsl:when>
								<xsl:otherwise>
									<xsl:sequence select="xs:decimal('1')"/>
								</xsl:otherwise>
							</xsl:choose>
						</xsl:variable>
						<active_checks_enabled>
							<xsl:sequence select="xs:string(xs:integer($var63_result))"/>
						</active_checks_enabled>
					</service>
				</xsl:for-each>
				</xsl:for-each>
			</xsl:for-each>
		</nagios>
	</xsl:template>
</xsl:stylesheet><!-- Stylus Studio meta-information - (c) 2004-2009. Progress Software Corporation. All rights reserved.

<metaInformation>
	<scenarios>
		<scenario default="yes" name="smSysConf.xml" userelativepaths="yes" externalpreview="no" url="..\..\test\schema\smBusConf.xml" htmlbaseurl="" outputurl="" processortype="saxon8" useresolver="yes" profilemode="0" profiledepth="" profilelength=""
		          urlprofilexml="" commandline="" additionalpath="" additionalclasspath="" postprocessortype="none" postprocesscommandline="" postprocessadditionalpath="" postprocessgeneratedext="" validateoutput="no" validator="internal"
		          customvalidator="">
			<advancedProp name="sInitialMode" value=""/>
			<advancedProp name="schemaCache" value="||"/>
			<advancedProp name="bXsltOneIsOkay" value="true"/>
			<advancedProp name="bSchemaAware" value="true"/>
			<advancedProp name="bGenerateByteCode" value="true"/>
			<advancedProp name="bXml11" value="false"/>
			<advancedProp name="iValidation" value="0"/>
			<advancedProp name="bExtensions" value="true"/>
			<advancedProp name="iWhitespace" value="0"/>
			<advancedProp name="sInitialTemplate" value=""/>
			<advancedProp name="bTinyTree" value="true"/>
			<advancedProp name="xsltVersion" value="2.0"/>
			<advancedProp name="bWarnings" value="true"/>
			<advancedProp name="bUseDTD" value="false"/>
			<advancedProp name="iErrorHandling" value="fatal"/>
		</scenario>
	</scenarios>
	<MapperMetaTag>
		<MapperInfo srcSchemaPathIsRelative="yes" srcSchemaInterpretAsXML="no" destSchemaPath="" destSchemaRoot="" destSchemaPathIsRelative="yes" destSchemaInterpretAsXML="no">
			<SourceSchema srcSchemaPath="..\..\test\schema\smSysConf.xml" srcSchemaRoot="configuration" AssociatedInstance="" loaderFunction="document" loaderFunctionUsesURI="no"/>
		</MapperInfo>
		<MapperBlockPosition>
			<template match="/">
				<block path="nagios/xsl:for-each" x="384" y="54"/>
				<block path="nagios/xsl:for-each/xsl:for-each" x="434" y="78"/>
				<block path="nagios/xsl:for-each/xsl:for-each/xsl:for-each" x="384" y="108"/>
				<block path="nagios/xsl:for-each/xsl:for-each/xsl:for-each/host/xsl:attribute/xsl:value-of" x="304" y="126"/>
				<block path="nagios/xsl:for-each/xsl:for-each/xsl:for-each/host/xsl:for-each" x="384" y="144"/>
				<block path="nagios/xsl:for-each/xsl:for-each/xsl:for-each/host/xsl:for-each[1]" x="24" y="58"/>
				<block path="nagios/xsl:for-each/xsl:for-each/xsl:for-each/host/xsl:for-each[1]/xsl:if/!=[0]" x="338" y="214"/>
				<block path="nagios/xsl:for-each/xsl:for-each/xsl:for-each/host/xsl:for-each[1]/xsl:if" x="384" y="216"/>
				<block path="nagios/xsl:for-each/xsl:for-each/xsl:for-each/host/use_variables/xsl:if" x="384" y="288"/>
				<block path="nagios/xsl:for-each/xsl:for-each/xsl:for-each/host/use_variables/xsl:if[1]" x="424" y="279"/>
				<block path="nagios/xsl:for-each/xsl:for-each/xsl:for-each/host/use_variables/xsl:if[2]" x="344" y="279"/>
				<block path="nagios/xsl:for-each/xsl:for-each[1]" x="274" y="68"/>
				<block path="nagios/xsl:for-each/xsl:for-each[1]/xsl:for-each" x="344" y="98"/>
				<block path="nagios/xsl:for-each/xsl:for-each[1]/xsl:for-each/host/xsl:for-each" x="384" y="18"/>
				<block path="nagios/xsl:for-each/xsl:for-each[1]/xsl:for-each/host/xsl:for-each[1]" x="344" y="18"/>
				<block path="nagios/xsl:for-each/xsl:for-each[1]/xsl:for-each/host/xsl:for-each[2]" x="224" y="18"/>
				<block path="nagios/xsl:for-each/xsl:for-each[1]/xsl:for-each/host/xsl:for-each[2]/xsl:if/!=[0]" x="138" y="77"/>
				<block path="nagios/xsl:for-each/xsl:for-each[1]/xsl:for-each/host/xsl:for-each[2]/xsl:if" x="184" y="79"/>
				<block path="nagios/xsl:for-each/xsl:for-each[2]" x="224" y="98"/>
				<block path="nagios/xsl:for-each/xsl:for-each[2]/servicegroup/xsl:for-each" x="184" y="18"/>
				<block path="nagios/xsl:for-each/xsl:for-each[2]/servicegroup/xsl:for-each[1]" x="64" y="18"/>
				<block path="nagios/xsl:for-each/xsl:for-each[3]" x="184" y="98"/>
				<block path="nagios/xsl:for-each/xsl:for-each[4]" x="74" y="68"/>
				<block path="nagios/xsl:for-each/xsl:for-each[4]/xsl:for-each" x="144" y="98"/>
				<block path="nagios/xsl:for-each/xsl:for-each[4]/xsl:for-each/service/xsl:for-each" x="264" y="160"/>
				<block path="nagios/xsl:for-each/xsl:for-each[4]/xsl:for-each/service/xsl:for-each/xsl:for-each" x="294" y="8"/>
				<block path="nagios/xsl:for-each/xsl:for-each[4]/xsl:for-each/service/xsl:for-each/xsl:for-each/xsl:if/=[0]" x="38" y="116"/>
				<block path="nagios/xsl:for-each/xsl:for-each[4]/xsl:for-each/service/xsl:for-each/xsl:for-each/xsl:if" x="84" y="118"/>
				<block path="nagios/xsl:for-each/xsl:for-each[4]/xsl:for-each/service/xsl:for-each/xsl:for-each/xsl:if/xsl:choose" x="274" y="108"/>
				<block path="nagios/xsl:for-each/xsl:for-each[4]/xsl:for-each/service/xsl:for-each/xsl:for-each/xsl:if/xsl:choose/&gt;[0]" x="228" y="102"/>
				<block path="nagios/xsl:for-each/xsl:for-each[4]/xsl:for-each/service/xsl:for-each/xsl:for-each/xsl:if/xsl:choose/&gt;[0]/string-length[0]" x="182" y="96"/>
				<block path="nagios/xsl:for-each/xsl:for-each[4]/xsl:for-each/service/xsl:for-each/xsl:for-each/xsl:if/xsl:choose/xsl:when/xsl:if/=[0]" x="378" y="136"/>
				<block path="nagios/xsl:for-each/xsl:for-each[4]/xsl:for-each/service/xsl:for-each/xsl:for-each/xsl:if/xsl:choose/xsl:when/xsl:if" x="424" y="138"/>
				<block path="nagios/xsl:for-each/xsl:for-each[4]/xsl:for-each/service/xsl:for-each/xsl:for-each/xsl:if/xsl:choose/xsl:when/xsl:if/host_name/xsl:value-of" x="24" y="18"/>
				<block path="nagios/xsl:for-each/xsl:for-each[4]/xsl:for-each/service/xsl:for-each/xsl:for-each/xsl:if/xsl:choose/xsl:otherwise/host_name/xsl:value-of" x="344" y="138"/>
				<block path="nagios/xsl:for-each/xsl:for-each[4]/xsl:for-each/xsl:for-each" x="24" y="98"/>
				<block path="nagios/xsl:for-each/xsl:for-each[4]/xsl:for-each/xsl:for-each/service/xsl:for-each" x="124" y="158"/>
				<block path="nagios/xsl:for-each/xsl:for-each[4]/xsl:for-each/xsl:for-each/service/xsl:for-each/xsl:for-each" x="34" y="148"/>
				<block path="nagios/xsl:for-each/xsl:for-each[4]/xsl:for-each/xsl:for-each/service/xsl:for-each/xsl:for-each/xsl:if/=[0]" x="138" y="136"/>
				<block path="nagios/xsl:for-each/xsl:for-each[4]/xsl:for-each/xsl:for-each/service/xsl:for-each/xsl:for-each/xsl:if" x="184" y="138"/>
				<block path="nagios/xsl:for-each/xsl:for-each[4]/xsl:for-each/xsl:for-each/service/xsl:for-each/xsl:for-each/xsl:if/host_name/xsl:value-of" x="224" y="138"/>
				<block path="nagios/xsl:for-each/xsl:for-each[4]/xsl:for-each/xsl:for-each/service/xsl:for-each[1]" x="384" y="98"/>
				<block path="nagios/xsl:for-each/xsl:for-each[4]/xsl:for-each/xsl:for-each/service/xsl:for-each[2]" x="384" y="98"/>
				<block path="nagios/xsl:for-each/xsl:for-each[5]" x="434" y="28"/>
				<block path="nagios/xsl:for-each/xsl:for-each[5]/xsl:for-each" x="344" y="58"/>
				<block path="nagios/xsl:for-each/xsl:for-each[5]/xsl:for-each/service/xsl:for-each" x="384" y="98"/>
				<block path="nagios/xsl:for-each/xsl:for-each[5]/xsl:for-each/service/xsl:for-each[1]" x="384" y="98"/>
				<block path="nagios/xsl:for-each/xsl:for-each[5]/xsl:for-each/service/xsl:for-each[2]" x="384" y="98"/>
				<block path="nagios/xsl:for-each/xsl:for-each[6]" x="274" y="28"/>
				<block path="nagios/xsl:for-each/xsl:for-each[6]/xsl:for-each" x="224" y="58"/>
				<block path="nagios/xsl:for-each/xsl:for-each[6]/xsl:for-each/service/xsl:for-each" x="384" y="98"/>
				<block path="nagios/xsl:for-each/xsl:for-each[6]/xsl:for-each/service/xsl:for-each[1]" x="384" y="98"/>
				<block path="nagios/xsl:for-each/xsl:for-each[6]/xsl:for-each/service/xsl:for-each[2]" x="384" y="98"/>
				<block path="nagios/xsl:for-each/xsl:for-each[7]" x="114" y="28"/>
				<block path="nagios/xsl:for-each/xsl:for-each[7]/xsl:for-each" x="184" y="58"/>
				<block path="nagios/xsl:for-each/xsl:for-each[7]/xsl:for-each/service/xsl:for-each" x="384" y="98"/>
				<block path="nagios/xsl:for-each/xsl:for-each[7]/xsl:for-each/service/xsl:for-each[1]" x="384" y="98"/>
			</template>
		</MapperBlockPosition>
		<TemplateContext></TemplateContext>
		<MapperFilter side="source"></MapperFilter>
	</MapperMetaTag>
</metaInformation>
-->