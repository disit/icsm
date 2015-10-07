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
<xsl:stylesheet version="2.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:ns0="http://www.cloudicaro.it/cloud_ontology/core#" xmlns:ns1="http://www.w3.org/1999/02/22-rdf-syntax-ns#" xmlns:xs="http://www.w3.org/2001/XMLSchema" xmlns:fn="http://www.w3.org/2005/xpath-functions" exclude-result-prefixes="ns0 ns1 xs fn">
	<xsl:output method="xml" encoding="UTF-8" byte-order-mark="no" indent="yes"/>
	<xsl:template match="/">
		<configuration>
			<xsl:attribute name="xsi:noNamespaceSchemaLocation" namespace="http://www.w3.org/2001/XMLSchema-instance" select="'F:/PROGRA~2/APACHE~1/Apache2.2/htdocs/icaroSVN/api/schema/sm.xsd'"/>
			<xsl:for-each select="ns1:RDF">
				<xsl:variable name="var1_cur" as="node()" select="."/>
				<xsl:for-each select="ns0:DataCenter">
					<identifier>
						<xsl:sequence select="fn:string(ns0:hasIdentifier)"/>
					</identifier>
					<description>
						<xsl:sequence select="xs:string(xs:anyURI(fn:string(@ns1:about)))"/>
					</description>
					<name>
						<xsl:sequence select="fn:string(ns0:hasName)"/>
					</name>
					<bid></bid>
					<contacts></contacts>
					<type>System</type>
					<hosts>
						<xsl:for-each select="$var1_cur/ns0:VirtualMachine">
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
								<monitor_ip_address>
									<xsl:choose>
										<xsl:when test="ns0:hasMonitorIPAddress">
											<xsl:sequence select="fn:string(ns0:hasMonitorIPAddress)"/>
										</xsl:when>
										<xsl:otherwise>
											<xsl:sequence select="fn:string(ns0:hasNetworkAdapter/ns0:NetworkAdapter[1]/ns0:hasIPAddress)"/>
										</xsl:otherwise>
									</xsl:choose>
								</monitor_ip_address>
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
						<xsl:for-each select="$var1_cur/ns0:HostMachine">
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
								<monitor_ip_address>
									<xsl:choose>
										<xsl:when test="ns0:hasMonitorIPAddress">
											<xsl:sequence select="fn:string(ns0:hasMonitorIPAddress)"/>
										</xsl:when>
										<xsl:otherwise>
											<xsl:sequence select="fn:string(ns0:hasNetworkAdapter/ns0:NetworkAdapter[1]/ns0:hasIPAddress)"/>
										</xsl:otherwise>
									</xsl:choose>
								</monitor_ip_address>
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
						<host>
								<id>
									<xsl:sequence select="xs:string(xs:anyURI(fn:string(@ns1:about)))"/>
								</id>
								<name>
									<xsl:sequence select="fn:concat('HLM-',fn:string(ns0:hasIdentifier))"/>
								</name>
								<os>None</os>
								<type>HLMhost</type>
								<ip_address>0.0.0.0</ip_address>
								<monitor_ip_address>
									0.0.0.0
								</monitor_ip_address>
								<monitor_info type="host">
            						<metrics/>
        						</monitor_info>
								<host_group>HLM-Producers</host_group>
								<description>
									<xsl:sequence select="fn:concat('HLM-',fn:string(ns0:hasName))"/>
								</description>
							</host>
					</hosts>
					<devices>
						<xsl:for-each select="$var1_cur/ns0:ExternalStorage">
							<device>
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
								<monitor_ip_address>
									<xsl:choose>
										<xsl:when test="ns0:hasMonitorIPAddress">
											<xsl:sequence select="fn:string(ns0:hasMonitorIPAddress)"/>
										</xsl:when>
										<xsl:otherwise>
											<xsl:sequence select="fn:string(ns0:hasNetworkAdapter/ns0:NetworkAdapter[1]/ns0:hasIPAddress)"/>
										</xsl:otherwise>
									</xsl:choose>
								</monitor_ip_address>
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
							</device>
						</xsl:for-each>
						<xsl:for-each select="$var1_cur/ns0:Firewall">
							<device>
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
								<monitor_ip_address>
									<xsl:choose>
										<xsl:when test="ns0:hasMonitorIPAddress">
											<xsl:sequence select="fn:string(ns0:hasMonitorIPAddress)"/>
										</xsl:when>
										<xsl:otherwise>
											<xsl:sequence select="fn:string(ns0:hasNetworkAdapter/ns0:NetworkAdapter[1]/ns0:hasIPAddress)"/>
										</xsl:otherwise>
									</xsl:choose>
								</monitor_ip_address>
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
							</device>
						</xsl:for-each>
						<xsl:for-each select="$var1_cur/ns0:Router">
							<device>
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
								<monitor_ip_address>
									<xsl:choose>
										<xsl:when test="ns0:hasMonitorIPAddress">
											<xsl:sequence select="fn:string(ns0:hasMonitorIPAddress)"/>
										</xsl:when>
										<xsl:otherwise>
											<xsl:sequence select="fn:string(ns0:hasNetworkAdapter/ns0:NetworkAdapter[1]/ns0:hasIPAddress)"/>
										</xsl:otherwise>
									</xsl:choose>
								</monitor_ip_address>
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
							</device>
						</xsl:for-each>
					</devices>
				</xsl:for-each>
			</xsl:for-each>
		</configuration>
	</xsl:template>
</xsl:stylesheet>
