<br><hr>
<div style="margin: 15px;">
<?php

/*
<a id="debugInfoLink" href="Javascript:document.getElementById('debugInfo').style.display='';document.getElementById('debugInfoLink').style.display='none';void(0);">Show Debug Info</a>
<div id="debugInfo" style="display:none;">
<a href="Javascript:document.getElementById('debugInfo').style.display='none';document.getElementById('debugInfoLink').style.display='';void(0);">Hide Debug Info</a>
*/

	global $timer;
	global $sql;
	global $cfg;

	// Show session variables 
	ss_DumpVarHide($_SESSION,'$_SESSION');

	// Show execution times
	ss_toggleDisplayJS();

	print("<pre>Timer: ");
	ss_ShowHideStart();
	$timer->report();
	$timer->reportOverall();
	ss_ShowHideEnd();
	print("</pre>");
		
	// Show SQL Queries
	print("<pre>DB Queries: ");
	ss_ShowHideStart();
	$sql->dumpQueries();
	ss_ShowHideEnd();
	print("</pre>");

	// Show SQL Queries
	print("<pre>Shared DB Queries: ");
	ss_ShowHideStart();
	$GLOBALS['commonDB']->dumpQueries();
	ss_ShowHideEnd();
	print("</pre>");
		
	// Show request variables 
	ss_DumpVarHide($_REQUEST,'$_REQUEST');

	// Show cookie variables 
	ss_DumpVarHide($_COOKIE,'$_COOKIE');

	// Show config
	ss_DumpVarHide($cfg,'$cfg');
	
	// Show server variables stuff
	ss_DumpVarHide($_SERVER,'$_SERVER');

?>
</div>