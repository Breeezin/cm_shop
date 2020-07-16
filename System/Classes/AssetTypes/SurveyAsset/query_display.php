<?php


	$defaultService = ss_optionExists('Survey Default Service');
	if (!$defaultService) 
		$defaultService = 'Survey';
		
	$this->param('Service',$defaultService);
	
	$assetPath = ss_withoutPreceedingSlash($asset->getPath());
	$assetID = $asset->getID();
	if (array_key_exists('Layout', $this->ATTRIBUTES) and strlen($this->ATTRIBUTES['Layout'])) {
		$asset->display->layout = $this->ATTRIBUTES['Layout'];
	}
	
	$rootFolder = str_replace('index.php','',$_SERVER['SCRIPT_FILENAME']);
	$customFolder = $rootFolder.'Custom/Classes/SurveyAsset';
	
	$customAllowedServices = array();
	if (file_exists($customFolder.'/inc_services.php')) {
		include($customFolder.'/inc_services.php');		
	}
	$theService = strtolower($this->ATTRIBUTES['Service']);
	if (count($customAllowedServices)) {
		if (!array_key_exists($theService, $customAllowedServices)) {
			$this->ATTRIBUTES['Service'] = $defaultService;
		}
	}
	foreach(array('query','model','view') as $prefix) {
		$name = $prefix.'_'.strtolower($this->ATTRIBUTES['Service']).'.php';
		if (file_exists($customFolder.'/Services/'.$name)) {
			include($customFolder."/Services/".$name);
		} else if (file_exists(dirname(__FILE__).'/Services/'.$name)) {
			include("Services/".$name);
		}
	}


?>
