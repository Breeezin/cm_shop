<?php 

	$this->param('or_id');	
	$this->param('BackURL');
	$this->param('BreadCrumbs');
	if (strlen($this->ATTRIBUTES['BreadCrumbs']) > 5) {
		$this->display->title = $this->ATTRIBUTES['BreadCrumbs'].": Edit Tracking Reference";
	} else {
		$this->display->title = "Edit Tracking Reference";
	}
	
	ss_audit( 'update', 'Orders', $this->ATTRIBUTES['or_id'], 'setting tracking' );

	$theOrder = getRow("
				SELECT *
				FROM shopsystem_orders 				
				WHERE or_id = {$this->ATTRIBUTES['or_id']}
	");
	
	$this->param('or_tracking_code', $theOrder['or_tracking_code']);	
	$this->param('or_track_link', $theOrder['or_track_link']);
	
	if (array_key_exists('DoAction', $this->ATTRIBUTES)) {
		$setSQL = "";
		if (!strlen($theOrder['or_tracked_and_traced'])) {
			$setSQL = ", or_tracked_and_traced = Now()";
		}
		
		$Q_UpdateOrder = query("
				UPDATE shopsystem_orders 
				SET  
				or_tracking_code = '".escape($this->ATTRIBUTES['or_tracking_code'])."'		
				$setSQL
				WHERE or_id = {$this->ATTRIBUTES['or_id']}
		");
		$shortBackURL = str_replace($GLOBALS['cfg']['currentServer'],'',$this->ATTRIBUTES['BackURL']);		
		
		locationRelative($shortBackURL);
	}
		
?>
