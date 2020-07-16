<?php
	// admin adds issue response
	$this->param('cir_id');
	$this->param('BackURL');

	$cir_id = (int)$this->ATTRIBUTES['cir_id'];

	if( $cir_id && ss_adminCapability( ADMIN_DELETE_ISSUE ) )
		query("update client_issue_response set cir_deleted = true where cir_id = $cir_id " );

	locationRelative($this->ATTRIBUTES['BackURL']);
?>
