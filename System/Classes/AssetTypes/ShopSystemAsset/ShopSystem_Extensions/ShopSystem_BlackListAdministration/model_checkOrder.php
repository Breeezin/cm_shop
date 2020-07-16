<?php 
	$this->param('tr_id');

	$tr_id = (int) $this->ATTRIBUTES['tr_id'];

	$ret = array();

	ss_log_message( "Checking user blacklist against Transaction:$tr_id" );

	$Order = getRow("SELECT or_id, or_us_id, or_shipping_details FROM shopsystem_orders WHERE or_tr_id = $tr_id");
	if( $Transaction = getRow("SELECT * FROM transactions where tr_id = $tr_id") )
	{
		// check the users record first, takes care of the billing details.
		$foo = new Request('shopsystem_blacklist.CheckClient', array( 'us_id' => $Order['or_us_id'] ) );
		$ret = $foo->value;

		$details = unserialize($Order['or_shipping_details']);
		
		// only check shipping details, billing details checked from user record in model_checkUser

		ss_paramKey($details['PurchaserDetails'],'0_50A1','');
		ss_paramKey($details['ShippingDetails'],'0_50A1','');

		$ret = array_merge( $ret, $this->checkAddress( $details['PurchaserDetails'], $this->junkwords, false ));
		$ret = array_merge( $ret, $this->checkAddress( $details['ShippingDetails'], $this->junkwords, true ));

		// check IP addresses and blocks...

		$oip = $Transaction['tr_ip_address'];

		if( getField( "select count(*) from proxy_addresses where ip_address = '$oip'" ) == 0 )
		{

			if( ($s = GetField( "select min(bl_id) from blacklist join blacklist_ip_addresses on blip_bl_id = bl_id where blip_ip_address = '$oip'" ) ) )
				$ret[] = array( 'score' => 50, 'bl_id' => $s,'note' => "IP address ($oip) found in blacklist id:$s" );

			// more advanced checks

			// netblock banned
			if( ($s = GetField( "select min(bl_id) from blacklist join blacklist_ip_addresses on blip_bl_id = bl_id where blip_netblock_start <= INET_ATON('$oip') and blip_netblock_end >= INET_ATON('$oip') and blip_ban_netblock = true" ) ) )
				$ret[] = array( 'score' => 100, 'bl_id' => $s,'note' => "IP address banned by netblock ($oip) in blacklist id:$s" );

			// same browser ident AND raw fingerprint AND netblock
			$bident = escape( trim( $Transaction['tr_browser_ident'] ) );
			$fingerp = escape( trim( $Transaction['tr_fingerprint'] ) );

			if( ($s = GetField( "select min(bl_id) from blacklist join blacklist_ip_addresses on blip_bl_id = bl_id where blip_netblock_start <= INET_ATON('$oip') and blip_netblock_end >= INET_ATON('$oip') and blip_browser_ident = '$bident' and blip_raw_fingerprint = '$fingerp'" ) ) )
				$ret[] = array( 'score' => 10, 'bl_id' => $s,'note' => "Very similar computer setup in netblock ($oip) in blacklist id:$s" );

		}

		// check CC number 

		if( $ccnotes = getField( "select orn_text from shopsystem_order_notes where orn_or_id = {$Order['or_id']} and orn_text like 'Card:%'" ) )
		{
			// glob the card number bits [5..]
			if( $ccnotes[5] == 'x' )		// unknown issuer
			{
				$last4 = substr($ccnotes, 17, 4);
				$issuer_name = '';
				$issuer_num  = 0;
				$expiry = substr($ccnotes, 29, 4)."-".substr($ccnotes, 26, 2)."-01";
			}
			else
			{
				$last4 = substr($ccnotes, 15, 4);
				$issuer_name = substr( $ccnotes, 47 );
				$issuer_num  = (int)substr( $ccnotes, 5 );		// unknown length so cheat
				$expiry = "20".substr($ccnotes, 27, 2)."-".substr($ccnotes, 24, 2)."-01";
			}

			ss_log_message( "checking card issuer:$issuer_num last4:$last4 expiry:$expiry" );

			if( ($s = GetField( "select min(bl_id) from blacklist join blacklist_cc_details on blcc_bl_id = bl_id where blcc_issuer_num = '$issuer_num' and blcc_last4 = '$last4' and blcc_expiry_date = '$expiry'" ) ) )
				$ret[] = array( 'score' => 100, 'bl_id' => $s,'note' => "Same card used in blacklist id:$s" );

			if( ($s = GetField( "select min(bl_id) from blacklist join blacklist_cc_details on blcc_bl_id = bl_id where blcc_last4 = '$last4' and blcc_expiry_date = '$expiry'" ) ) )
				$ret[] = array( 'score' => 50, 'bl_id' => $s,'note' => "Possibly same card used in blacklist id:$s" );
		}


		usort( $ret, 'ShopSystem_BlackListAdministration::score_sort' );
	}

	ss_log_message( "Returning" );
	ss_log_message_r(  'Log:'.__FILE__.':'.__LINE__, $ret );

	return $ret;

?>
