<?php

	$this->param('Service','Welcome');
	$assetPath = ss_withoutPreceedingSlash($asset->getPath());
	$assetID = $asset->getID();
	foreach(array('query','model','view') as $prefix) {
		$name = $prefix.'_'.strtolower($this->ATTRIBUTES['Service']).'.php';
		if (file_exists(dirname(__FILE__).'/Services/'.$name)) include("Services/".$name);
	}
	

?>
