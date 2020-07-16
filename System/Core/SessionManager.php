<?php

class SQLSessionManager 
{
   var $life_time;

   function __construct() {

	  $this->life_time = get_cfg_var("session.gc_maxlifetime");

	  // Register this object as the session handler
	  session_set_save_handler( 
		array( &$this, "open" ), 
		array( &$this, "close" ),
		array( &$this, "read" ),
		array( &$this, "write"),
		array( &$this, "destroy"),
		array( &$this, "gc" )
	  );
   }

   function open( $save_path, $session_name )
   {
	  global $sess_save_path;

	  $sess_save_path = $save_path;

	  return true;
   }

   function close()
   {
	  return true;
   }

   function read( $id )
   {
//	  ss_log_message( "Getting session ID:$id" );
	  // Set empty result
	  $data = '';

	  // Fetch session data from the selected database

	  $time = time();

	  $newid = escape($id);
	  $sql = "SELECT session_data FROM sessions WHERE session_id = '$newid' AND expires > $time";
	  //ss_log_message( $sql );
	  $rs = query($sql);
	  if( $rs->numRows() > 0 )
	  {
		$row = $rs->fetchRow();
		$data = $row['session_data'];
	  }

	  return $data;
   }

   function write( $id, $data )
   {
	  //ss_log_message( "Writing session ID:$id" );

	  $time = time() + $this->life_time;

	  $newid = escape($id);
	  $newdata = escape($data);

	  $sql = "REPLACE sessions (session_id,session_data,expires) VALUES('$newid', '$newdata', $time)";
	  //ss_log_message( $sql );
	  query( $sql );

	  return TRUE;
   }

   function destroy( $id )
   {
	  $newid = esape($id);
	  $sql = "DELETE FROM sessions WHERE session_id = '$newid'";
	  //ss_log_message( $sql );
	  query( $sql );

	  return TRUE;
   }

   function gc() {

	  // Garbage Collection

	  // Delete all records who have passed the expiration time
	  $sql = 'DELETE FROM sessions WHERE expires < UNIX_TIMESTAMP()';

	  //ss_log_message( $sql );
	  query( $sql );

	  return true;

   }

}

//	ss_log_message_r($_COOKIE,'');
	// new SQLSessionManager();

	// See if we've already set a token for this user
	// if not.. we need to create one
//	session_set_cookie_params (0, ss_WithTrailingSlash(dirname($_SERVER['REQUEST_URI'])));

	if( in_array( $_SERVER['REQUEST_URI'], $cfg['AllowCache'] ) )
	{
		session_cache_limiter( 'public' );
		session_cache_expire( 180 );
	}

	session_set_cookie_params (0, ss_WithTrailingSlash(dirname($_SERVER['SCRIPT_NAME'])), $cfg['SessionDomain']);
	session_start();

	if (!array_key_exists('User',$_SESSION))
	{
		// Initialise the user as a guest
		$_SESSION['User'] = array(
			'us_id'	=>	NULL,		// this will be overwritten when the DB connection comes up;
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
	
	
	//ss_DumpVar('$_SESSION',$_SESSION);
	//ss_DumpVar('$_COOKIE',$_COOKIE);
	
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
