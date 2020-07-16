<?php 
	$this->param('ci_id');
	$this->param('BackURL');

	// make sure that the issue isn't tagged to another admin.

	$ci_id = (int)$this->ATTRIBUTES['ci_id'];
	$existing = getRow( "select * from client_issue where ci_id = $ci_id" );
	if( $existing['ci_assigned_to'] && ($existing['ci_assigned_to'] != ss_getUserID() ) )
	{
		// nope nope nope
		locationRelative($this->ATTRIBUTES['BackURL']);
	}

	if( ss_adminCapability( ADMIN_CUSTOMER_ISSUE ) )
	{
		query("update client_issue set ci_closed = now() where ci_id = ".(int)$this->ATTRIBUTES['ci_id'] );
		query( "insert into client_issue_edit (cie_ci_id, cie_us_id, cie_closed) values (".(int)$this->ATTRIBUTES['ci_id'].", ".ss_getUserID().", true)" );
	}

	locationRelative($this->ATTRIBUTES['BackURL']);
?>
