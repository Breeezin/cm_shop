<?php

//	if( ( getField( "select us_do_not_track from users where us_id = ".ss_getUserID() ) == 'true' ) || ss_isAdmin( ) )
	if( true )
	{
		// Set the user to be a guest again
		$_SESSION['User'] = array(
			'us_id'	=>	-1,
			'user_groups'	=>	array(0),
			'us_first_name'	=>	'Guest',
			'us_last_name'	=>	'User',
			'us_email'		=>	null,
		);
		$_REQUEST['DontTryCookieLogin'] = 1;
		
		setcookie('keepMeLoggedInCookie','logout',10,str_replace('index.php','',$_SERVER['SCRIPT_NAME']),str_replace('www','',$_SERVER['HTTP_HOST']));
	}

	$customFilePath = expandPath('Custom/Core/logout.php');
	if (file_exists($customFilePath))
	{
		require($customFilePath);	
	}


   	$this->param('Layout','');
    if (strlen($this->ATTRIBUTES['Layout']) > 0) {
        $this->display->layout = $this->ATTRIBUTES['Layout'];
    }

?>
