<?php
		
	if (!array_key_exists("DisableOutputBuffering",$_REQUEST)) {
		location('index.php?'.$_SERVER['QUERY_STRING'].'&DisableOutputBuffering=1');	
	}
	
	// Load the tabbed file
	//$Q_Countries = ss_ParseTabDelimitedFile('Custom/ContentStore/ImportExport/countries.tab');
	$Q_Currencies = ss_ParseTabDelimitedFile('Custom/ContentStore/ImportExport/Currencies.tab');
	
	
?>