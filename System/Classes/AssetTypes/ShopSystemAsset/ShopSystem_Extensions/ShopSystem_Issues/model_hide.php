<?php 
	$this->param('ci_id');
	$this->param('BackURL');
	
	$Q_Update = query("update client_issue set ci_invisible = 1 where ci_id = ".(int)$this->ATTRIBUTES['ci_id'] );
	
	locationRelative($this->ATTRIBUTES['BackURL']);
?>
