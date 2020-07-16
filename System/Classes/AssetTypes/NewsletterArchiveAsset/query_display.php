<?php

	$this->param('Service','ArchiveList');

	$rootFolder = str_replace('index.php','',$_SERVER['SCRIPT_FILENAME']);
	$customFolder = $rootFolder.'Custom/Classes/ShopSystemAsset';
	
	foreach(array('query','model','view') as $prefix) {
		$name = $prefix.'_'.strtolower($this->ATTRIBUTES['Service']).'.php';
		if (file_exists($customFolder.'/Services/'.$name)) {
			include($customFolder."/Services/".$name);
		} else if (file_exists(dirname(__FILE__).'/Services/'.$name)) {
			include("Services/".$name);
		}
	}


?>