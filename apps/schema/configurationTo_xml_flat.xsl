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
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:xs="http://www.w3.org/2001/XMLSchema" exclude-result-prefixes="xs">
	<xsl:output method="xml" encoding="UTF-8" indent="yes"/>
	<xsl:template match="/">
		<root>
			<xsl:attribute name="xsi:noNamespaceSchemaLocation" namespace="http://www.w3.org/2001/XMLSchema-instance">C:/PROGRA~2/APACHE~2/Apache2.2/htdocs/icaro/_xml_flat.xsd</xsl:attribute>
			<xsl:for-each select="configuration">
				
				<xsl:variable name="var1_cid" select="@cid"/>
				<item>
					<xsl:attribute name="rel">root</xsl:attribute>
					<xsl:variable name="var2_cid">
						<xsl:if test="@cid">
							<xsl:value-of select="'1'"/>
						</xsl:if>
					</xsl:variable>
					<xsl:if test="string(boolean(string($var2_cid))) != 'false'">
						<xsl:attribute name="id">
							<xsl:value-of select="string($var1_cid)"/>
						</xsl:attribute>
					</xsl:if>
					<content>
						<name>
							<xsl:value-of select="concat(concat(concat(string(name), ' ('), string(identifier)), ')')"/>
						</name>
					</content>
				</item>
			</xsl:for-each>
			
			<xsl:for-each select="configuration">
				<xsl:variable name="var6_cur" select="."/>
				<xsl:variable name="viewUrl" select="@url"/>
				<xsl:for-each select="applications">
					<xsl:variable name="var3_cid" select="$var6_cur/@cid"/>
					<xsl:variable name="var4_cid">
						<xsl:if test="$var6_cur/@cid">
							<xsl:value-of select="'1'"/>
						</xsl:if>
					</xsl:variable>
					<xsl:variable name="var5_resultof_exists" select="boolean(string($var4_cid))"/>
					<item>
						<xsl:attribute name="rel">applications</xsl:attribute>
						
						<xsl:if test="string($var5_resultof_exists) != 'false'">
							<xsl:attribute name="parent_id">
								<xsl:value-of select="string($var3_cid)"/>
							</xsl:attribute>
						</xsl:if>
						<xsl:attribute name="id">applications</xsl:attribute>
						<content>
							<name>
								<xsl:if test="string($var5_resultof_exists) != 'false'">
									<xsl:attribute name="class">folder</xsl:attribute>
									<xsl:attribute name="href">
										<xsl:value-of select="concat(concat(concat($viewUrl, string($var6_cur/description)), '/'), 'applications')"/>
									</xsl:attribute>
								</xsl:if>
								<xsl:value-of select="'applications'"/>
							</name>
						</content>
					</item>
				</xsl:for-each>
			</xsl:for-each>
			<xsl:for-each select="configuration">
				<xsl:variable name="var10_cur" select="."/>
				<xsl:variable name="viewUrl" select="@url"/>
				<xsl:for-each select="tenants">
					<xsl:variable name="var7_cid" select="$var10_cur/@cid"/>
					<xsl:variable name="var8_cid">
						<xsl:if test="$var10_cur/@cid">
							<xsl:value-of select="'1'"/>
						</xsl:if>
					</xsl:variable>
					<xsl:variable name="var9_resultof_exists" select="boolean(string($var8_cid))"/>
					<item>
						<xsl:attribute name="rel">tenants</xsl:attribute>
						
						<xsl:if test="string($var9_resultof_exists) != 'false'">
							<xsl:attribute name="parent_id">
								<xsl:value-of select="string($var7_cid)"/>
							</xsl:attribute>
						</xsl:if>
						<xsl:attribute name="id">tenants</xsl:attribute>
						<content>
							<name>
								<xsl:if test="string($var9_resultof_exists) != 'false'">
									<xsl:attribute name="class">folder</xsl:attribute>
									<xsl:attribute name="href">
										<xsl:value-of select="concat(concat(concat($viewUrl, string($var10_cur/description)), '/'), 'tenants')"/>
									</xsl:attribute>
								</xsl:if>
								<xsl:value-of select="'tenants'"/>
							</name>
						</content>
					</item>
				</xsl:for-each>
			</xsl:for-each>
			<xsl:for-each select="configuration">
				<xsl:variable name="viewUrl" select="@url"/>
				<xsl:variable name="var14_cur" select="."/>
				<xsl:for-each select="hosts">
					<xsl:variable name="var11_cid" select="$var14_cur/@cid"/>
					<xsl:variable name="var12_cid">
						<xsl:if test="$var14_cur/@cid">
							<xsl:value-of select="'1'"/>
						</xsl:if>
					</xsl:variable>
					<xsl:variable name="var13_resultof_exists" select="boolean(string($var12_cid))"/>
					<item>
						<xsl:attribute name="rel">hosts</xsl:attribute>
					
						<xsl:if test="string($var13_resultof_exists) != 'false'">
							<xsl:attribute name="parent_id">
								<xsl:value-of select="string($var11_cid)"/>
							</xsl:attribute>
						</xsl:if>
						<xsl:attribute name="id">hosts</xsl:attribute>
						<content>
							<name>
								<xsl:if test="string($var13_resultof_exists) != 'false'">
									<xsl:attribute name="class">folder</xsl:attribute>
									<xsl:attribute name="href">
										<xsl:value-of select="concat(concat(concat($viewUrl, string($var14_cur/description)), '/'), 'hosts')"/>
									</xsl:attribute>
								</xsl:if>
								<xsl:value-of select="'hosts'"/>
							</name>
						</content>
					</item>
				</xsl:for-each>
			</xsl:for-each>
			<xsl:for-each select="configuration">
				<xsl:variable name="viewUrl" select="@url"/>
				<xsl:variable name="var18_cur" select="."/>
				<xsl:for-each select="devices">
					<xsl:variable name="var15_cid" select="$var18_cur/@cid"/>
					<xsl:variable name="var16_cid">
						<xsl:if test="$var18_cur/@cid">
							<xsl:value-of select="'1'"/>
						</xsl:if>
					</xsl:variable>
					<xsl:variable name="var17_resultof_exists" select="boolean(string($var16_cid))"/>
					<item>
						<xsl:attribute name="rel">devices</xsl:attribute>
						
						<xsl:if test="string($var17_resultof_exists) != 'false'">
							<xsl:attribute name="parent_id">
								<xsl:value-of select="string($var15_cid)"/>
							</xsl:attribute>
						</xsl:if>
						<xsl:attribute name="id">devices</xsl:attribute>
						<content>
							<name>
								<xsl:if test="string($var17_resultof_exists) != 'false'">
									<xsl:attribute name="class">folder</xsl:attribute>
									<xsl:attribute name="href">
										<xsl:value-of select="concat(concat(concat($viewUrl, string($var18_cur/description)), '/'), 'devices')"/>
									</xsl:attribute>
								</xsl:if>
								<xsl:value-of select="'devices'"/>
							</name>
						</content>
					</item>
				</xsl:for-each>
			</xsl:for-each>
 		    <xsl:for-each select="configuration">
				<xsl:variable name="viewUrl" select="@url"/>
				<xsl:variable name="var18_cur" select="."/>
			
					<xsl:variable name="var15_cid" select="$var18_cur/@cid"/>
					
						    <item>
							<xsl:attribute name="rel">SLA</xsl:attribute>
							<xsl:attribute name="parent_id">
								<xsl:value-of select="string(number(string($var15_cid)))"/>
							</xsl:attribute>
							<xsl:attribute name="id">SLA</xsl:attribute>
							<content>
									<name>
										
										<xsl:attribute name="class">sla</xsl:attribute>
											
										
										<xsl:attribute name="href">
											<xsl:value-of select="concat(concat('sla', '/'), concat($viewUrl, string($var18_cur/description)))"/>
										</xsl:attribute>
										<xsl:value-of select="'SLA'"/>
									</name>
							</content>
							</item>
						
						
				
			</xsl:for-each>
			
			<xsl:for-each select="configuration">
				<xsl:variable name="viewUrl" select="@url"/>
				<xsl:variable name="var23_cur" select="."/>
				<xsl:for-each select="applications/application">
					<xsl:variable name="var19_cid" select="$var23_cur/@cid"/>
					<item>
						<xsl:attribute name="parent_id">applications</xsl:attribute>
						<xsl:attribute name="id">
							<xsl:value-of select="string(id)"/>
						</xsl:attribute>
						<content>
							<name>
								<xsl:variable name="var20_cid">
									<xsl:if test="$var23_cur/@cid">
										<xsl:value-of select="'1'"/>
									</xsl:if>
								</xsl:variable>
								<xsl:if test="string(boolean(string($var20_cid))) != 'false'">
									<xsl:variable name="var21_aid" select="@aid"/>
									<xsl:variable name="var22_aid">
										<xsl:if test="@aid">
											<xsl:value-of select="'1'"/>
										</xsl:if>
									</xsl:variable>
									<xsl:if test="string(boolean(string($var22_aid))) != 'false'">
									<xsl:attribute name="class">folder</xsl:attribute>
										<xsl:attribute name="href">
											<xsl:value-of select="concat(concat(concat(concat(concat(concat($viewUrl, string($var23_cur/description)), '/'), 'applications'), '/'), 'aid:'), string(number(string($var21_aid))))"/>
										</xsl:attribute>
									</xsl:if>
								</xsl:if>
								<xsl:value-of select="string(name)"/>
							</name>
						</content>
					</item>
				</xsl:for-each>
			</xsl:for-each>
			<xsl:for-each select="configuration">
				<xsl:variable name="viewUrl" select="@url"/>
				<xsl:variable name="var28_cur" select="."/>
				<xsl:for-each select="tenants/tenant">
					<xsl:variable name="var24_cid" select="$var28_cur/@cid"/>
					<item>
						<xsl:attribute name="parent_id">tenants</xsl:attribute>
						<xsl:attribute name="id">
							<xsl:value-of select="string(description)"/>
						</xsl:attribute>
						<content>
							<name>
								<xsl:variable name="var25_cid">
									<xsl:if test="$var28_cur/@cid">
										<xsl:value-of select="'1'"/>
									</xsl:if>
								</xsl:variable>
								<xsl:if test="string(boolean(string($var25_cid))) != 'false'">
									<xsl:variable name="var26_tid" select="@tid"/>
									<xsl:variable name="var27_tid">
										<xsl:if test="@tid">
											<xsl:value-of select="'1'"/>
										</xsl:if>
									</xsl:variable>
									<xsl:if test="string(boolean(string($var27_tid))) != 'false'">
										<xsl:attribute name="href">
											<xsl:value-of select="concat(concat(concat(concat(concat(concat($viewUrl, string($var28_cur/description)), '/'), 'tenants'), '/'), 'tid:'), string(number(string($var26_tid))))"/>
										</xsl:attribute>
									</xsl:if>
								</xsl:if>
								<xsl:value-of select="string(name)"/>
							</name>
						</content>
					</item>
				</xsl:for-each>
			</xsl:for-each>
			<xsl:for-each select="configuration">
				<xsl:variable name="viewUrl" select="@url"/>
				<xsl:variable name="var33_cur" select="."/>
				<xsl:for-each select="hosts/host">
					<xsl:variable name="var29_cid" select="$var33_cur/@cid"/>
					<item>
						
						<xsl:choose>
							<xsl:when test="string(type) != 'HLMhost'">
								<xsl:attribute name="rel">
									<xsl:value-of select="string(type)"/>
								</xsl:attribute>
								<xsl:attribute name="parent_id">hosts</xsl:attribute>
							</xsl:when>
							<xsl:otherwise> 
								<xsl:attribute name="rel">
									<xsl:value-of select="SLA"/>
								</xsl:attribute>
								<xsl:attribute name="parent_id">SLA</xsl:attribute>
							<!-- 	<xsl:attribute name="parent_id">HLMetrics</xsl:attribute> -->
							</xsl:otherwise>
						</xsl:choose>
						
						
						<xsl:attribute name="id">
							<xsl:value-of select="string(description)"/>
						</xsl:attribute>
						<content>
							<name>
								<xsl:variable name="var30_cid">
									<xsl:if test="$var33_cur/@cid">
										<xsl:value-of select="'1'"/>
									</xsl:if>
								</xsl:variable>
								<xsl:if test="string(boolean(string($var30_cid))) != 'false'">
									<xsl:variable name="var31_hid" select="@hid"/>
									<xsl:variable name="var32_hid">
										<xsl:if test="@hid">
											<xsl:value-of select="'1'"/>
										</xsl:if>
									</xsl:variable>
									<xsl:if test="string(boolean(string($var32_hid))) != 'false'">
										<xsl:attribute name="href">
											<xsl:value-of select="concat(concat(concat(concat(concat(concat($viewUrl, string($var33_cur/description)), '/'), 'hosts'), '/'), 'hid:'), string(number(string($var31_hid))))"/>
										</xsl:attribute>
									</xsl:if>
								</xsl:if>
								<xsl:value-of select="string(name)"/>
							</name>
						</content>
					</item>
				</xsl:for-each>
			</xsl:for-each>
			<xsl:for-each select="configuration">
				<xsl:variable name="var38_cur" select="."/>
				<xsl:variable name="viewUrl" select="@url"/>
				<xsl:for-each select="devices/device">
					<xsl:variable name="var34_cid" select="$var38_cur/@cid"/>
					<item>
						<xsl:attribute name="rel">
							<xsl:value-of select="string(device_type)"/>
						</xsl:attribute>
						<xsl:attribute name="parent_id">devices</xsl:attribute>
						<xsl:attribute name="id">
							<xsl:value-of select="string(description)"/>
						</xsl:attribute>
						<content>
							<name>
								<xsl:variable name="var35_cid">
									<xsl:if test="$var38_cur/@cid">
										<xsl:value-of select="'1'"/>
									</xsl:if>
								</xsl:variable>
								<xsl:if test="string(boolean(string($var35_cid))) != 'false'">
									<xsl:variable name="var36_did" select="@did"/>
									<xsl:variable name="var37_did">
										<xsl:if test="@did">
											<xsl:value-of select="'1'"/>
										</xsl:if>
									</xsl:variable>
									<xsl:if test="string(boolean(string($var37_did))) != 'false'">
										<xsl:attribute name="href">
											<xsl:value-of select="concat(concat(concat(concat(concat(concat($viewUrl, string($var38_cur/description)), '/'), 'devices'), '/'), 'did:'), string(number(string($var36_did))))"/>
										</xsl:attribute>
									</xsl:if>
								</xsl:if>
								<xsl:value-of select="string(name)"/>
							</name>
						</content>
					</item>
				</xsl:for-each>
			</xsl:for-each>
			<xsl:for-each select="configuration">
				<xsl:variable name="viewUrl" select="@url"/>
				<xsl:variable name="varconf_cur" select="."/>
				<xsl:for-each select="applications/application">
					<xsl:variable name="varapp_cur" select="."/>
					<xsl:for-each select="services/service">
						<xsl:variable name="varserv_cur" select="."/>
						<item>
							<xsl:attribute name="rel">service</xsl:attribute>
							<xsl:attribute name="parent_id">
								<xsl:value-of select="string($varapp_cur/id)"/>
							</xsl:attribute>
							<xsl:attribute name="id">
								<xsl:value-of select="string(description)"/>
							</xsl:attribute>
							<xsl:attribute name="type"><xsl:value-of select="string(type)"/></xsl:attribute>
							<content>
								<name>
									<xsl:attribute name="href">
												<xsl:value-of select="concat(concat(concat(concat(concat(concat($viewUrl, string($varconf_cur/description)), '/'), 'services'), '/'), 'sid:'), string(number(string($varserv_cur/@sid))))"/>
									</xsl:attribute>
									<xsl:value-of select="string(name)"/>
								</name>
							</content>
						</item>
					</xsl:for-each>
				</xsl:for-each>
			</xsl:for-each>
			<xsl:for-each select="configuration">
				<xsl:variable name="var38_cur" select="."/>
				<xsl:variable name="viewUrl" select="@url"/>
				<xsl:for-each select="relations/host">
					<xsl:variable name="var34_cid" select="@cid"/>
					<item>
						<xsl:attribute name="rel">
							<xsl:value-of select="string(type)"/>
						</xsl:attribute>
						<xsl:attribute name="parent_id">
							<xsl:value-of select="string(parent_host)"/>
						</xsl:attribute>
						<xsl:attribute name="id">
							<xsl:value-of select="string(description)"/>
						</xsl:attribute>
						<content>
							<name>
								<xsl:variable name="var35_cid">
									<xsl:if test="@cid">
										<xsl:value-of select="'1'"/>
									</xsl:if>
								</xsl:variable>
								<xsl:if test="string(boolean(string($var35_cid))) != 'false'">
									<xsl:variable name="var36_hid" select="@hid"/>
									<xsl:variable name="var37_hid">
										<xsl:if test="@hid">
											<xsl:value-of select="'1'"/>
										</xsl:if>
									</xsl:variable>
									<xsl:if test="string(boolean(string($var37_hid))) != 'false'">
										<xsl:attribute name="href">
											<xsl:value-of select="concat(concat(concat(concat(concat(concat($viewUrl, string($var34_cid)), '/'), 'hosts'), '/'), 'hid:'), string(number(string($var36_hid))))"/>
										</xsl:attribute>
									</xsl:if>
								</xsl:if>
								<xsl:value-of select="string(name)"/>
							</name>
						</content>
					</item>
				</xsl:for-each>
			</xsl:for-each>
		</root>
	</xsl:template>
</xsl:stylesheet>
