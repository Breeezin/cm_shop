<?php
	$this->display->layout = 'None';
	$this->param('siv_id','');
	$this->param('ForAdmin',false);
	
	$startCategory = 'IS NULL';
	if (strlen($this->ATTRIBUTES['ca_id'])) {
		$startCategory = '= '.$this->ATTRIBUTES['ca_id'];	
		$Category = getRow("
			SELECT * FROM shopsystem_service_invoice
		");				
		
		$result = array(
			$Category['ca_name']	=>	$this->ATTRIBUTES['ca_id'],
		);
	} else {
		$startCategory = 'IS NULL';
		$result = array();
	}
	
	return $result;
?>
