<?php

/*	
	Debuging 
*/
function ss_isItUs() {
	global $cfg;

	if($_SERVER['REMOTE_ADDR'] == $cfg['devIP'])
		return true;

//	if(!strncmp( $_SERVER['REMOTE_ADDR'], "192.168", 7 ) )
//		return true;

	return false;
}
function ss_Pre($text) {
	print("<PRE>$text</PRE>");
}

function ss_DumpVar($var,$description = NULL, $forUs = false ) {

    if(ss_isItUs()) {
        $backtrace = debug_backtrace();
        $file = '';
        for ($i=0;$i<count($backtrace);$i++) {
              // skip the first one, since it's always this func
              if (strpos($backtrace[$i]["file"],'Debugging.php') === false )  {
                  $file = ($backtrace[$i]["file"].' (Line # : '.$backtrace[$i]["line"].' )<br>');
                  $parts = explode('IM_', $file);
                  $file = 'IM_' . ($parts[1]);
                  break;
              }
        }

			print('<PRE>');
			print("$file: ");
			print('</PRE>');

	}

    if ($forUs) {
		if(ss_isItUs()) {
			print('<PRE>');
			print("$description: ");
			print_r($var);
			print('</PRE>');
		}
	}/* else {
		print('<PRE>');
		print("$description: ");
		print_r($var);
		print('</PRE>');
	} */
}

function ss_DumpVarHide($var,$description = NULL,$show = false, $forUs = false) {
	ss_toggleDisplayJS();
	if ($show) {
		$linkDisplay = 'none';	$infoDisplay = '';
	} else {
		$linkDisplay = '';	$infoDisplay = 'none';
	}
	$unique = md5(uniqid(time()));
	if ($forUs) {
		if(ss_isItUs()) {
			print("<PRE>$description: ");
			print("<a style=\"display:$linkDisplay\" id=\"debugInfoLink$unique\" href=\"Javascript:toggleDisplay('debugInfo$unique');void(0);\">Show/Hide</a>".
				"<div id=\"debugInfo$unique\" style=\"display:$infoDisplay;\">");
			print_r($var);	
			print("</div>");
			print('</PRE>');
		}
	} else {
		print("<PRE>$description: ");
		print("<a style=\"display:$linkDisplay\" id=\"debugInfoLink$unique\" href=\"Javascript:toggleDisplay('debugInfo$unique');void(0);\">Show/Hide</a>".
			"<div id=\"debugInfo$unique\" style=\"display:$infoDisplay;\">");
		print_r($var);	
		print("</div>");
		print('</PRE>');	
	}
	
}

function ss_ShowHideStart($show = false,$forUs = false) {
	ss_toggleDisplayJS();
	if ($show) {
		$linkDisplay = 'none';	$infoDisplay = '';
	} else {
		$linkDisplay = '';	$infoDisplay = 'none';
	}
	$unique = md5(uniqid(time()));
	if (($forUs and ss_isItUs()) or !$forUs) {
		print("<a style=\"display:$linkDisplay\" id=\"debugInfoLink$unique\" href=\"Javascript:toggleDisplay('debugInfo$unique');void(0);\">Show/Hide</a>".
			"<div id=\"debugInfo$unique\" style=\"display:$infoDisplay;\">");
	}
}

function ss_ShowHideEnd($forUs = false) {
	if (($forUs and ss_isItUs()) or !$forUs) {
		print("</div>");
	}
}

function ss_DumpVarDie($var,$description = NULL, $forUs = false)
{
	$output = "<br/>Dying at";
	$trace = debug_backtrace();
	foreach ( $trace as $level => $position )
	{
		$output .= "<br/>level ".$level;
		if( array_key_exists( 'file', $position ) )
			$output .= " ".$position['file'].":".$position['line'];
	}

	if( ss_isItUs( ) )
	{
		echo $output;
		echo $description;
		echo "<br/><br/>".str_replace( '  ', '&nbsp;', nl2br( print_r( $var, true ) ) );
		echo "We die here";
	}
	else
	{
		ss_log_message( "ss_DumpVarDie() on live site" );
		ss_log_message( $output );
		ss_log_message( $description );
		ss_log_message_r(  'Log:'.__FILE__.':'.__LINE__, $var );
	}

	die;
}

function ss_disableDebugOutput() {
	global $cfg;
	$cfg['debugMode'] = false;
}

function ss_Die($message) {
	global $cfg;
	print("<PRE>$message</PRE>");
	if ($cfg['debugMode']) {
		require('System/Core/DebugInfo.php');		// Code
	}	
	die();	
}


// From PHP Manual User contributed notes: samstealth at yahoo dot com
// Modified to put single quotes around array keys
function ss_VarExport($a) {
	$result = "";
	switch (gettype($a)) {
		case "array":
			reset($a);
			$result = "array(";
//			ss_log_message_r(  'Log:'.__FILE__.':'.__LINE__, $a );
			foreach( $a as $k => $v )
				if( $k && $v )
					$result .= "'$k' => ".ss_VarExport($v).",\n";
			$result .= ")\n";
			break;
			
		case "string": 	
			$result = "'$a'";	
			break;
			
		case "boolean":	
			$result = ($a) ? "true" : "false";
			break;
			
		default:		
			$result = $a;
			break;
	}
	return $result;
}

?>
