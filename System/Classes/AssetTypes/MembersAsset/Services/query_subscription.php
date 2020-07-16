<?php 
	$this->param('SubscriptionType','');	
	$this->param('us_id');
	
	
	
	$errorMessages =  array();
	// get country for the client
	$clientCountry = ss_getCountryID();
	if (!strlen($clientCountry)) {
		$clientCountry = 554;
	}
	//$clientCountry = 1;
	//ss_DumpVarDie($clientCountry);	
?>