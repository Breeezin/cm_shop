<?php

	ss_DumpVar('$_SESSION',$_SESSION);
	ss_log_message_r($_COOKIE,'');

	if( php_sapi_name() == 'cli' )
		return;
	// See if we've already set a token for this user
	// if not.. we need to create one
	session_set_cookie_params (0, ss_WithTrailingSlash(dirname($_SERVER['REQUEST_URI'])));
	session_start();

	
	if (!array_key_exists('User',$_SESSION)) {
		// Initialise the user as a guest
		$_SESSION['User'] = array(
			'us_id'	=>	-1,
			'user_groups'	=>	array(0),
			'us_first_name'	=>	'Guest',
			'us_last_name'	=>	'User',
			'us_email'		=>	null,
		);
	}
	

	$clearCookies = FALSE;
	if (!array_key_exists('tokenCheck',$_COOKIE)) {
		// Seed the random number generator
		srand(
			((double)microtime()*1000000)
			^ ip2long($_SERVER['REMOTE_ADDR'])
			^ (int)$_SERVER['REMOTE_PORT']
		);
	
		// Create a token to use for the session id
		$tokenCheck = md5(uniqid(rand(),1)); // better, difficult to guess
		
		// Set the cookie
		setcookie('tokenCheck',$tokenCheck);
		$_SESSION['tokenCheck'] = $tokenCheck;
		setcookie('tokenCheck',$tokenCheck,time()+3600*24,dirname($_SERVER['SCRIPT_NAME']).(dirname($_SERVER['SCRIPT_NAME'])=='/'?'':'/'));	// expires in one hour
	} else {
		$tokenCheck = $_COOKIE['tokenCheck'];
	}
	echo "2\n";
	if (!array_key_exists('statsUser',$_COOKIE)) {
		// Seed the random number generator
		srand(
			((double)microtime()*1000000)
			^ ip2long($_SERVER['REMOTE_ADDR'])
			^ (int)$_SERVER['REMOTE_PORT']
		);
	
		
		
		// Create a token to use for the session id
		//$tokenCheck = md5(uniqid(rand(),1)); // better, difficult to guess
		
		// Set the cookie				
		setcookie('statsUser',$tokenCheck,time()+3600*24*365*5,str_replace('index.php','',$_SERVER['SCRIPT_NAME']),str_replace('www','',$_SERVER['HTTP_HOST']));
	}

	//ss_log_message_r($_COOKIE,'');
	
	
	ss_DumpVar('$_SESSION',$_SESSION);
	ss_DumpVar('$_COOKIE',$_COOKIE);
	
	// Check if the sesion contains a diferent token to the one in the cookies
	if (array_key_exists('tokenCheck',$_SESSION) && ($tokenCheck != $_SESSION['tokenCheck'])) {
		// If it does then we need to get a new session
		$clearCookies = TRUE;				

	}

	// The token didn't match	
	if (0 and $clearCookies) {
		//print('wanna clear cookies');
		session_destroy();
	    unset($_COOKIE[session_name()]);
		print ("<HTML><HEAD><META</HEAD><BODY ONLOAD=\"document.forms.CookieCutter.submit()\"><FORM NAME=\"CookieCutter\" ACTION=\"{$_CGI['SCRIPT_NAME']}\" METHOD=\"POST\">Resetting cookies.. </BODY></HTML>");
		exit;
	} else {
		// We continually update this cookie so that it stays in sync
		// with the PHPSESSID cookie. Otherwise this cookie will be lost
		// and the user will get a new session because the token won't match
		setcookie('tokenCheck',$tokenCheck,time()+3600*24);	// expires in one day
	}		

?>
