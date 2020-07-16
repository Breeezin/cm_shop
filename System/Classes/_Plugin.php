<?php
// This is the basis for all Classes. Every class must include an 
// exposeServices method to inform the system of what Actions
// it makes available (if any).
class Plugin
{
	var $classDirectory = 'System/Classes';
	var $cache = 'No';
	var $ATTRIBUTES = array(); //= array();
	var $atts = array(); //= array();
	var $pluginDirectory = NULL;
	var $entryErrors = array();
	
	function __construct() {
		/*	Constructor for the class. Do anything here that needs to 
			be done. Classes extending Plugin should call this function
			before doing anything in their own constructor.
		*/
	}

	function param($name,$default = "\t\n1234567890\tPARAMETERWASNOTSUPPLIEDNEVERUSETHISASINPUTPLEASE\n", $nullValue = "\t\n1234567890\tPARAMETERWASNOTSUPPLIEDNEVERUSETHISASINPUTPLEASE\n") {
		//ss_DumpVar($this,$name);
		if ($default === "\t\n1234567890\tPARAMETERWASNOTSUPPLIEDNEVERUSETHISASINPUTPLEASE\n") {
			if (!array_key_exists($name,$this->ATTRIBUTES)) {
				die("$name is a required attribute.");
			} 
		} else {
			if (!array_key_exists($name,$this->ATTRIBUTES)) {
				$this->ATTRIBUTES[$name] = $default;
			}
		}
		if ($nullValue != "\t\n1234567890\tPARAMETERWASNOTSUPPLIEDNEVERUSETHISASINPUTPLEASE\n" and !strlen($this->ATTRIBUTES[$name])) {
			$this->ATTRIBUTES[$name] = $nullValue;
		}
	}
	
	function paramLen($name,$default = "\t\n1234567890\tPARAMETERWASNOTSUPPLIEDNEVERUSETHISASINPUTPLEASE\n", $nullValue = "\t\n1234567890\tPARAMETERWASNOTSUPPLIEDNEVERUSETHISASINPUTPLEASE\n") {
		//ss_DumpVar($this,$name);
		if ($default === "\t\n1234567890\tPARAMETERWASNOTSUPPLIEDNEVERUSETHISASINPUTPLEASE\n") {
			if (!array_key_exists($name,$this->ATTRIBUTES)) {
				die("$name is a required attribute.");
			} 
		} else {
			if (!array_key_exists($name,$this->ATTRIBUTES)) {
				$this->ATTRIBUTES[$name] = $default;
			}
		}
		if ($nullValue != "\t\n1234567890\tPARAMETERWASNOTSUPPLIEDNEVERUSETHISASINPUTPLEASE\n" and !strlen($this->ATTRIBUTES[$name])) {
			$this->ATTRIBUTES[$name] = $nullValue;
		}
		
		if (!strlen($this->ATTRIBUTES[$name])) {
			die("$name is a required attribute.");
		}
		 
	}
	
	function inputFilter() {
		/*	This should modify the '$this->ATTRIBUTES' variable
			E.g.

			$this->ATTRIBUTES['fm_id'] = 5;	
		*/
	}
	
	function exposeServices() {
		/*	This should return an array of Actions that the plugin
			provides. E.g.
			
			return array(
				"Asset.Display"			=>		array('method' => 'display'),
				"Asset.Embed"			=>		array('method' => 'embed'),
				"Asset.Create"			=>		array('method' => 'create'),
			);
		*/
		return array();
	}
	
	function outputFilter($content) {
		return $content;
	}
	
	function doAction() {
		$safeFA = str_replace(".", "_", $this->ATTRIBUTES['act']);
		if (array_key_exists("Do_$safeFA", $this->ATTRIBUTES)) {
			return true;
		}
		return false;
	}
	
	function processTemplate($template,&$data,$custom = array(), $fileType = 'html') {
		
		$templateFile = "{$this->classDirectory}/Templates/{$template}.$fileType";
		$customTemplate = "Custom/ContentStore/Templates/{$GLOBALS['cfg']['currentSiteFolder']}".get_class($this).'/'.$template;
		//ss_DumpVar($customTemplate,'custom',true);
		if (file_exists(expandPath($customTemplate.'.'.$fileType))) $templateFile = $customTemplate.'.'.$fileType;									
		$useCustomImagesFolder = null;
		if (file_exists(expandPath($customTemplate.'.php'))) $useCustomImagesFolder = get_class($this);							

		
		return processTemplate($templateFile,$data,$custom,$useCustomImagesFolder);
	}
	
	function useTemplate($template, &$data,$custom = array()) {
		print($this->processTemplate($template,$data,$custom));
	}
	
}
?>
