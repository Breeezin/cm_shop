<?php

	// create new global variable called cfg
	$cfg = array();

	$cfg['siteName'] = 'PHP Content Manager Test';
	
	// blarg@127.0.0.1 IDENTIFIED BY 'asdfhu82
	// Set up database settings	

	$dbCfg['dbType'] 		= 'mysql';
	$dbCfg['dbServer']	= '127.0.0.1'; //
	$dbCfg['dbName']		= 'shop';
	$dbCfg['dbUsername']	= 'uSd7sDf8';
	$dbCfg['dbPassword']	= 'hFd8sdfU';

	$dbCfg['commondbServer']	= '127.0.0.1'; //
	$dbCfg['commondbName']		= 'common';
	$dbCfg['commondbUsername']	= '_Shared';
	$dbCfg['commondbPassword']	= 'bfgv98kjm6';

	// $cfg['DB_Charset'] = 'utf8';
	//$cfg['DB_Charset'] = 'iso-8859-1';
	$cfg['DB_Charset'] = 'latin1';
	//$cfg['DB_Charset'] = 'utf8mb4';
	//$cfg['Web_Charset'] = 'iso-8859-1';
	$cfg['Web_Charset'] = 'utf-8';
	$cfg['EmailAddress'] = "wiley@acme.com";
	$cfg['bugReportEmailAddresses'] = array('rr@acme.com');
	$cfg['website_name'] = "PHP CM";

	$cfg['FullURI'] = ($_SERVER['SERVER_PORT'] == 443 ? "https://" : "http://");
	$cfg['FullURI'] .= $_SERVER['HTTP_HOST'];
	$cfg['FullURI'] .= ($_SERVER['SERVER_PORT'] != 80?':'.$_SERVER['SERVER_PORT']:'');
	$cfg['FullURI'] .= $_SERVER['REQUEST_URI'];
	$cfg['currentServer'] = ($_SERVER['SERVER_PORT'] == 443 ? "https://" : "http://") . $_SERVER['HTTP_HOST'] . ($_SERVER['SERVER_PORT'] != 80?':'.$_SERVER['SERVER_PORT']:'') .dirname($_SERVER['SCRIPT_NAME']).(dirname($_SERVER['SCRIPT_NAME'])=='/'?"":"/");
	
	$cfg['currentSite'] = ($_SERVER['SERVER_PORT'] == 443 ? "https://" : "http://") . $_SERVER['HTTP_HOST'];
	$cfg['SecureSite'] = ($_SERVER['SERVER_PORT'] == 443 ? "https://" : "http://") . $_SERVER['HTTP_HOST'];

	$cfg['ExcludeVendors'] = "5";

	// redirect to www.*
	if( ($_SERVER['HTTP_HOST'] == "acmerockets.com" ) )
		{
		header( "Location: ".($_SERVER['SERVER_PORT'] == 443 ? "https://www." : "http://www.") . $_SERVER['HTTP_HOST'] . ($_SERVER['SERVER_PORT'] != 80?':'.$_SERVER['SERVER_PORT']:'') .$_SERVER['REQUEST_URI']);
		die;
		}
	
	// Set a default fuseaction
	$cfg['defaultAction'] = 'Asset.Display';

	//$cfg['devIP'] = '203.189.127.54';
	$cfg['devIP'] = '162.213.249.158';

	// Enable or disable debug mode
////	$cfg['debugMode'] = false;
	$cfg['debugMode'] = true;
//	$cfg['debugMode'] = $_SERVER['REMOTE_ADDR'] == '60.234.213.169'; //false;
//	$cfg['debugMode'] = $_SERVER['REMOTE_ADDR'] == '124.197.29.176';
//	$cfg['debugMode'] = $_SERVER['REMOTE_ADDR'] == $cfg['devIP'];
	
	// Enable or disable caching
	$cfg['cache'] = false;

	// Enable or disable caching of templates
	$cfg['cacheTemplates'] = true;

	$cfg['multiSites'] = array(
		'http://shop.iconcepts.local/'   =>	'acmerockets',
		'https://shop.iconcepts.local/'   =>	'acmerockets',
		'https://shop.iconcepts.local:443/'   =>	'acmerockets',
		'http://shop.iconcepts.local:8080/'   =>	'acmerockets',
	);
/* staging site
	$cfg['multiSites'] = array(
		'http://test.acmerockets.com/'   =>	'acmerockets',
		'https://test.acmerockets.com/'   =>	'acmerockets',
		'https://test.acmerockets.com:443/'   =>	'acmerockets',
		'http://test2.acmerockets.com:8080/'   =>	'acmerockets',
		'http://test2.acmerockets.com/'   =>	'acmerockets',
		'https://test2.acmerockets.com/'   =>	'acmerockets',
		'https://test2.acmerockets.com:443/'   =>	'acmerockets',
		'http://test.acmerockets.com:8080/'   =>	'acmerockets',
		'https://sandbox.acqra.com/' => '',
		'https://api.acqra.com/' => '',
	);
*/

	$cfg['multiSiteHomes'] = array(
		'acmerockets'	=>	'Acme Rockets',
		'rocketskate'	=>	'Rocket Skate',
		'rubberbands'	=>	'Rubber Bands',
	);

	$cfg['multiSiteLog'] = array(
		'acmerockets'	=>	'acme_messages',
		'rocketskate'	=>	'skate_messages',
	);

	$cfg['multiSiteDiscount'] = array(
		'acmerockets'	=>	'0',
		'rocketskate'	=>	'0',
	);

	$cfg['currentSiteFolder'] = $cfg['multiSites'][$cfg['currentServer']]."/";

	$cfg['multiSiteLanguage'] = array(
			'acmerockets'   =>      0,
			'rocketskate'   =>      1,
	);

	$cfg['currentLanguage'] = $cfg['multiSiteLanguage'][$cfg['multiSites'][$cfg['currentServer']]];

	$cfg['AllowCache'] = array( '/Acme%20Rockets/Home', );
	

	$cfg['ShippingTracking'] = 5.00;
	$cfg['ShippingVacuum'] = 1;
	$cfg['ShippingVacuumPerBox'] = 1.00;

	$foo = explode('.',$_SERVER['HTTP_HOST']);
	$fooc = count( $foo );
	if( $fooc >= 2 )
		$cfg['SessionDomain'] = $foo[$fooc-2].".".$foo[$fooc-1];

	$cfg['DaysStockHeld'] = 10;

	$cfg['ShowFakePrices'] = array('109.164.224.20', '123.255.40.107',  );
?>
