<?php
//----------------------------------------------------------------------------//
// nagiosPluginPHP (c) copyright 2008 CYKO Pty Ltd
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// THIS SOFTWARE IS GPL LICENSED
//----------------------------------------------------------------------------//
//  This program is free software; you can redistribute it and/or modify
//  it under the terms of the GNU General Public License (version 2) as 
//  published by the Free Software Foundation.
//
//  This program is distributed in the hope that it will be useful,
//  but WITHOUT ANY WARRANTY; without even the implied warranty of
//  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//  GNU Library General Public License for more details.
//
//  You should have received a copy of the GNU General Public License
//  along with this program; if not, write to the Free Software
//  Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA 02111-1307, USA.
//----------------------------------------------------------------------------//

//--------------------------------------------------------------------//
// NOTES
//--------------------------------------------------------------------//
// 
// This file should be installed in your nagios libexec folder
// eg. /usr/local/nagios/libexec/
//
// see check_skel for usage example
//
// see http://nagios.sourceforge.net/docs/3_0/pluginapi.html
// and http://nagiosplug.sourceforge.net/developer-guidelines.html
// for details on Nagios Plugins


//--------------------------------------------------------------------//
// utils.php
//--------------------------------------------------------------------//



//--------------------------------------------------------------------//
// DEFINE CONSTANTS
//--------------------------------------------------------------------//
define('STATE_OK', 				0);
define('STATE_WARNING', 		1);
define('STATE_CRITICAL', 		2);
define('STATE_UNKNOWN', 		3);
define('STATE_DEPENDENT', 		4);

//--------------------------------------------------------------------//
// NAGIOS PLUGIN CLASS
//--------------------------------------------------------------------//
class Nagios_Plugin 
{
	private $_arrLongText			= Array();
	private $_arrPerformanceData	= Array();
	public 	$strVersion 			= "(nagiosPluginsPHP 1.0)";
	public 	$strRevision 			= "1.0.4";
	public 	$strUsage				= "";
	public 	$strHelp				= "";
	public	$arrOptions				= Array();
	public	$strName				= "";
	
