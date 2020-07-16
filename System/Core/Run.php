<?php
	// Include some useful functions
	require_once('System/Core/Functions/ContentManager.php');
	require_once('System/Core/Functions/Debugging.php');
	require_once('System/Core/Functions/FileSystem.php');
	require_once('System/Core/Functions/DateTime.php');
	require_once('System/Core/Functions/WebsitePage.php');
	require_once('System/Core/Functions/Template.php');
  	require_once('System/Core/Functions/StringHandling.php');
  	require_once('System/Core/Functions/OutputBuffering.php');
  	require_once('System/Core/Functions/Shop.php');
  	require_once('System/Core/Functions/USPS.php');
	require_once('System/Core/Functions/Internationalisation.php');		// Code

	require_once('System/Core/Timer.php');
	$timer = new Timer();
	$timer = $timer->start('Boot');

	require_once('Custom/GlobalSettings.php');		// Code

	// blunt those XSS attacks, not foolproof
//	if( count( $_POST ) > 0 )
	if( false )
	{
		/* need to intercept this

		NONE:2018-10-24 03:11:02:PAYMENT bank:99 transaction:-1 Array
		(
			[HTTPS] => on
			[SSL_TLS_SNI] => test.acmerockets.com
			[HTTP_HOST] => test.acmerockets.com
			[CONTENT_LENGTH] => 276
			[CONTENT_TYPE] => application/x-www-form-urlencoded
			[PATH] => /usr/local/sbin:/usr/local/bin:/usr/sbin:/usr/bin:/sbin:/bin
			[SERVER_SIGNATURE] => 
			[SERVER_SOFTWARE] => Apache/2.4.10 (Debian) OpenSSL/1.0.1t
			[SERVER_NAME] => test.acmerockets.com
			[SERVER_ADDR] => 67.231.16.122
			[SERVER_PORT] => 443
			[REMOTE_ADDR] => 103.255.252.156		// testing, live is 103.255.252.41
		)

		NONE:2018-10-24 03:11:02:PAYMENT bank:99 transaction:-1 ACQRA _GET
		NONE:2018-10-24 03:11:02:PAYMENT bank:99 transaction:-1 Array
		(
		)

		NONE:2018-10-24 03:11:02:PAYMENT bank:99 transaction:-1 ACQRA _POST
		NONE:2018-10-24 03:11:02:PAYMENT bank:99 transaction:-1 Array
		(
			[order_ref] => 1701497
			[transaction_id] => 201810241510312760163
			[status_code] => 10000
			[status_message] => Payment Success
			[amount] => 228.26
			[transaction_time] => 2018-10-24 15:10:12
			[currency] => EUR
			[settlement_ref] => 5403650604286920803009
			[hash] => 444357454b3d2d20e619bf1d995bfc9752e980d4d7b19b3897c28695caa4bf22
		)
			*/

		if( !strncmp( $_SERVER['REMOTE_ADDR'], '103.255.252', 11 ) )		// this is from the acqra server
		{
			ss_log_message( "intercepting ACQRA POST" );

			if( array_key_exists( 'hash', $_POST ) 							// it's a status update, forward it onto the proper place.
				&& array_key_exists( 'order_ref', $_POST )
				&& array_key_exists( 'status_code', $_POST )
				&& array_key_exists( 'amount', $_POST ) )
			{	// v1.0
				$ch = curl_init( "https://{$_SERVER['SERVER_NAME']}/acqra/confirm.php" ); 	
				curl_setopt( $ch, CURLOPT_POST, 1); 	
				curl_setopt( $ch, CURLOPT_POSTFIELDS, $_POST); 	
				curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, 1); 	
				curl_setopt( $ch, CURLOPT_HEADER, 0); 	
				curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, 0); 	
				curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, 0); 	
				curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1);  	
				if( ( $result = curl_exec($ch)) === false )
					ss_log_message( "CURL error :".curl_error($ch) );
				else
					ss_log_message( "Response to POST to self is $response" );
			}
			else
			{
				if( array_key_exists( 'transaction_no', $_POST ) 							// it's a status update, forward it onto the proper place.
					&& array_key_exists( 'order_id', $_POST )
					&& array_key_exists( 'status', $_POST )
					&& array_key_exists( 'amount', $_POST ) )
				{	// v1.1
					$ch = curl_init( "https://{$_SERVER['SERVER_NAME']}/acqra_unionpay/confirm.php" ); 	
					curl_setopt( $ch, CURLOPT_POST, 1); 	
					curl_setopt( $ch, CURLOPT_POSTFIELDS, $_POST); 	
					curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, 1); 	
					curl_setopt( $ch, CURLOPT_HEADER, 0); 	
					curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, 0); 	
					curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, 0); 	
					curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1);  	
					if( ( $result = curl_exec($ch)) === false )
						ss_log_message( "CURL error :".curl_error($ch) );
					else
						ss_log_message( "Response to POST to self is $response" );
				}
				else
				{
					ss_log_message( "ACQRA missing post data" );
					ss_log_message_r(  'Log:'.__FILE__.':'.__LINE__, $_POST );
				}
			}

			die;
		}

		if( array_key_exists('HTTP_REFERER', $_SERVER) 
		&& (strlen($_SERVER['HTTP_REFERER']) > 0 ) )
		{
			$referrer_host = parse_url($_SERVER['HTTP_REFERER']);
			if( $referrer_host )
			{
//				echo "referrer >".$referrer_host['host']."<br>";
				$found = FALSE;
				foreach( $cfg['multiSites'] as $url => $site )
				{
					$local_host = parse_url( $url );
//					echo "local >".$local_host['host']."<br>";
					if( !strcasecmp($referrer_host['host'], $local_host['host']) )
						$found = TRUE;
				}
				if( !$found )
				{
					echo "XSS attack";
					ss_log_message( "XSS attack" );
					ss_log_message_r(  'Log:'.__FILE__.':'.__LINE__, $_SERVER );
					ss_log_message_r(  'Log:'.__FILE__.':'.__LINE__, $_POST );
					die;
				}
				if( strcasecmp( $referrer_host['host'], $_SERVER['SERVER_NAME'] ) )
				{
					// cross site post
					if( array_key_exists( 'Session', $_POST ) )
					{
						// sanitise
						$sid = substr( $_POST['Session'], 0, strspn( $_POST['Session'], 'abcdefghijklmnopqrstuvwxyz0123456789' ) );
						ss_log_message( "session $sid swapping from {$referrer_host['host']} to {$_SERVER['SERVER_NAME']}" );
						session_id( $sid );
					}
				}
			}
		}
		else
		{
			// horrible hack
			if( array_key_exists( 'message', $_POST ) && array_key_exists( 'req_reference_number', $_POST )  )
			{
				if( ss_getUserID() > 0 )
					ss_audit( 'other', 'users', ss_getUserID(), "User returned from gateway with message ".$_POST['message']." about order ".((int)$_POST['req_reference_number']) );
				location( "/Shop_System/Service/OrderStatus/tr_id/".((int)$_POST['req_reference_number'])."/Message/".htmlentities($_POST['message']) );
			}

			// another horrible hack
			if( array_key_exists( 'Session', $_POST ) && array_key_exists( 'imageField',  $_POST ) && !strncmp($_POST['imageField'], 'Swap to ', 8 ) )
			{
				$sid = substr( $_POST['Session'], 0, strspn( $_POST['Session'], 'abcdefghijklmnopqrstuvwxyz0123456789' ) );
				ss_log_message( "session $sid swapping from {$referrer_host['host']} to {$_SERVER['SERVER_NAME']}" );
				if( ss_getUserID() > 0 )
					ss_audit( 'other', 'users', ss_getUserID(), "User swapping from {$referrer_host['host']} to {$_SERVER['SERVER_NAME']}" );
				session_id( $sid );
			}
			else
			{
				if( ss_getUserID() > 0 )
					ss_audit( 'other', 'users', ss_getUserID(), "User posting data from an empty page. nasty" );

				echo "<html>";
				echo "You are sending information to this page, but your browser has sent through no referer.<br/>";
				echo "<br/>";
				echo "This could be caused by<br/>";
				echo "1) You are using a proxy server.  Please disable this, it isn't a good idea to shop though a proxy.<br/>";
				echo "2) Your browser is configured oddly.  We appreciate your privacy, but this is a shop!<br/>";
				echo "3) Cross site scripting.  Someone has configured another website (not this one) to send data here.<br/>";
				echo "<br/>";
				echo "Please correct the above issues and go to our home page and try again.<br/>";
				echo "<br/>";
				echo "</html>";
				ss_log_message( "Referrer mangled" );
				ss_log_message_r(  'Log:'.__FILE__.':'.__LINE__, $_SERVER );
				ss_log_message_r(  'Log:'.__FILE__.':'.__LINE__, $_POST );
				sleep(10);
				die;
			}
		}
	}


	// Include some important stuff
	$timer = $timer->start('ServerCheck');
	require_once('System/Core/ServerCheck.php');		// Code
	$timer = $timer->finish('ServerCheck');
	$timer = $timer->start('ErrorHandler');
	require_once('System/Core/ErrorHandler.php');	// Code
	$timer = $timer->finish('ErrorHandler');
	$timer = $timer->start('BackStack');
	require_once('System/Core/BackStack.php');		// Class
	$timer = $timer->finish('BackStack');
	$timer = $timer->start('QueryManager');
	require_once('System/Core/QueryManager.php');	// Class
	$timer = $timer->finish('QueryManager');
	$timer = $timer->start('LayoutHandler');
	require_once('System/Core/LayoutHandler.php');	// Class
	$timer = $timer->finish('LayoutHandler');
	$timer = $timer->start('Request');
	require_once('System/Core/Request.php');			// Class
	$timer = $timer->finish('Request');
	$timer = $timer->start('ListFunctions');
	require_once('System/Core/ListFunctions.php');	// Class
	$timer = $timer->finish('ListFunctions');
	$sql = new QueryManager($dbCfg['dbType'],$dbCfg['dbUsername'],$dbCfg['dbPassword'],$dbCfg['dbServer'],$dbCfg['dbName']);
	$timer = $timer->start('SessionManager');
	require_once('System/Core/SessionManager.php');	// Code - must be after BackStack is defined
	$timer = $timer->finish('SessionManager');
	$timer = $timer->start('_Plugin');
	require_once('System/Classes/_Plugin.php');		// Class
	$timer = $timer->finish('_Plugin');

	// log this
	if( !array_key_exists( 'REDIRECT_STATUS', $_SERVER ) || ( $_SERVER['REDIRECT_STATUS'] == 200 ) )
	{
		if( strstr( $_SERVER['REQUEST_URI'], 'ImageManager.get' ) )		// far too much of this.
			;
		else
		{
			if( array_key_exists( 'HTTP_REFERER', $_SERVER ) && strlen( $_SERVER['HTTP_REFERER'] ) )
				ss_log_message( 'Referer '.$_SERVER['HTTP_REFERER'] );

			if( array_key_exists( 'HTTPS', $_SERVER ) && strlen( $_SERVER['HTTPS'] ) )
				ss_log_message( 'SSL '.$_SERVER['SSL_PROTOCOL'].' '.$_SERVER['REQUEST_METHOD'].' '.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'].' FROM '.$_SERVER['REMOTE_ADDR'] );
			else
				ss_log_message( 'PLAIN NA '.$_SERVER['REQUEST_METHOD'].' '.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'].' FROM '.$_SERVER['REMOTE_ADDR'] );
		}
	}

	if (ss_isItUs() and array_key_exists('Debug',$_REQUEST)) $cfg['debugMode'] = true;

	// Create a new query manager called shared sql
	$GLOBALS['commonDB'] = new QueryManager($dbCfg['dbType'],$dbCfg['commondbUsername'],$dbCfg['commondbPassword'],$dbCfg['commondbServer'],$dbCfg['commondbName']);

	require_once('Custom/Core/Functions.php');

	/*	Load the site configuration from the DB into the global cfg.
		I used to load this into the session to cache it, but sometimes caused issues
		with ppl not getting the configuration updates immediately
		and this part only takes something like 0.002s to run anyway.	*/
	ss_paramKey($GLOBALS['cfg'], 'templateFolder', 'Custom/ContentStore');
	ss_paramKey($GLOBALS['cfg'], 'currentSiteFolder', '');
	//$GLOBALS['cfg']['currentSiteFolder'] = str_replace('/','',$GLOBALS['cfg']['currentSiteFolder']);
	ss_paramKey($GLOBALS['cfg'], 'multiSites', array());

	$whereSQL = '';
	if (strlen($GLOBALS['cfg']['currentSiteFolder'])) {
		$whereSQL = " WHERE cfg_folder_name LIKE  '".str_replace('/', '', $GLOBALS['cfg']['currentSiteFolder'])."'";
	}
	$configuration = getRow('SELECT * FROM configuration '.$whereSQL);
	if( is_array( $configuration) )
		if( sizeof( $configuration ) == 0 )
		{       
			header("HTTP/1.0 404 Not Found");
			echo "How did you get here?";
			ss_log_message( "select from configuration failed $whereSQL" );
			ss_DumpVarDie( $GLOBALS['cfg'] );
			die;
		}
		else
			;
	else
	{
		header("HTTP/1.0 404 Not Found");
		echo "Table configuration missing or unconfigured";
		ss_log_message( "select from configuration failed $whereSQL" );
		ss_DumpVarDie( $GLOBALS['cfg'] );
		die;
	}

	foreach ($configuration as $key => $value) {
		if ($key != 'cfg_id')
		{
			$key = substr($key,4);
			if ($key != 'options') 
				$GLOBALS['cfg'][$key] = $value;
			else
			{
				$txtOptions = $value;
				$txtOptions = str_replace(chr(13).chr(10),chr(10),$txtOptions);
				$newLine = chr(10);
				$arrayOptions = ListToArray($txtOptions,$newLine);
				$options = array();
				foreach ($arrayOptions as $aOption) {
					if(strpos($aOption,'=') === false) {
						$options[strtolower($aOption)] = true;
					} else {
						$options[strtolower(ListFirst($aOption,'='))] = ListLast($aOption,'=');
					}
				}
				$GLOBALS['cfg'][$key] = $options;
			}
		}
	}

	// If enabled, then stop visitors from some countries from acccessing the site
	if (ss_optionExists('Restrict countries')) require('CountryRestriction.php');

/*	//users' session so we only need to query it once
	if (!array_key_exists('cfg',$_SESSION)) ss_RefreshSessionConfiguration();
	// Merge the cfg from GlobalSettings.php with the cfg from the session (which is from the DB)
	ss_LoadConfigurationFromSession();*/

	// We need this to be in the request scope at the top level for fuse actions
	if (!array_key_exists('BackStructure',$_REQUEST)) {
		if (array_key_exists('REQUEST_URI',$_SERVER)) {
			$_REQUEST['REQUEST_URI'] = $_SERVER['REQUEST_URI'];
		} else {
			$_REQUEST['REQUEST_URI'] = '';
		}
		// $_REQUEST['DUMMY_USER_GROUPS'] = $_SESSION['User']['user_groups'];
	}

/*
	$us_id = $_SESSION['User']['us_id'];
	if( $us_id )
	{
		if( $user = getRow( "select * from users where us_id = $us_id" ) )
		{
			if( $user['us_account_credit'] > 0 )
			{
//				ss_log_message( "User ID is $us_id, credit {$user['us_account_credit']}{$user['UsAccountCreditCurrency']}" );
				$creditCurrency = $user['UsAccountCreditCurrency'];

				if( array_key_exists( $user['UsAccountCreditCurrency'], $GLOBALS['cfg']['ChargeCurrency'] )
				  && ( !array_key_exists( 'Discount', $GLOBALS['cfg']['ChargeCurrency'][$user['UsAccountCreditCurrency']] )
					|| ( $GLOBALS['cfg']['ChargeCurrency'][$user['UsAccountCreditCurrency']]['Discount'] == 0 ) ) )		// no discount in this currency
				{
					// remove discounts in other currencies, or currencies with discount?
					foreach( $GLOBALS['cfg']['ChargeCurrency'] as $index=>$curr )
						$GLOBALS['cfg']['ChargeCurrency'][$index]['Discount'] = 0;
				}

			}
		}
	}
*/

	// Add an RFA if one is supplied
	if (array_key_exists('RFA', $_REQUEST)) {
		$_SESSION['RFAStack'][] = $_REQUEST['RFA'];
		unset($_REQUEST['RFA']);
	}

	// Add a BackStack to the session if none exists
	if (!array_key_exists('BackStack',$_SESSION)) $_SESSION['BackStack'] = new BackStack();

	// If a BackStructure has been requested then restore it
	if (array_key_exists('BackStructure',$_REQUEST)) $_SESSION['BackStack']->restoreAttributeSet($_REQUEST['BackStructure']);

	// Now store the current attribute set for using later if needed
	$_SESSION['BackStack']->storeAttributeSet();

	// Set the default fuseaction
	if (!array_key_exists('act',$_REQUEST)) $_REQUEST['act'] = $cfg['defaultAction'];

    //added for the Hosted payments options
    //sends "result" in the request
    if (isset($_REQUEST['result']) and isset($_SESSION['Transaction']) and (!isset($_REQUEST['count']))){
    # this is a redirection BACK from the Payments Page.
       $backURL = $_SESSION['Transaction'];
       $backURL = $backURL . '&result=' .$_REQUEST["result"] . '&count=1';
       //ss_DumpVarDie($backURL);
       location($backURL);
    }

	// Do the specified act
	timerStart('Overhead - Initial');

	$GLOBALS['RequestDepth'] = 0;
	$result = new Request($_REQUEST['act'],$_REQUEST);

	timerFinish('Overhead - Initial');
	$timer = $timer->finish('Boot');
	$timer->finish('Timer');

	// Disconnect from the database if we need to
	session_write_close();
	$sql->disconnect();

	// Display the output
	print $result->display;

	// Print some debugging information
	if ($cfg['debugMode']) {
		require('System/Core/DebugInfo.php');		// Code
	}

?>
