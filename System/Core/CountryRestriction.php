<?php

if( !ss_isAdmin() )
{
//	if( !array_key_exists( 'DefaultCurrency', $_SESSION ) || !array_key_exists( $_SESSION['DefaultCurrency'], $cfg['ChargeCurrency'] ) )
//		$_SESSION['DefaultCurrency'] = 'EUR';

	if( !array_key_exists( 'ForceCountry', $_SESSION )
	 || !is_array( $_SESSION['ForceCountry'] ) )
	{
		$_SESSION['ForceCountry'] = getRow( "select * from countries where cn_two_code = '".ss_getCountry(NULL, 'cn_two_code')."'");
		// set currency
		$_SESSION['DefaultCurrency'] = 'EUR';

//		if( in_array( $_SESSION['ForceCountry']['cn_currency_code'], $cfg['ChargeCurrency'] ) )
//			$_SESSION['DefaultCurrency'] = $_SESSION['ForceCountry']['cn_currency_code'];

/*
		foreach( $GLOBALS['cfg']['ChargeCurrency'] as $index=>$curr )
			if( $curr['CurrencyCode'] == $_SESSION['ForceCountry']['cn_currency_code'] )
				$_SESSION['DefaultCurrency'] = $index;
*/
	}

/*
	if( array_key_exists( 'RedirectCountry', $_SESSION ) && strlen( $_SESSION['RedirectCountry'] ) )
	{
		$there = parse_url( $_SESSION['RedirectCountry'] );
		$tmp = $_SESSION['RedirectCountry'];
	/*	$_SESSION['RedirectCountry'] = '';	*/
	/*
		if( $there['host'] != $_SERVER['HTTP_HOST'] )
		{
			if( $_SERVER['SERVER_PORT'] != 443 )
				header( 'Location: ' . $tmp );
		}
	}

	// sanity check, if we are on an unrestricted site...
	if( array_key_exists( 'ForceCountry', $_SESSION ) && strlen( $_SESSION['ForceCountry'] ) && !array_key_exists( 'ForceCountry', $GLOBALS['cfg'] ) )
	{
	//	ss_log_message( "checking on ".$_SESSION['ForceCountry'] );
		// and trying for force a country that belongs to a restricted site...
		$Cn = GetRow( "select * from countries where cn_two_code = '".safe( $_SESSION['ForceCountry'] )."'" );
		
		if( strlen( $Cn['cn_redirect_url'] ) )
		{
			// nuke the country
			$_SESSION['ForceCountry'] = ss_getCountry(NULL, 'cn_two_code');
			ss_log_message( "cleared, now ".$_SESSION['ForceCountry'] );
		}
	}
	*/
}
else
{
	ss_log_message( "Skipping check, is admin"  );
}


function forceSSLMode()
{
    if( strpos( $_SERVER['HTTP_HOST'], '.local' ) )
		return;

    if( !array_key_exists( 'HTTPS', $_SERVER ) || $_SERVER[ 'HTTPS' ] != "on" )
		header( 'Location: https://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'] );
}

	/*	This is designed to restrict the site from displaying to various countries based
		on their ip address. */
    // indenting fixed, Rex
    // added code to handle a fixed asset display based on IP Address

