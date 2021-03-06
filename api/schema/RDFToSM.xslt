<?xml version="1.0" encoding="UTF-8"?>
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
<xsl:stylesheet version="2.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:ns0="http://www.cloudicaro.it/cloud_ontology/core#" xmlns:ns1="http://www.w3.org/1999/02/22-rdf-syntax-ns#" xmlns:xs="http://www.w3.org/2001/XMLSchema" xmlns:fn="http://www.w3.org/2005/xpath-functions" xmlns:ns2="http://xmlns.com/foaf/0.1/" exclude-result-prefixes="ns0 ns1 xs fn ns2">
	<xsl:output method="xml" encoding="UTF-8" indent="yes"/>
	<xsl:template match="/">
		<configuration>
			<xsl:attribute name="xsi:noNamespaceSchemaLocation" namespace="http://www.w3.org/2001/XMLSchema-instance" select="'E:/Icaro/data/sm.xsd'"/>
			<xsl:for-each select="ns1:RDF">
				<xsl:variable name="var24_cur" as="node()" select="."/>
				<xsl:for-each select="ns0:BusinessConfiguration">
					<xsl:variable name="var23_cur" as="node()" select="."/>
					<xsl:variable name="var1_User" as="node()*" select="$var24_cur/ns0:User"/>
					<xsl:variable name="var2_VirtualMachine" as="node()*" select="$var24_cur/ns0:VirtualMachine"/>
					<xsl:variable name="var3_resultof_first" as="node()" select="ns0:hasPart"/>
					<identifier>
						<xsl:sequence select="fn:string(ns0:hasIdentifier)"/>
					</identifier>
					<description>
						<xsl:sequence select="xs:string(xs:anyURI(fn:string(@ns1:about)))"/>
					</description>
					<name>
						<xsl:sequence select="fn:string(ns0:hasName)"/>
					</name>
					<xsl:for-each select="$var1_User[(xs:string(xs:anyURI(fn:string($var23_cur/ns0:createdBy/@ns1:resource))) = xs:string(xs:anyURI(fn:string(@ns1:about))))]">
						<contacts>
							<xsl:sequence select="fn:string(ns2:mbox)"/>
						</contacts>
					</xsl:for-each>
					<applications>
						<xsl:for-each select="$var3_resultof_first/ns0:IcaroApplication">
							<xsl:variable name="var15_cur" as="node()" select="."/>
							<application>
								<name>
									<xsl:sequence select="fn:string(ns0:hasName)"/>
								</name>
								<id>
									<xsl:sequence select="fn:string(ns0:hasIdentifier)"/>
								</id>
								<description>
									<xsl:sequence select="xs:string(xs:anyURI(fn:string(@ns1:about)))"/>
								</description>
								<xsl:variable name="var6_resultof_map" as="xs:string*">
									<xsl:for-each select="$var1_User[(xs:string(xs:anyURI(fn:string($var15_cur/ns0:createdBy/@ns1:resource))) = xs:string(xs:anyURI(fn:string(@ns1:about))))]">
										<xsl:sequence select="fn:string(ns2:mbox)"/>
									</xsl:for-each>
								</xsl:variable>
								<xsl:for-each select="$var6_resultof_map">
									<xsl:variable name="var5_resultof_map" as="xs:string*">
										<xsl:for-each select="$var15_cur/ns0:administeredBy">
											<xsl:variable name="var4_cur" as="node()" select="."/>
											<xsl:for-each select="$var1_User[(xs:string(xs:anyURI(fn:string($var4_cur/@ns1:resource))) = xs:string(xs:anyURI(fn:string(@ns1:about))))]">
												<xsl:sequence select="fn:string(ns2:mbox)"/>
											</xsl:for-each>
										</xsl:for-each>
									</xsl:variable>
									<contacts>
										<xsl:sequence select="fn:concat(fn:concat(., ';'), fn:string-join($var5_resultof_map, ';'))"/>
									</contacts>
								</xsl:for-each>
								<services>
									<xsl:for-each select="ns0:needs/ns0:IcaroService">
										<xsl:variable name="var14_cur" as="node()" select="."/>
										<xsl:variable name="var7_hasMonitorIPAddress" as="node()?" select="ns0:hasMonitorIPAddress"/>
										<xsl:variable name="var8_resultof_exists" as="xs:boolean" select="fn:exists($var7_hasMonitorIPAddress)"/>
										<xsl:variable name="var9_resultof_substring_after" as="xs:string" select="fn:substring-after(xs:string(xs:anyURI(fn:string(ns1:type/@ns1:resource))), '#')"/>
										<service>
											<id>
												<xsl:sequence select="fn:string(ns0:hasIdentifier)"/>
											</id>
											<name>
												<xsl:sequence select="fn:string(ns0:hasName)"/>
											</name>
											<type>
												<xsl:sequence select="$var9_resultof_substring_after"/>
											</type>
											<description>
												<xsl:sequence select="xs:string(xs:anyURI(fn:string(@ns1:about)))"/>
											</description>
											<xsl:variable name="var13_result" as="xs:boolean">
												<xsl:choose>
													<xsl:when test="$var8_resultof_exists">
														<xsl:variable name="var11_resultof_map" as="xs:boolean?">
															<xsl:for-each select="$var7_hasMonitorIPAddress">
																<xsl:variable name="var10_resultof_map" as="xs:boolean*">
																	<xsl:for-each select="node()">
																		<xsl:sequence select="fn:boolean(self::text())"/>
																	</xsl:for-each>
																</xsl:variable>
																<xsl:sequence select="fn:exists($var10_resultof_map[.])"/>
															</xsl:for-each>
														</xsl:variable>
														<xsl:sequence select="fn:exists($var11_resultof_map[.])"/>
													</xsl:when>
													<xsl:otherwise>
														<xsl:sequence select="fn:true()"/>
													</xsl:otherwise>
												</xsl:choose>
											</xsl:variable>
											<xsl:if test="$var13_result">
												<ip_address>
													<xsl:choose>
														<xsl:when test="$var8_resultof_exists">
															<xsl:variable name="var12_resultof_map" as="xs:string*">
																<xsl:for-each select="($var7_hasMonitorIPAddress/node())[fn:boolean(self::text())]">
																	<xsl:sequence select="fn:string(.)"/>
																</xsl:for-each>
															</xsl:variable>
															<xsl:sequence select="xs:string(fn:string-join(for $x in $var12_resultof_map return xs:string($x), ' '))"/>
														</xsl:when>
														<xsl:otherwise>
															<xsl:sequence select="''"/>
														</xsl:otherwise>
													</xsl:choose>
												</ip_address>
											</xsl:if>
											<service_group>
												<xsl:sequence select="$var9_resultof_substring_after"/>
											</service_group>
											<xsl:for-each select="$var2_VirtualMachine[(xs:string(xs:anyURI(fn:string(@ns1:about))) = xs:string(xs:anyURI(fn:string($var14_cur/ns0:runsOnVM/@ns1:resource))))]">
												<run_on>
													<xsl:sequence select="fn:string(ns0:hasIdentifier)"/>
												</run_on>
											</xsl:for-each>
											<monitor_info>
												<xsl:attribute name="type" select="'service'"/>
												<metrics>
													<xsl:for-each select="ns0:hasMonitorInfo/ns0:MonitorInfo">
														<metric>
															<name>
																<xsl:sequence select="fn:string(ns0:hasMetricName)"/>
															</name>
															<xsl:for-each select="ns0:hasCriticalValue">
																<critical_value>
																	<xsl:sequence select="xs:string(xs:decimal(fn:string(.)))"/>
																</critical_value>
															</xsl:for-each>
															<xsl:for-each select="ns0:hasWarningValue">
																<warning_value>
																	<xsl:sequence select="xs:string(xs:decimal(fn:string(.)))"/>
																</warning_value>
															</xsl:for-each>
															<xsl:for-each select="ns0:hasArguments">
																<args>
																	<xsl:sequence select="fn:string(.)"/>
																</args>
															</xsl:for-each>
															<xsl:for-each select="ns0:hasMaxCheckAttempts">
																<max_check_attempts>
																	<xsl:sequence select="xs:string(xs:integer(fn:string(.)))"/>
																</max_check_attempts>
															</xsl:for-each>
															<xsl:for-each select="ns0:hasCheckInterval">
																<check_interval>
																	<xsl:sequence select="xs:string(xs:integer(fn:string(.)))"/>
																</check_interval>
															</xsl:for-each>
														</metric>
													</xsl:for-each>
												</metrics>
											</monitor_info>
											<xsl:for-each select="ns0:usesTcpPort">
												<port>
													<xsl:sequence select="xs:string(xs:integer(fn:string(.)))"/>
												</port>
											</xsl:for-each>
											<xsl:for-each select="ns0:hasProcessName">
												<process_name>
													<xsl:sequence select="fn:string(.)"/>
												</process_name>
											</xsl:for-each>
											<xsl:for-each select="ns0:hasAuthUserName">
												<auth_user>
													<xsl:sequence select="fn:string(.)"/>
												</auth_user>
											</xsl:for-each>
											<xsl:for-each select="ns0:hasAuthUserPassword">
												<auth_pwd>
													<xsl:sequence select="fn:string(.)"/>
												</auth_pwd>
											</xsl:for-each>
										</service>
									</xsl:for-each>
								</services>
							</application>
						</xsl:for-each>
					</applications>
					<tenants>
						<xsl:for-each select="$var3_resultof_first/ns0:IcaroTenant">
							<xsl:variable name="var19_cur" as="node()" select="."/>
							<tenant>
								<name>
									<xsl:sequence select="fn:string(ns0:hasName)"/>
								</name>
								<id>
									<xsl:sequence select="fn:string(ns0:hasIdentifier)"/>
								</id>
								<description>
									<xsl:sequence select="xs:string(xs:anyURI(fn:string(@ns1:about)))"/>
								</description>
								<xsl:variable name="var18_resultof_map" as="xs:string*">
									<xsl:for-each select="$var1_User[(xs:string(xs:anyURI(fn:string($var19_cur/ns0:createdBy/@ns1:resource))) = xs:string(xs:anyURI(fn:string(@ns1:about))))]">
										<xsl:sequence select="fn:string(ns2:mbox)"/>
									</xsl:for-each>
								</xsl:variable>
								<xsl:for-each select="$var18_resultof_map">
									<xsl:variable name="var17_resultof_map" as="xs:string*">
										<xsl:for-each select="$var19_cur/ns0:administeredBy">
											<xsl:variable name="var16_cur" as="node()" select="."/>
											<xsl:for-each select="$var1_User[(xs:string(xs:anyURI(fn:string($var16_cur/@ns1:resource))) = xs:string(xs:anyURI(fn:string(@ns1:about))))]">
												<xsl:sequence select="fn:string(ns2:mbox)"/>
											</xsl:for-each>
										</xsl:for-each>
									</xsl:variable>
									<contacts>
										<xsl:sequence select="fn:concat(fn:concat(., ';'), fn:string-join($var17_resultof_map, ';'))"/>
									</contacts>
								</xsl:for-each>
								<monitor_info>
									<xsl:attribute name="type" select="'tenant'"/>
									<metrics>
										<xsl:for-each select="ns0:hasMonitorInfo/ns0:MonitorInfo">
											<metric>
												<name>
													<xsl:sequence select="fn:string(ns0:hasMetricName)"/>
												</name>
												<xsl:for-each select="ns0:hasCriticalValue">
													<critical_value>
														<xsl:sequence select="xs:string(xs:decimal(fn:string(.)))"/>
													</critical_value>
												</xsl:for-each>
												<xsl:for-each select="ns0:hasWarningValue">
													<warning_value>
														<xsl:sequence select="xs:string(xs:decimal(fn:string(.)))"/>
													</warning_value>
												</xsl:for-each>
												<xsl:for-each select="ns0:hasArguments">
													<args>
														<xsl:sequence select="fn:string(.)"/>
													</args>
												</xsl:for-each>
												<xsl:for-each select="ns0:hasMaxCheckAttempts">
													<max_check_attempts>
														<xsl:sequence select="xs:string(xs:integer(fn:string(.)))"/>
													</max_check_attempts>
												</xsl:for-each>
												<xsl:for-each select="ns0:hasCheckInterval">
													<check_interval>
														<xsl:sequence select="xs:string(xs:integer(fn:string(.)))"/>
													</check_interval>
												</xsl:for-each>
											</metric>
										</xsl:for-each>
									</metrics>
								</monitor_info>
								<runOn>
									<xsl:sequence select="xs:string(xs:anyURI(fn:string(ns0:isTenantOf/@ns1:resource)))"/>
								</runOn>
							</tenant>
						</xsl:for-each>
					</tenants>
					<hosts>
						<xsl:for-each select="$var2_VirtualMachine">
							<host>
								<id>
									<xsl:sequence select="fn:string(ns0:hasIdentifier)"/>
								</id>
								<name>
									<xsl:sequence select="fn:string(ns0:hasName)"/>
								</name>
								<os>
									<xsl:sequence select="fn:substring-after(xs:string(xs:anyURI(fn:string(ns0:hasOS/@ns1:resource))), '#')"/>
								</os>
								<type>vmhost</type>
								<xsl:for-each select="ns0:hasNetworkAdapter/ns0:NetworkAdapter">
									<ip_address>
										<xsl:sequence select="fn:string(ns0:hasIPAddress)"/>
									</ip_address>
								</xsl:for-each>
								<monitor_info>
									<xsl:attribute name="type" select="'host'"/>
									<metrics>
										<xsl:for-each select="ns0:hasMonitorInfo/ns0:MonitorInfo">
											<metric>
												<name>
													<xsl:sequence select="fn:string(ns0:hasMetricName)"/>
												</name>
												<xsl:for-each select="ns0:hasCriticalValue">
													<critical_value>
														<xsl:sequence select="xs:string(xs:decimal(fn:string(.)))"/>
													</critical_value>
												</xsl:for-each>
												<xsl:for-each select="ns0:hasWarningValue">
													<warning_value>
														<xsl:sequence select="xs:string(xs:decimal(fn:string(.)))"/>
													</warning_value>
												</xsl:for-each>
											</metric>
										</xsl:for-each>
									</metrics>
								</monitor_info>
								<parent_host>
									<xsl:sequence select="xs:string(xs:anyURI(fn:string(ns0:isPartOf/@ns1:resource)))"/>
								</parent_host>
								<xsl:for-each select="ns0:isInDomain">
									<domain_name>
										<xsl:sequence select="fn:string(.)"/>
									</domain_name>
								</xsl:for-each>
								<xsl:for-each select="ns0:hasAuthUserPassword">
									<auth_pwd>
										<xsl:sequence select="fn:string(.)"/>
									</auth_pwd>
								</xsl:for-each>
								<xsl:for-each select="ns0:hasAuthUserName">
									<auth_user>
										<xsl:sequence select="fn:string(.)"/>
									</auth_user>
								</xsl:for-each>
							</host>
						</xsl:for-each>
						<xsl:for-each select="$var24_cur/ns0:HostMachine">
							<host>
								<id>
									<xsl:sequence select="fn:string(ns0:hasIdentifier)"/>
								</id>
								<name>
									<xsl:sequence select="fn:string(ns0:hasName)"/>
								</name>
								<os>
									<xsl:sequence select="fn:substring-after(xs:string(xs:anyURI(fn:string(ns0:hasOS/@ns1:resource))), '#')"/>
								</os>
								<type>host</type>
								<xsl:for-each select="ns0:hasNetworkAdapter/ns0:NetworkAdapter">
									<ip_address>
										<xsl:sequence select="fn:string(ns0:hasIPAddress)"/>
									</ip_address>
								</xsl:for-each>
								<monitor_info>
									<xsl:attribute name="type" select="'host'"/>
									<metrics>
										<xsl:for-each select="ns0:hasMonitorInfo/ns0:MonitorInfo">
											<metric>
												<name>
													<xsl:sequence select="fn:string(ns0:hasMetricName)"/>
												</name>
												<xsl:for-each select="ns0:hasCriticalValue">
													<critical_value>
														<xsl:sequence select="xs:string(xs:decimal(fn:string(.)))"/>
													</critical_value>
												</xsl:for-each>
												<xsl:for-each select="ns0:hasWarningValue">
													<warning_value>
														<xsl:sequence select="xs:string(xs:decimal(fn:string(.)))"/>
													</warning_value>
												</xsl:for-each>
											</metric>
										</xsl:for-each>
									</metrics>
								</monitor_info>
								<host_group>
									<xsl:sequence select="xs:string(xs:anyURI(fn:string(ns0:isPartOf/@ns1:resource)))"/>
								</host_group>
								<xsl:for-each select="ns0:isInDomain">
									<domain_name>
										<xsl:sequence select="fn:string(.)"/>
									</domain_name>
								</xsl:for-each>
								<xsl:for-each select="ns0:hasAuthUserPassword">
									<auth_pwd>
										<xsl:sequence select="fn:string(.)"/>
									</auth_pwd>
								</xsl:for-each>
								<xsl:for-each select="ns0:hasAuthUserName">
									<auth_user>
										<xsl:sequence select="fn:string(.)"/>
									</auth_user>
								</xsl:for-each>
								<description>
									<xsl:sequence select="xs:string(xs:anyURI(fn:string(@ns1:about)))"/>
								</description>
							</host>
						</xsl:for-each>
					</hosts>
					<devices>
						<xsl:for-each select="$var24_cur/ns0:ExternalStorage">
							<xsl:variable name="var20_val" as="node()+">
								<id>
									<xsl:sequence select="fn:string(ns0:hasIdentifier)"/>
								</id>
								<device_type>
									<xsl:sequence select="fn:substring-after(xs:string(fn:node-name(.)), 'icr:')"/>
								</device_type>
								<type>physical</type>
								<xsl:for-each select="ns0:hasNetworkAdapter/ns0:NetworkAdapter">
									<ip_address>
										<xsl:sequence select="fn:string(ns0:hasIPAddress)"/>
									</ip_address>
								</xsl:for-each>
								<name>
									<xsl:sequence select="fn:string(ns0:hasName)"/>
								</name>
								<monitor_info>
									<xsl:attribute name="type" select="'device'"/>
									<metrics>
										<xsl:for-each select="ns0:hasMonitorInfo/ns0:MonitorInfo">
											<metric>
												<name>
													<xsl:sequence select="fn:string(ns0:hasMetricName)"/>
												</name>
												<xsl:for-each select="ns0:hasCriticalValue">
													<critical_value>
														<xsl:sequence select="xs:string(xs:decimal(fn:string(.)))"/>
													</critical_value>
												</xsl:for-each>
												<xsl:for-each select="ns0:hasWarningValue">
													<warning_value>
														<xsl:sequence select="xs:string(xs:decimal(fn:string(.)))"/>
													</warning_value>
												</xsl:for-each>
												<xsl:for-each select="ns0:hasArguments">
													<args>
														<xsl:sequence select="fn:string(.)"/>
													</args>
												</xsl:for-each>
												<xsl:for-each select="ns0:hasMaxCheckAttempts">
													<max_check_attempts>
														<xsl:sequence select="xs:string(xs:integer(fn:string(.)))"/>
													</max_check_attempts>
												</xsl:for-each>
												<xsl:for-each select="ns0:hasCheckInterval">
													<check_interval>
														<xsl:sequence select="xs:string(xs:integer(fn:string(.)))"/>
													</check_interval>
												</xsl:for-each>
											</metric>
										</xsl:for-each>
									</metrics>
								</monitor_info>
								<xsl:for-each select="ns0:hasModelName">
									<model>
										<xsl:sequence select="fn:string(.)"/>
									</model>
								</xsl:for-each>
								<description>
									<xsl:sequence select="xs:string(xs:anyURI(fn:string(@ns1:about)))"/>
								</description>
							</xsl:variable>
							<device>
								<xsl:sequence select="$var20_val"/>
							</device>
						</xsl:for-each>
						<xsl:for-each select="$var24_cur/ns0:Firewall">
							<xsl:variable name="var21_val" as="node()+">
								<id>
									<xsl:sequence select="fn:string(ns0:hasIdentifier)"/>
								</id>
								<device_type>
									<xsl:sequence select="fn:substring-after(xs:string(fn:node-name(.)), 'icr:')"/>
								</device_type>
								<type>physical</type>
								<xsl:for-each select="ns0:hasNetworkAdapter/ns0:NetworkAdapter">
									<ip_address>
										<xsl:sequence select="fn:string(ns0:hasIPAddress)"/>
									</ip_address>
								</xsl:for-each>
								<name>
									<xsl:sequence select="fn:string(ns0:hasName)"/>
								</name>
								<monitor_info>
									<xsl:attribute name="type" select="'device'"/>
									<metrics>
										<xsl:for-each select="ns0:hasMonitorInfo/ns0:MonitorInfo">
											<metric>
												<name>
													<xsl:sequence select="fn:string(ns0:hasMetricName)"/>
												</name>
												<xsl:for-each select="ns0:hasCriticalValue">
													<critical_value>
														<xsl:sequence select="xs:string(xs:decimal(fn:string(.)))"/>
													</critical_value>
												</xsl:for-each>
												<xsl:for-each select="ns0:hasWarningValue">
													<warning_value>
														<xsl:sequence select="xs:string(xs:decimal(fn:string(.)))"/>
													</warning_value>
												</xsl:for-each>
												<xsl:for-each select="ns0:hasArguments">
													<args>
														<xsl:sequence select="fn:string(.)"/>
													</args>
												</xsl:for-each>
												<xsl:for-each select="ns0:hasMaxCheckAttempts">
													<max_check_attempts>
														<xsl:sequence select="xs:string(xs:integer(fn:string(.)))"/>
													</max_check_attempts>
												</xsl:for-each>
												<xsl:for-each select="ns0:hasCheckInterval">
													<check_interval>
														<xsl:sequence select="xs:string(xs:integer(fn:string(.)))"/>
													</check_interval>
												</xsl:for-each>
											</metric>
										</xsl:for-each>
									</metrics>
								</monitor_info>
								<xsl:for-each select="ns0:hasModelName">
									<model>
										<xsl:sequence select="fn:string(.)"/>
									</model>
								</xsl:for-each>
								<description>
									<xsl:sequence select="xs:string(xs:anyURI(fn:string(@ns1:about)))"/>
								</description>
							</xsl:variable>
							<device>
								<xsl:sequence select="$var21_val"/>
							</device>
						</xsl:for-each>
						<xsl:for-each select="$var24_cur/ns0:Router">
							<xsl:variable name="var22_val" as="node()+">
								<id>
									<xsl:sequence select="fn:string(ns0:hasIdentifier)"/>
								</id>
								<device_type>
									<xsl:sequence select="fn:substring-after(xs:string(fn:node-name(.)), 'icr:')"/>
								</device_type>
								<type>physical</type>
								<xsl:for-each select="ns0:hasNetworkAdapter/ns0:NetworkAdapter">
									<ip_address>
										<xsl:sequence select="fn:string(ns0:hasIPAddress)"/>
									</ip_address>
								</xsl:for-each>
								<name>
									<xsl:sequence select="fn:string(ns0:hasName)"/>
								</name>
								<monitor_info>
									<xsl:attribute name="type" select="'device'"/>
									<metrics>
										<xsl:for-each select="ns0:hasMonitorInfo/ns0:MonitorInfo">
											<metric>
												<name>
													<xsl:sequence select="fn:string(ns0:hasMetricName)"/>
												</name>
												<xsl:for-each select="ns0:hasCriticalValue">
													<critical_value>
														<xsl:sequence select="xs:string(xs:decimal(fn:string(.)))"/>
													</critical_value>
												</xsl:for-each>
												<xsl:for-each select="ns0:hasWarningValue">
													<warning_value>
														<xsl:sequence select="xs:string(xs:decimal(fn:string(.)))"/>
													</warning_value>
												</xsl:for-each>
												<xsl:for-each select="ns0:hasArguments">
													<args>
														<xsl:sequence select="fn:string(.)"/>
													</args>
												</xsl:for-each>
												<xsl:for-each select="ns0:hasMaxCheckAttempts">
													<max_check_attempts>
														<xsl:sequence select="xs:string(xs:integer(fn:string(.)))"/>
													</max_check_attempts>
												</xsl:for-each>
												<xsl:for-each select="ns0:hasCheckInterval">
													<check_interval>
														<xsl:sequence select="xs:string(xs:integer(fn:string(.)))"/>
													</check_interval>
												</xsl:for-each>
											</metric>
										</xsl:for-each>
									</metrics>
								</monitor_info>
								<xsl:for-each select="ns0:hasModelName">
									<model>
										<xsl:sequence select="fn:string(.)"/>
									</model>
								</xsl:for-each>
								<description>
									<xsl:sequence select="xs:string(xs:anyURI(fn:string(@ns1:about)))"/>
								</description>
							</xsl:variable>
							<device>
								<xsl:sequence select="$var22_val"/>
							</device>
						</xsl:for-each>
					</devices>
				</xsl:for-each>
			</xsl:for-each>
		</configuration>
	</xsl:template>
</xsl:stylesheet>
