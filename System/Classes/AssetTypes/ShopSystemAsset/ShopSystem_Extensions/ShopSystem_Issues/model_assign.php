<?php
	// admin adds issue response
	$this->param('ci_id');
	$this->param('BackURL');
	$this->param('assigned_to');

	$ci_id = (int)$this->ATTRIBUTES['ci_id'];

	$assigned_to = (int)$this->ATTRIBUTES['assigned_to'];

	if( $assigned_to > 0 )
		$ass = "ci_assigned_to = $assigned_to";
	else
		$ass = "ci_assigned_to = NULL";

	if( $ci_id )
		query( "update client_issue set $ass where ci_id = $ci_id" );

	print( "update client_issue set $ass where ci_id = $ci_id" );

	locationRelative($this->ATTRIBUTES['BackURL']);
?>
