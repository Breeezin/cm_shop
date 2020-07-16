<?php 
	$this->param('or_id');
	$this->param('BackURL');
	
	$Q_Update = query("
		UPDATE shopsystem_refunds
		SET rfd_pending = false
		WHERE rfd_or_id = {$this->ATTRIBUTES['or_id']}
	");
	
	locationRelative($this->ATTRIBUTES['BackURL']);
?>
