<?php

    // This shows everyone, everyone elses events
    $showAll = true;
    $editAll = true;
    	
	$defaultService = 'View';
    // must be in a specified group or admin to view, and logged in
	ss_paramKey($asset->cereal, 'AST_SCHEDULER_GROUPS', array());
	$groups = array_keys($_SESSION['User']['user_groups']);
	$isMember = 0;
    foreach ($asset->cereal['AST_SCHEDULER_GROUPS'] as $group) {
        if (in_array($group, $groups)) {
    		$isMember = 1;
    		break;
    	}
    }

    // check if admin user or main admin group
	ss_paramKey($asset->cereal, 'AST_SCHEDULER_ADMIN_GROUPS', array());
	$userid = $_SESSION['User']['us_id'];
	$isAdmin = 0;
    if (in_array($userid,$asset->cereal['AST_SCHEDULER_ADMIN_GROUPS']) || in_array(1,$groups)){
        $isAdmin = 1;        
    }
		
	$this->param('Service',$defaultService);
	
	$assetPath = ss_withoutPreceedingSlash($asset->getPath());
	$assetID = $asset->getID();
	if (array_key_exists('Layout', $this->ATTRIBUTES) and strlen($this->ATTRIBUTES['Layout'])) {
		$asset->display->layout = $this->ATTRIBUTES['Layout'];
	}
	
	$rootFolder = str_replace('index.php','',$_SERVER['SCRIPT_FILENAME']);
	$customFolder = $rootFolder.'Custom/Classes/SchedulerAsset';
	
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