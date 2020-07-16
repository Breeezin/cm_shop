<?php 
	$this->param('ci_id');
	$this->param('BackURL');
	
	query("update client_issue set ci_closed = null where ci_id = ".(int)$this->ATTRIBUTES['ci_id'] );
	query( "insert into client_issue_edit (cie_ci_id, cie_us_id, cie_closed) values (".(int)$this->ATTRIBUTES['ci_id'].", ".ss_getUserID().", false)" );

	locationRelative($this->ATTRIBUTES['BackURL']);
?>
