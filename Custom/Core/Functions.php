<?php
	// This file is to define any custom functions :)
	if (array_key_exists('User',$_SESSION) && $_SESSION['User']['us_id'] == NULL )
	{
		query( "insert into guest_users (gu_browser_ident, gu_ip_address, gu_session ) values ('"
			.safe($_SERVER['HTTP_USER_AGENT'])."','".$_SERVER['REMOTE_ADDR']."','".session_id()."')" );
		// Initialise the user as a unique guest
		$_SESSION['User']['us_id'] = -getLastAutoIncInsert();
	}

	if (array_key_exists('User',$_SESSION) && ((int)$_SESSION['User']['us_id']) > 0 )
	{
		$newCount = getField( 'select count(*) as count from client_issue join client_issue_response on cir_ci_id = ci_id join users on ci_us_id = us_id where ci_us_id = '.((int)$_SESSION['User']['us_id']).' and cir_created > us_members_viewed' );
		if( $newCount > 0 )
			$_SESSION['NewMessage'] = 1;
	}

?>
