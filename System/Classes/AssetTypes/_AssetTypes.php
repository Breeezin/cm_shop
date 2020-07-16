<?php

class AssetTypes extends Plugin {
	
	var $fieldSet = NULL;
	
	function __construct() {
		parent::__construct();
//		$this->Plugin();
	}
	
	// This takes an asset reference as input and should display the asset
	function display(&$asset) {
		print 'Display is not defined for this asset type.';
	}
	
	// This takes an asset reference as input and should embed the asset
	function embed(&$asset) {
		print 'Embed is not defined for this asset type.';
	}
	
	
	function embedImage(&$asset) {
		locationRelative($asset->classDirectory."/Images/embed.png");
	}

	// This takes an asset reference as input and should display an edit
	// form speecific to the current asset type
	function edit(&$asset) {
		print 'Edit is not defined for this asset type.';
	}	
	
	function properties(&$asset) {
		print '&nbsp;';	
	}
	
	// Delete any records from associated tables
	function delete(&$asset) {
		// Most wont do anything in here	
		die("Please define delete method.");
		
	}
	
	function copy(&$asset) {
		// Most wont do anything in here	
	}
	
	function newAsset(&$asset) {
		// Most wont do anything in here	
		return null;
	}
	
	
	function getInfo(&$asset) {
		return null;
	}
	
	function processSave(&$asset) {
		return null;
	}
	
	function getClassName() {
		die('Please define for getClassName  for :'.get_class($this));	
	}
	
	function processTemplate($template,&$data,$custom = array(), $filetype = '') {
		
		$className = $this->getClassName();
		//$templateFile = dirname(__FILE__)."/".$className."/Templates/{$template}.html";
		//print(expandPath(ss_getClassDirectory($className))."/Templates/{$template}.html");
		//print("<BR>".$templateFile);
		
		
		$templateFile = expandPath(ss_getClassDirectory($className))."/Templates/{$template}.html";
		//ss_DumpVarDie(expandPath(ss_getClassDirectory($className))."/".$className."/Templates/{$template}.html");
		$useCustomImagesFolder = null;
		

		$customTemplate = "Custom/ContentStore/Templates/{$GLOBALS['cfg']['currentSiteFolder']}".$className.'/'.$template;
		if (file_exists(expandPath($customTemplate.'.html'))) $templateFile = $customTemplate.'.html';									
		if (file_exists(expandPath($customTemplate.'.php'))) $useCustomImagesFolder = $className;							
	
		return processTemplate($templateFile,$data,$custom,$useCustomImagesFolder);
	}
}


?>
