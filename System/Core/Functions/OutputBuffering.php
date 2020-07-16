<?php

$ob_count = 0;
$forceBuffer = false;

function ss_ob_start() {
	global $ob_count;
	$ob_count++;
	if (!array_key_exists('DisableOutputBuffering',$_REQUEST) or $GLOBALS['forceBuffer']) ob_start();
}

function ss_ob_end_clean() {
	global $ob_count;
	$ob_count--;
	if (!array_key_exists('DisableOutputBuffering',$_REQUEST) or $GLOBALS['forceBuffer']) return ob_end_clean();		
	return null;
}

function ss_escapeBufferingClean() {
	global $ob_count;
	if (!array_key_exists('DisableOutputBuffering',$_REQUEST) or $GLOBALS['forceBuffer']) while ($ob_count > 0) ss_ob_end_clean();
}

?>
