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
	require_once('System/Core/Functions/Internationalisation.php');		// Code 
	
	require_once('System/Core/Timer.php');
	$timer = new Timer();
	$timer = $timer->start('Boot');


	// Include some important stuff
	$timer = $timer->start('LoadingSomeStuff');
	require_once('System/Core/ServerCheck.php');		// Code 
	require_once('System/Core/ErrorHandler.php');	// Code 
	require_once('System/Core/BackStack.php');		// Class
	require_once('System/Core/QueryManagerCLI.php');	// Class
	require_once('System/Core/LayoutHandler.php');	// Class
	require_once('System/Core/RequestCLI.php');			// Class
	require_once('System/Core/ListFunctions.php');	// Class
	require_once('System/Core/SessionManagerCLI.php');	// Code - must be after BackStack is defined	
	require_once('System/Classes/_Plugin.php');		// Class

	require_once('Custom/GlobalSettings.php');		// Code 	

	if (ss_isItUs() and array_key_exists('Debug',$_REQUEST)) $cfg['debugMode'] = true;
	$timer = $timer->finish('LoadingSomeStuff');


	// Create a new query manager called sql
	$sql = new QueryManager($dbCfg['dbType'],$dbCfg['dbUsername'],$dbCfg['dbPassword'],$dbCfg['dbServer'],$dbCfg['dbName']);	

	$commonDB = new QueryManager($dbCfg['dbType'],$dbCfg['commondbUsername'],$dbCfg['commondbPassword'],$dbCfg['commondbServer'],$dbCfg['commondbName']);

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
	foreach ($configuration as $key => $value) {
		if ($key != 'cfg_id') {		
			$key = substr($key,2);		
			if ($key != 'Options') {
				$GLOBALS['cfg'][$key] = $value;	
			} else {
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

	// Do the specified act
	timerStart('Overhead - Initial');
	
	$GLOBALS['RequestDepth'] = 0;

	$result = new Request($_REQUEST['act'],$_REQUEST);
	
	timerFinish('Overhead - Initial');
	$timer = $timer->finish('Boot');
	$timer->finish('Timer');

	// Disconnect from the database if we need to
	$sql->disconnect();

	// Display the output
	print $result->display;
	
	// Print some debugging information
	if ($cfg['debugMode']) {
		require('System/Core/DebugInfo.php');		// Code
	}	

?>
