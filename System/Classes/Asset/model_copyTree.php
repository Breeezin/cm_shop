<?php 
	$this->param("Source");
	$this->param("Destination");
	if (!is_dir ($this->ATTRIBUTES['Source'])) {
		mkdir($this->ATTRIBUTES['Source']);			
	}

	if (!is_dir ($this->ATTRIBUTES['Destination'])) {
		mkdir($this->ATTRIBUTES['Destination']);			
	}
	
	ss_copyDirectoryWithSub($this->ATTRIBUTES['Source'],$this->ATTRIBUTES['Destination']);	
?>