<?php

	if (!array_key_exists('AssetEmbedCounts',$GLOBALS)) $GLOBALS['AssetEmbedCounts'] = array();	
	if (!array_key_exists($this->getID(),$GLOBALS['AssetEmbedCounts'])) $GLOBALS['AssetEmbedCounts'][$this->getID()] = 0;	
	$GLOBALS['AssetEmbedCounts'][$this->getID()]++;	

	if ($GLOBALS['AssetEmbedCounts'][$this->getID()] > 20 && ( php_sapi_name() != 'cli' )) {
		print ("embed limit reached ");
	} else {
		// Include and instantiate the class type
		$className = $this->fields['as_type'].'Asset';
		requireOnceClass($className);
		$temp = new $className;
		$temp->ATTRIBUTES = &$this->ATTRIBUTES;
	
		// Call the display handler for the specific type
		$temp->embed($this);
	}
?>
