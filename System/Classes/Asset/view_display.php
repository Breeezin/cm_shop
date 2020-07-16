<?php

	// Include and instantiate the class type
	$className = $this->fields['as_type'].'Asset';
	
	
	requireOnceClass($className);
	$temp = new $className;
	$temp->ATTRIBUTES = &$this->ATTRIBUTES;
	$temp->atts = &$this->ATTRIBUTES;
	
	
	// Call the display handler for the specific type
	$temp->display($this);
	/*
	global $cfg;
	if ($cfg['currentServer'] == "http://cm.im.co.nz/") {
		ss_DumpVarDie($this,"hmm4");
		
	}*/	
?>
