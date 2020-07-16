<?php
	$this->param('Icon', '');
	$this->param('Panel', '');
	$this->param('Name', '');	
	
	$this->display->layout = 'None';
	
	$opener = array();
	
	$opener['Icon'] = $this->ATTRIBUTES['Icon'];
	$opener['Panel'] = $this->ATTRIBUTES['Panel'];
	$opener['Name'] = $this->ATTRIBUTES['Name'];
	$opener['RelativeHere'] = $this->classDirectory."/";

	print ($this->processTemplate('OpenerCloser',$opener));	
?>