<?php

	$this->threadsPerPage = 10;
	$this->messagesPerPage = 10;
	$this->asset = $asset;

	// $this->param('Service','ThreadList');
	$this->param('Service',$this->defaultService);
	$assetPath = ss_withoutPreceedingSlash($asset->getPath());
	$assetID = $asset->getID();
	
	// Check if the user is an administrator
	ss_paramKey($asset->cereal,$this->fieldPrefix.'ADMIN_USERGROUPS',array());
	$user = ss_getUser();
	$this->isAdmin = false;	
	foreach($asset->cereal[$this->fieldPrefix.'ADMIN_USERGROUPS'] as $ug) {
		if (array_key_exists($ug,$user['user_groups'])) {
			$this->isAdmin = true;	
			break;
		}
	}
	
	// Call the services
	foreach(array('query','model','view') as $prefix) {
		$name = $prefix.'_'.strtolower($this->ATTRIBUTES['Service']).'.php';
		
		if (file_exists(dirname(__FILE__).'/Services/'.$name)) {
			
			include("Services/".$name);
		}
	}
	

?>