	//------------------------------------------------------------------------//
	// __construct
	//------------------------------------------------------------------------//
	/**
	 * __construct()
	 *
	 * class constructor
	 *
	 * stores variables, gets command line options and checks for help requests (-h)
	 *
	 * @param		string 		$strRevision	optional revision number of the plugin	  Default = NULL
	 * @param		string 		$strUsage		optional usage example for the plugin	  Default = NULL
	 * @param		string 		$strHelp		optional help message for the plugin	  Default = NULL
	 * @param		array 		$arrOptions		optional array of command line options
	 * 											to be parsed for the plugin. See the
	 * 											getOpt method for more details.			  Default = NULL
	 * 											
	 * @return  	void
	 */
	function __construct($strRevision=NULL, $strUsage=NULL, $strHelp=NULL, $arrOptions=NULL)
	{
		// store params
		if ($strRevision)
		{
			$this->strRevision = $strRevision;
		}
		if ($strUsage)
		{
			$this->strUsage = $strUsage;
		}
		if ($strHelp)
		{
			$this->strHelp	= $strHelp;
		}
		
		// get options
		$this->arrOptions = $this->getOpt($arrOptions);
		
		// get script name
		$this->strName = basename($_SERVER['argv'][0]);

		// check for help request
		if (isset($this->arrOptions['help']))
		{
			// output help
			$this->printHelp();
		}
		
		// turn error reporting on for very verbose requests (-vvv)
		if (isset($this->arrOptions['verbose']) &&  $this->arrOptions['verbose']> 2)
		{
			error_reporting(E_ALL);
		}
	}
	
	
	//------------------------------------------------------------------------//
	// output
	//------------------------------------------------------------------------//
	/**
	 * output()
	 *
	 * prints plugin output
	 *
	 * prints formated plugin output and terminates execution. 
	 * This should be the last method your plugin calls.
	 *
	 * @param		string 		$strMessage		text output to be returned to Nagios
	 * @param		int 		$intStatus		return code, must be one of the 
	 * 											defined STATE_* constants
	 *
	 * @return  	void (terminates execution)
	 */
	function output($strMessage, $intStatus)
	{
		echo $this->_buildOutput($strMessage);
		exit($intStatus);
	}
	
	
	//------------------------------------------------------------------------//
	// printHelp
	//------------------------------------------------------------------------//
	/**
	 * printHelp()
	 *
	 * prints a help message
	 *
	 * prints a help message and by default terminates execution.
	 * uses the $strRevision, $strUsage and $strHelp strings passed to
	 * the class constructor.
	 *
	 * @param		bool 		$bolExit		optional terminate execution	  Default = TRUE
	 *
	 * @return  	void
	 */
	function printHelp($bolExit=TRUE)
	{
		$this->printRevision(FALSE);
		echo "\n";
		$this->printUsage(FALSE);
		echo "\n";
		echo str_replace("<name>", $this->strName, $this->strHelp)."\n";

		if ($bolExit)
		{
			exit(STATE_UNKNOWN);
		}
	}
	
	
	//------------------------------------------------------------------------//
	// printRevision
	//------------------------------------------------------------------------//
	/**
	 * printRevision()
	 *
	 * prints the plugin revision
	 *
	 * prints the plugin revision and by default terminates execution.
	 * uses the $strRevision string passed to the class constructor.
	 *
	 * @param		bool 		$bolExit		optional terminate execution	  Default = TRUE
	 *
	 * @return  	void
	 */
	function printRevision($bolExit=TRUE)
	{
		echo $this->strName."  ";
		echo $this->strVersion."  ";
		echo $this->strRevision."\n";
		
		if ($bolExit)
		{
			exit(STATE_UNKNOWN);
		}
	}
	
	
	//------------------------------------------------------------------------//
	// printUsage
	//------------------------------------------------------------------------//
	/**
	 * printUsage()
	 *
	 * prints a usage message
	 *
	 * prints a usage message and by default terminates execution.
	 * uses the $strUsage string passed to the class constructor.
	 *
	 * @param		bool 		$bolExit		optional terminate execution	  Default = TRUE
	 *
	 * @return  	void
	 */
	function printUsage($bolExit=TRUE)
	{
		echo str_replace("<name>", $this->strName, $this->strUsage)."\n";
		
		if ($bolExit)
		{
			exit(STATE_UNKNOWN);
		}
	}

	
	//------------------------------------------------------------------------//
	// addLongText
	//------------------------------------------------------------------------//
	/**
	 * addLongText()
	 *
	 * adds a long text line to be output
	 *
	 * adds a long text line to be output by the output method
	 *
	 * @param		string 		$strValue		long line to be output
	 *
	 * @return  	void
	 */
	function addLongText($strValue)
	{
		$this->_arrLongText[] 			= $strValue;
	}
	
	
	//------------------------------------------------------------------------//
	// addPerformanceData
	//------------------------------------------------------------------------//
	/**
	 * addPerformanceData()
	 *
	 * adds a performance data line to be output
	 *
	 * adds a performance data line to be output by the output method
	 *
	 * @param		string 		$strValue		performance data line to be output
	 *
	 * @return  	void
	 */
	function addPerformanceData($strValue)
	{
		$this->_arrPerformanceData[] 	= $strValue;
	}
	
	
	//------------------------------------------------------------------------//
	// addVerbose
	//------------------------------------------------------------------------//
	/**
	 * addVerbose()
	 *
	 * adds a verbose line to the plugin output
	 *
	 * adds a verbose line to the plugin output if the plugin is called with
	 * a verbosity level equal to or higher than $intLevel
	 *
	 * @param		string 		$strValue		verbose line to be output
	 * @param		int 		$intLevel		optional verbosity level	  Default = 1
	 *
	 * @return  	void
	 */
	function addVerbose($strValue, $intLevel=1)
	{
		if ($this->arrOptions['verbose'] >= $intLevel)
		{
			echo $strValue."\n";
		}
	}
	
	
	//------------------------------------------------------------------------//
	// _buildOutput
	//------------------------------------------------------------------------//
	/**
	 * _buildOutput()
	 *
	 * build output
	 *
	 * build output from text output, long text lines and performance data
	 *
	 * @param		string 		$strMessage		text output to be returned to Nagios
	 *
	 * @return  	string
	 *
	 * @private
	 */
	private function _buildOutput($strMessage)
	{
		// add text output
		$strOutput = $strMessage;
		
		// add first line of performance data
		if (count($this->_arrPerformanceData))
		{
			$strOutput .= "|". array_shift($this->_arrPerformanceData);	
		}
		$strOutput .= "\n";
		
		// add long text lines
		$strOutput .= implode("\n", $this->_arrLongText);
			
		// add additional lines of performance data
		if (count($this->_arrPerformanceData))
		{
			$strOutput .= "|";
			foreach ($this->_arrPerformanceData AS $strLine)
			{
				$strOutput .= $strLine ."\n";
			}	
		}
		
		// add a trailing \n
		if (substr($strOutput, -1) != "\n")
		{
			$strOutput	.= "\n";	
		}
		
		// return output
		return $strOutput;
	}
	
	
	//------------------------------------------------------------------------//
	// getOpt
	//------------------------------------------------------------------------//
	/**
	 * getOpt()
	 *
	 * gets options from the command line argument list
	 *
	 * gets standard Nagios plugin options from the command line argument list.
	 * also accepts an array of custom options in the form;
	 * 		$arrCustomOptions[$chrKey] = $strValue
	 *  	where $chrKey is a single character option passed to the script 
	 * 			with a single hyphen (-)
	 * 		and $strValue is the key in the return array the option is to be 
	 * 			mapped to, optionally followed by a single colon (parameter 
	 * 			requires value) or two colons (optional value)
	 * 
	 * 		For Example, a script called with;
	 * 			check_mine -m myValue
	 * 		with a custom options array containing;
	 * 			$arrCustomOptions['m'] = 'myOption:'
	 * 		would result in a return array containing;
	 * 			$arrReturn['myOption'] = 'myValue'
	 * 
	 * this method can be easily modified to allow the use of long options 
	 * passed to the script with two hyphens (--) if used with php 5.3.0
	 * 
	 * see the php getopt function for more details
	 *
	 * @param		array 		$arrCustomOptions	optional array of command line options
	 * 												to be parsed for the plugin.			  Default = NULL
	 *
	 * @return  	array
	 */
	function getOpt($arrCustomOptions=NULL)
	{
		$arrOptions 	= Array();
		$arrLongOptions	= Array();
		$strOptions		= "";
		
		// reserved options
		$arrOptions["V"]	= "version";
		$arrOptions["h"]	= "help";
		//$arrOptions["?"]	= "help";
		$arrOptions["t"]	= "timeout:";
		$arrOptions["w"]	= "warning:";
		$arrOptions["c"]	= "critical:";
		$arrOptions["H"]	= "hostname:";
		$arrOptions["v"]	= "verbose::";
		
		// standard options
		$arrOptions["C"]	= "comunity:";
		$arrOptions["a"]	= "authentication:";
		$arrOptions["l"]	= "logname:";
		
		// standard options with multiple meanings
		$arrOptions["p"]	= "password:";
		//$arrOptions["p"]	= "port:";
		//$arrOptions["p"]	= "passwd:";
		$arrOptions["u"]	= "username:";
		//$arrOptions["url"]	= "url:";

		// custom options
		if (is_array($arrCustomOptions))
		{
			$arrOptions = array_merge($arrOptions, $arrCustomOptions);
		}

		// process options
		foreach($arrOptions AS $strOption=>$strLongOption)
		{
			// short options
			if (!(int)$strOption)
			{
				if (substr($strLongOption, -2) == "::")
				{
					$strRequired = "::";
				}
				elseif(substr($strLongOption, -1) == ":")
				{
					$strRequired = ":";
				}
				else
				{
					$strRequired = "";
				}
				$strOptions .= $strOption.$strRequired;
			}
			
			// long options
			if ($strLongOption)
			{
				$arrLongOptions[] = $strLongOption;
			}
		}
		
		// get options
		$arrReturnOptions = getopt($strOptions, array_unique($arrLongOptions)); //PHP 5.3 or better only
		//$arrReturnOptions = getopt(implode("",array_keys($arrOptions)),$arrOptions);
		$arrOutputOptions = Array();
		
		// build output array
		foreach ($arrReturnOptions AS $strOption=>$mixValue)
		{
			// set non-value options to TRUE
			if ($mixValue === FALSE)
			{
				$mixValue = TRUE;
			}
			
			if (strlen($strOption) == 1 && $strLongOption = trim($arrOptions[$strOption], ":"))
			{
				// convert short options to long options
				$arrOutputOptions[$strLongOption] = $mixValue;
			}
			else
			{
				// add in long options
				$arrOutputOptions[$strOption] = $mixValue;
			}
		}
		
		// calculate verbosity level
		if (isset($arrOutputOptions["verbose"]))
		{
			if (($arrOutputOptions["verbose"]) === TRUE)
			{
				$arrOutputOptions["verbose"] = 1;	
			}
			elseif(!(int)$arrOutputOptions["verbose"])
			{
				$arrOutputOptions["verbose"] = substr_count($arrOutputOptions["verbose"], "v") + 1;
			}
		}
		
		// return options
		return $arrOutputOptions;
	}
}


?>