function jumpToForcedAsset( $countryID )
{
    $Q_CheckAllowed = getRow("
            SELECT * FROM countries
            WHERE cn_id = $countryID
            ");

    // if they are trying to log in, or are actually logged in, or using SSL don't restrict the asset.


//    ss_DumpVarDie( $_SERVER );
//    ss_DumpVarDie( $Q_CheckAllowed );
//    ss_DumpVarDie( $_SESSION );
//	return $Q_CheckAllowed;

    if( array_key_exists( 'HTTPS', $_SERVER ) && $_SERVER[ 'HTTPS' ] == "on" )
	{
		$_SESSION['IsAllowedToUse'] = true;
		return $Q_CheckAllowed;
	}

    if( !strncasecmp( $_SERVER['REQUEST_URI'], "/admin", 6 ) )
	{
		$_SESSION['IsAllowedToUse'] = true;
		return $Q_CheckAllowed;
	}

    if( strstr( $_SERVER['REQUEST_URI'], "Members" ) != false )
		return $Q_CheckAllowed;

    if( strstr( $_SERVER['REQUEST_URI'], "Login" ) != false )
		return $Q_CheckAllowed;

	if( array_key_exists('act',$_REQUEST) && ($_REQUEST['act'] == "Administration" ) )
		return $Q_CheckAllowed;

    if( $_SESSION['User']['us_id'] != -1 )
	{
		$_SESSION['IsAllowedToUse'] = true;
		return $Q_CheckAllowed;
	}

    if( IsSet( $Q_CheckAllowed['cn_display_as_id'] ) )
    {
        $GLOBALS['RequestDepth'] = 0;
        $result = new Request("Asset.Display",array(
                        'as_id'	=>	$Q_CheckAllowed['cn_display_as_id'],
                        'Service'	=>	'Engine'
                    ));
        print $result->display;
        die;
    }
	return $Q_CheckAllowed;

}

    // if we post something to force the country code
    $countryID = -1;
    foreach( $_POST as $key => $val )
	    if( $key == 'forceCountry' )
		    $countryID = (int)$val;

    foreach( $_GET as $key => $val )
	    if( $key == 'forceCountry' )
		    $countryID = (int)$val;

    if( $countryID != -1 )
        $Q_CheckAllowed = jumpToForcedAsset( $countryID );

    // if we having already initialised this element of the session variable
	if (!array_key_exists('IsAllowedToUse',$_SESSION)) 
    {
		$countryID = ss_getCountryID();

        // default is allow
		if (strlen($countryID) == 0)
			$_SESSION['IsAllowedToUse'] = true;
		else
        {
            $Q_CheckAllowed = jumpToForcedAsset( $countryID );

			if (!strlen($Q_CheckAllowed['cn_disable_access']) 
					&& !IsSet( $Q_CheckAllowed['cn_display_as_id'] ))
				$_SESSION['IsAllowedToUse'] = true;	
			else
            {
				if (strlen($Q_CheckAllowed['cn_disable_access_code']))
                {
					//ss_DumpVar($_REQUEST,$Q_CheckAllowed['cn_disable_access_code'], true);
					if (array_key_exists('AccessCode', $_REQUEST) and $_REQUEST['AccessCode'] == $Q_CheckAllowed['cn_disable_access_code'])
                    {
						$_SESSION['AccessCode'] = $_REQUEST['AccessCode'];
						if( !IsSet( $Q_CheckAllowed['cn_display_as_id'] ) )
							$_SESSION['IsAllowedToUse'] = true;

						setcookie('AccessCode',$Q_CheckAllowed['cn_disable_access_code'],time()+3600*24*365*5,
                                str_replace('index.php','',$_SERVER['SCRIPT_NAME']),str_replace('www','',$_SERVER['HTTP_HOST']));
						
					}
                    else
                        if (array_key_exists('AccessCode', $_COOKIE) and $_COOKIE['AccessCode'] == $Q_CheckAllowed['cn_disable_access_code'])
                        {
                            $_SESSION['AccessCode'] = $_REQUEST['AccessCode'];
							if( !IsSet( $Q_CheckAllowed['cn_display_as_id'] ) )
								$_SESSION['IsAllowedToUse'] = true;
                        }
					/*else if (array_key_exists('AccessCode', $_SESSION) and $_SESSION['AccessCode'] == $Q_CheckAllowed['cn_disable_access_code']) {
						if( !IsSet( $Q_CheckAllowed['cn_display_as_id'] ) )
							$_SESSION['IsAllowedToUse'] = true;					
					}*/
				}
			}
		}
	}
	  
	if (!array_key_exists('IsAllowedToUse',$_SESSION) && !IsSet( $Q_CheckAllowed['cn_display_as_id'] )) {
		ss_log_message( "Giving Under Construction message to :-" );
		ss_log_message_r(  'Log:'.__FILE__.':'.__LINE__, $_SERVER );
		ss_log_message_r(  'Log:'.__FILE__.':'.__LINE__, $_SESSION );
		die('<html><body>Site Under Construction</body></html>');
	} 
?>
