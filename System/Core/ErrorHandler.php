<?php
	register_shutdown_function( "fatal_handler" );

	function fatal_handler() {

		global $timer;
		global $sql;

		$errfile = "unknown file";
		$errstr  = "shutdown";
		$errno   = E_CORE_ERROR;
		$errline = 0;

		$error = error_get_last();

		if( $error !== NULL) {

			$errno   = $error["type"];
			$errfile = $error["file"];
			$errline = $error["line"];
			$errstr  = $error["message"];

			ss_log_message( "PHP Fatal Error ($errno) at $errfile:$errline $errstr" );

			imErrorHandler( $errno, $errstr, $errfile, $errline, NULL );

			$backTrace = '';
			$backTraceArray = debug_backtrace();
			for ($i=1; $i<count($backTraceArray); $i++) {
				if (array_key_exists('file',$backTraceArray[$i]) and array_key_exists('line',$backTraceArray[$i]) and array_key_exists('function',$backTraceArray[$i])) {
					$backTrace .= $backTraceArray[$i]['file'].' '.$backTraceArray[$i]['line'].' '.$backTraceArray[$i]['function']."\n";
				}
			}

			ss_log_message( "BackTrace:$backTrace" );

			ss_log_message( "Client headers" );
			ss_log_message_r(  'Log:'.__FILE__.':'.__LINE__, apache_request_headers() );
			ss_log_message( "SESSION" );
			ss_log_message_r(  'Log:'.__FILE__.':'.__LINE__, $_SESSION );
			ss_log_message( "SERVER" );
			ss_log_message_r(  'Log:'.__FILE__.':'.__LINE__, $_SERVER );
			ss_log_message( "POST" );
			ss_log_message_r(  'Log:'.__FILE__.':'.__LINE__, $_POST );
			ss_log_message( "GET" );
			ss_log_message_r(  'Log:'.__FILE__.':'.__LINE__, $_GET );

			ob_start(); 
			$timer->report();
			$varcontent = ob_get_contents(); 
			ob_end_clean();
			ss_log_message( "TimerReport:$varcontent" );

			ob_start(); 
			$timer->reportOverall();
			$varcontent = ob_get_contents(); 
			ob_end_clean();
			ss_log_message( "TimerReportOverall:$varcontent" );

			ob_start(); 
			$sql->dumpQueries();
			$varcontent = ob_get_contents(); 
			ob_end_clean();
			ss_log_message( "Queries:$varcontent" );
		}
	}

	// error handler function
	function imErrorHandler ($errno, $errstr, $errfile, $errline, $vars) {
		if( $errno != 2048 )		// TODO, fix this mess
			ss_log_message( "ERROR #:$errno str:$errstr where:$errfile:$errline" );
//		ss_log_message_r(  'Log:'.__FILE__.':'.__LINE__, $GLOBALS );
		return;

		if( 0 )
		{
			ob_end_clean();
			var_dump($errfile.":".$errline);
			var_dump($errstr);
			die;
		}

		if( IsSet( $IgnoreErrors ) && $IgnoreErrors )
			return;

		//check the user agency and ignore if it is google image search
		$userAgency = strtolower($_SERVER['HTTP_USER_AGENT']);
		if (substr_count($userAgency, 'google') and substr_count($userAgency, 'image')) {
			die();
		} else {
		
			global $cfg;
					
			ss_escapeBufferingClean();
			
			require_once('System/Libraries/htmlMimeMail/htmlMimeMail.php');
			
			$mail = new htmlMimeMail();
			$mail->setFrom("errors@{$_SERVER['HTTP_HOST']}");
			
			// Figure out the website address
			$address = $cfg['currentServer'];
			$address = str_replace('http://','',$address);
			$address = str_replace('https://','',$address);
			$mail->setSubject("Bug Report From {$address}");
			
			// Construct an error message
			$dateTime = date('Y-m-d H:i');
					
			$errorMessage = "<h3>Error occurred at ".$dateTime." on {$cfg['currentServer']} : {$_SERVER['SERVER_ADDR']}</h3><P><STRONG>".ss_HTMLEditFormat($errstr)."</STRONG> ($errno) on line number <STRONG>$errline</STRONG> in file <STRONG>$errfile</STRONG></P><HR>";
			$content = "<strong>".ss_HTMLEditFormat($errstr)."</STRONG> ($errno) on line number <STRONG>$errline</STRONG> in file <STRONG>$errfile</STRONG></P><HR>";
			
			$backTrace = '';
			$backTraceArray = debug_backtrace();
			for ($i=1; $i<count($backTraceArray); $i++) {
				if (array_key_exists('file',$backTraceArray[$i]) and array_key_exists('line',$backTraceArray[$i]) and array_key_exists('function',$backTraceArray[$i])) {
					$backTrace .= $backTraceArray[$i]['file'].' '.$backTraceArray[$i]['line'].' '.$backTraceArray[$i]['function']."\n";
				}
			}

            // Grab a dump of all the variables
			ob_start(); 
			ss_DumpVar($vars,'Variables');
			ss_DumpVar($GLOBALS['cfg'],'$GLOBALS[\'cfg\']');
			ss_DumpVar($_REQUEST,'$_REQUEST');
			ss_DumpVar($_SERVER,'$_SERVER');
			ss_DumpVar($_SESSION,'$_SESSION');
			$varcontent = ob_get_contents(); 
			ob_end_clean();

			print "<p><h3>An unexpected error has occurred.</h3>We are sorry for the inconvenience and will endeavor to fix the problem as soon as possible.</p><p><a href='{$cfg['currentServer']}'>Click here to return to {$cfg['currentServer']}</a></p>";
			
			if (ss_isItUs()) {
				print "<table bgcolor=\"#dddddd\"><tr><td>The following is only displayed to the developer:<br />";
				print $errorMessage;
				print ss_HTMLEditFormatWithBreaks($backTrace);
				print $varcontent;
				print "</td></tr></table>";
			}
			
			// Add some styles to make it look purty
			$styles = "<STYLE TYPE=\"text/css\">".
				"p {font-family: Arial, Helvetica, sans-serif; font-size: 13px; }".
				"h1 {font-family: Arial, Helvetica, sans-serif; font-size: 16px; color: aaaaaa; }".
				"</STYLE>";
	
			$errorMessage .= "<p>Error ID: ".$newID."</p>";
			if (array_key_exists('REQUEST_URI', $_SERVER)) {
				$errorMessage .= "<p>REQUEST URI: ".$_SERVER['REQUEST_URI']."</p>";
                $errorMessage .= "<p><a href='".$cfg['currentServer'].$_SERVER['REQUEST_URI']."'>Go to this page</a></p>";
            }
			$errorMessage .= "<p>Click <a href='http://www.acmerockets.com/index.php?act=WebSitesManager.BugReport'>here</a> to go the Bug Report list</p>";

			$errorMessage .= nl2br( print_r( $_SERVER, true ) );
            // Assign the html to the email
			$mail->setHtml($styles.$errorMessage);
			
			// Send the email (if we should)
			//if (!ss_isItUs())
			if ($cfg['bugReportEmailAddresses'] != null) {
				$mail->send($cfg['bugReportEmailAddresses']);
			}
		}
		if (!ss_isItUs() && !ss_isAdmin())
		{
			ss_log_message( "Sleeping on connection from ".$_SERVER['REMOTE_ADDR'] );
			disconnect();
			sleep(10000);
		}
		die();
		
	}
	
	// set to the user defined error handler
    $old_error_handler = set_error_handler("imErrorHandler");
	
	// set the error reporting level for this script
	error_reporting (E_ALL);

?>
