<?php 

function locationRelative($relative, $js=false) {
	// $cfg['currentserver'] has a trailing forward slash
	// so no slash is required on $relative
	global $cfg;
	
	ss_escapeBufferingClean();	
	$ar = debug_backtrace();
//die;
	//ss_DumpVarDie($cfg['currentServer'], "Location: {$cfg['currentServer']}{$relative}");
	$newRelative = ss_withoutPreceedingSlash($relative);
	//ss_DumpVarDie("Location: {$cfg['currentServer']}{$newRelative}", '', true);
	ss_log_message( "redirection to $newRelative from {$ar[0]['file']}:{$ar[0]['line']}" );
	if($js)
		print("<script language='javascript'>document.location = \"{$cfg['currentServer']}{$newRelative}\";</script>");
	else {
		header("Location: {$cfg['currentServer']}{$newRelative}");
	}
	exit;
}

function location($url,$js=false) {
	ss_escapeBufferingClean();
	$ar = debug_backtrace();
	ss_log_message( "redirection to $url from {$ar[0]['file']}:{$ar[0]['line']}" );
	//ss_DumpVarDie($cfg['currentServer'], "Location: {$cfg['currentServer']}{$relative}");
	if($js)
		print("<script language='javascript'>document.location = \"".ss_JSStringFormat($url)."\";</script>");
	else
		header("Location: $url");
	exit;
}

/**
 * @return void
 * @desc Return to previous page in stack
 */
function rfaReturn() {
	die('Dont use rfaReturn');
	//location(array_pop($_SESSION['RFAStack']));
}

function startAdminPercentageBar($caption) {
	include('inc_startPercentageBar.php');
}

function stopAdminPercentageBar($location = null) {
	include('inc_stopPercentageBar.php');
}

function updateAdminPercentageBar($percentage) {
	print("<SCRIPT LANGUAGE=\"Javascript\">sw(".$percentage.");</SCRIPT>");
	flush();
}

function ss_customStyleSheet($name) {
	print("<link rel=\"stylesheet\" href=\"sty_{$name}.css\" type=\"text/css\">");
}

function ss_toggleDisplayJS() {
	if (!array_key_exists('toggleDisplayJSIncluded',$GLOBALS)) {
		print("<script language=\"Javascript\">");
		print("function toggleDisplay(what) {");	
		print("  what=document.getElementById(what);");
		print("  if (what.style.display == 'none') { what.style.display=''; } else { what.style.display='none'; }");	
		print("}");	
		print("</script>");	
		$GLBOALS['toggleDisplayJSIncluded'] = 1;
	}
}
?>
