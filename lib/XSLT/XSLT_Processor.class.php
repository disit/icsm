<?php
DEFINE("SAXON_JAR","/saxon/saxon9he.jar");

class XSLT_Processor
{
	protected $schemaDir;
	
	function XSLT_Processor(){
		$this->schemaDir=realpath("./").DIRECTORY_SEPARATOR."schema/";
	}
	
	function setSchemaDir($path)
	{
		$this->schemaDir=$path;
	}
	
	function mapFile($xmlFile,$xsltFile)
	{
		$filename=$this->schemaDir."output_".round(microtime(true) * 1000).".xml";
		$xsltFilePath=$xsltFile;
		$saxon = str_replace("/", DIRECTORY_SEPARATOR, SAXON_JAR);
		$cmd ="java -jar \"".__DIR__.$saxon."\" -xsl:\"$xsltFilePath\" -s:\"$xmlFile\" -o:\"$filename\"";
		sm_Logger::error($cmd);
		$output=array();
		$xmlString="";
		exec($cmd,$output);
		if(!file_exists($filename))
			sm_Logger::error($output);
		else 
		{
			$xmlString = file_get_contents($filename);
			unlink($filename);
		}
		
		return $xmlString;
	}
	
	function mapString($xmlStr,$xsltFile)
	{
		$filename=$this->schemaDir."tmp_".round(microtime(true) * 1000).".xml";
		$f=fopen($filename,"wt");
		fwrite($f,$xmlStr);
		fclose($f);
		$res = $this->mapFile($filename,$xsltFile);
		unlink($filename);
		return $res; 
	}
}
