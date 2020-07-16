<?php

	// set the error reporting level for this script
	error_reporting (E_ALL);
	
	// error handler function
	function imErrorHandler ($errno, $errstr, $errfile, $errline, $vars) {
		global $cfg;
		
		require_once('System/Libraries/htmlMimeMail/htmlMimeMail.php');
		
		$mail = new htmlMimeMail();
		$mail->setFrom("errors@{$_SERVER['HTTP_HOST']}");
		
		// Figure out the website address
		$address = $cfg['currentServer'];
		$address = str_replace('http://','',$address);
		$address = str_replace('https://','',$address);
		$mail->setSubject("Bug Report From {$address}");
		
		// Construct an error message
		$errorMessage = "<h1>Error occurred at ".date('Y-m-d H:i')." on {$cfg['currentServer']}</h1><P><STRONG>".ss_HTMLEditFormat($errstr)."</STRONG> ($errno) on line number <STRONG>$errline</STRONG> in file <STRONG>$errfile</STRONG></P><HR>";
		//print $errorMessage;
		
		
		// Grab a dump of all the variables
		ob_start();
		//ss_DumpVar(debug_backtrace(),'BackTrace');
		ss_DumpVar($vars,'Variables');
		$errorMessage .= '<PRE>'.ss_HTMLEditFormat(str_replace("</PRE>","",str_replace("<PRE>","",ob_get_contents()))).'</PRE>';
		ob_end_clean();
		
		// Add some styles to make it look purty
		$styles = "<STYLE TYPE=\"text/css\">".
			"p {font-family: Arial, Helvetica, sans-serif; font-size: 13px; }".
			"h1 {font-family: Arial, Helvetica, sans-serif; font-size: 16px; color: aaaaaa; }".
			"</STYLE>";
		
		// Assign the html to the email
		$mail->setHtml($styles.$errorMessage);
		
		// Send the email (if we should)
		if ($cfg['bugReportEmailAddresses'] != null) {
			$mail->send($cfg['bugReportEmailAddresses']);
		}
	
	}
	
	// set to the user defined error handler
	$old_error_handler = set_error_handler("imErrorHandler");


?>