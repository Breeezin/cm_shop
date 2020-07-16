<?php 
	$this->param('us_id');

	$us_id = (int)$this->ATTRIBUTES['us_id'];

	$ret = array();

	if( ($us_id > 0) && ( $user_row = getRow("select * from users where us_id = $us_id" )) )
	{
		$billing_address1 = strtolower(addslashes($user_row['us_0_50A1']));
		$billing_company = strtolower(addslashes($user_row['us_0_B4BF']));
		$billing_city = strtolower(addslashes($user_row['us_0_50A2']));
		$billing_zip = addslashes($user_row['us_0_B4C0']);
		$billing_phone = addslashes($user_row['us_0_B4C1']);
		$billing_country_state = addslashes($user_row['us_0_50A4']);
		$billing_country = (int)$user_row['us_0_50A4'];
		$billing_state = '';
		$fullname = strtolower(addslashes( $user_row['us_first_name'].' '.$user_row['us_last_name'] ));

		if( $pos = strpos( $billing_country_state, 'text&|&' ) )
			$billing_state = substr( $billing_country_state, $pos + 7 );
		else
			if( $pos = strpos( $billing_country_state, 'select&|&' ) )
			{
				$snum = (int)substr( $billing_country_state, $pos + 9 );
				$billing_state = getField( "select StName from country_states where sts_id = $snum" );
			}

		ss_log_message( "Checking user blacklist for us_id:$us_id name:$fullname addr:$billing_address1 comp:$billing_company city:$billing_city zip:$billing_zip country:$billing_country state:$billing_state phone:$billing_phone" );


		if( $user_row['us_bl_id'] > 0 )
			$ret[] = array( 'score' => 100, 'bl_id' => $user_row['us_bl_id'], 'note' => 'User flagged as blacklisted' );

		// check email address alone
		$email = addslashes($user_row['us_email']);
		if( ($s = GetField( "select min(bl_id) from blacklist where '$email' like bl_email_address" )) > 0 )
			$ret[] = array( 'score' => 99, 'bl_id' => $s,'note' => "Email address $email found in blacklist id:$s" );

		// check phone and country
		if( ($s = GetField( "select min(bl_id) from blacklist where '$billing_phone' like bl_billing_address_phone AND $billing_country = bl_billing_address_country" ) ) > 0 )
			$ret[] = array( 'score' => 99, 'bl_id' => $s,'note' => "Phone number $billing_phone found in blacklist (billing) with same country id:$s" );

		if( ($s = GetField( "select min(bl_id) from blacklist where '$billing_phone' like bl_shipping_address_phone AND $billing_country = bl_shipping_address_country" ) ) > 0 )
			$ret[] = array( 'score' => 99, 'bl_id' => $s,'note' => "Phone number $billing_phone found in blacklist (shipping) with same country id:$s" );

		// check address city country
		if( ($s = GetField( "select min(bl_id) from blacklist where '$billing_address1' like lower(bl_billing_address1)
				AND '$billing_city' like lower(bl_billing_address_city)
				AND $billing_country = bl_billing_address_country" ) ) > 0 )
			$ret[] = array( 'score' => 99, 'bl_id' => $s,'note' => "Billing Address (addr/city/country) match id:$s" );

		if( ($s = GetField( "select min(bl_id) from blacklist where '$billing_address1' like lower(bl_shipping_address1)
				AND '$billing_city' like lower(bl_shipping_address_city)
				AND $billing_country = bl_shipping_address_country" ) ) > 0 )
			$ret[] = array( 'score' => 99, 'bl_id' => $s,'note' => "Shipping Address (addr/city/country) match id:$s" );

		// check address zip country
		if( ($s = GetField( "select min(bl_id) from blacklist where '$billing_address1' like lower(bl_billing_address1)
				AND '$billing_zip' like bl_billing_address_zip
				AND $billing_country = bl_billing_address_country" ) ) > 0 )
			$ret[] = array( 'score' => 99, 'bl_id' => $s,'note' => "Billing Address (addr/zip/country) match id:$s" );

		if( ($s = GetField( "select min(bl_id) from blacklist where '$billing_address1' like lower(bl_shipping_address1)
				AND '$billing_zip' like bl_shipping_address_zip
				AND $billing_country = bl_shipping_address_country" ) ) > 0 )
			$ret[] = array( 'score' => 99, 'bl_id' => $s,'note' => "Shipping Address (addr/zip/country) match id:$s" );

		// check full name
		if( $user_row['us_bl_id'] != -1 )		// unless whitelisted client
		{
		//	if( ($s = GetField( "select min(bl_id) from blacklist where '$fullname' like bl_billing_name AND $billing_country = bl_billing_address_country" )) > 0 )
			if( ($s = GetField( "select min(bl_id) from blacklist where '$fullname' like lower(bl_billing_name)" )) > 0 )
				$ret[] = array( 'score' => 49, 'bl_id' => $s,'note' => "Name $fullname found in blacklist (billing name) id:$s" );

			//if( ($s = GetField( "select min(bl_id) from blacklist where '$fullname' like bl_shipping_name AND $billing_country = bl_shipping_address_country" )) > 0 )
			if( ($s = GetField( "select min(bl_id) from blacklist where '$fullname' like lower(bl_shipping_name)" )) > 0 )
				$ret[] = array( 'score' => 49, 'bl_id' => $s,'note' => "Name $fullname found in blacklist (shipping name) id:$s" );
		}

		// check company name and country
		if( strlen($billing_company) && ($s = GetField( "select min(bl_id) from blacklist where '$billing_company' like lower(bl_billing_company)
				AND $billing_country = bl_billing_address_country" ) ) > 0 )
			$ret[] = array( 'score' => 10, 'bl_id' => $s,'note' => "Billing Address (company/country) match id:$s" );

		if( strlen($billing_company) && ($s = GetField( "select min(bl_id) from blacklist where '$billing_company' like lower(bl_shipping_company)
				AND $billing_country = bl_shipping_address_country" ) ) > 0 )
			$ret[] = array( 'score' => 10, 'bl_id' => $s,'note' => "Shipping Address (company/country) match id:$s" );


		// check partial address / city / country
		$words = preg_split("/[\s,\.]+/", $billing_address1 );

		// remove empty elements, check with numbers
		$words2 = array();
		foreach( $words as $word )
		{
			$newword = '';
			for( $i = 0; $i < strlen( $word ); $i++ )
				if( strspn( $word[$i], '-.,+_*#!~<>{}[]();:' ) )
					;
				else
					$newword .= $word[$i];
			if( strlen( $newword ) >= 2 )
				$words2[] = $newword;
		}

		$check_address = '';
		$wc = 0;
		$wt = count($words);
		$add = NULL;
		foreach( $words2 as $word2 )
		{
			if( !in_array( trim(strtolower( $word2 )), $this->junkwords ) )
			{
				$wc++;
				$check_address .= '[[:<:]]'.$word2.'[[:>:]].*';

				if( $wc > 1 )
				{
					if( ($s = GetField( "select min(bl_id) from blacklist where lower(bl_billing_address1) regexp '$check_address'
						AND '$billing_city' like lower(bl_billing_address_city)
						AND $billing_country = bl_billing_address_country" ) ) > 0 )
						$add = array( 'score' => (int)($wc*99/$wt), 'bl_id' => $s,'note' => "Partial Billing Address (addr:$check_address/city:$billing_city/country:$billing_country) match id:$s" );

					if( ($s = GetField( "select min(bl_id) from blacklist where lower(bl_shipping_address1) regexp '$check_address'
						AND '$billing_city' like lower(bl_shipping_address_city)
						AND $billing_country = bl_shipping_address_country" ) ) > 0 )
						$add = array( 'score' => (int)($wc*99/$wt), 'bl_id' => $s,'note' => "Partial Shipping Address (addr:$check_address/city:$billing_city/country:$billing_country) match id:$s" );

					if( ($s = GetField( "select min(bl_id) from blacklist where lower(bl_billing_address1) regexp '$check_address'
						AND '$billing_zip' like bl_billing_address_zip
						AND $billing_country = bl_billing_address_country" ) ) > 0 )
						$add = array( 'score' => (int)($wc*99/$wt), 'bl_id' => $s,'note' => "Partial Billing Address (addr:$check_address/zip:$billing_zip/country:$billing_country) match id:$s" );

					if( ($s = GetField( "select min(bl_id) from blacklist where lower(bl_shipping_address1) regexp '$check_address'
						AND '$billing_zip' like bl_shipping_address_zip
						AND $billing_country = bl_shipping_address_country" ) ) > 0 )
						$add = array( 'score' => (int)($wc*99/$wt), 'bl_id' => $s,'note' => "Partial Shipping Address (addr:$check_address/zip:$billing_zip/country:$billing_country) match id:$s" );
				}
			}
			else
				ss_log_message( "Ignoring $word2" );
		}
		if( $add )
			$ret[] = $add;

		// remove empty elements check without numbers etc
		$words2 = array();
		foreach( $words as $word )
		{
			$newword = '';
			for( $i = 0; $i < strlen( $word ); $i++ )
				if( strspn( $word[$i], '-.,+_*#!~<>{}[]();:0123456789' ) )
					;
				else
					$newword .= $word[$i];
			if( strlen( $newword ) >= 2 )
				$words2[] = $newword;
		}

		$check_address = '';
		$wc = 0;
		$wt = count($words);
		$add = NULL;
		foreach( $words2 as $word2 )
		{
			if( !in_array( trim(strtolower( $word2 )), $this->junkwords ) )
			{
				$wc++;
				$check_address .= '[[:<:]]'.$word2.'[[:>:]].*';

				if( $wc > 1 )
				{
					if( ($s = GetField( "select min(bl_id) from blacklist where lower(bl_billing_address1) regexp '$check_address'
						AND '$billing_city' like lower(bl_billing_address_city)
						AND $billing_country = bl_billing_address_country" ) ) > 0 )
						$add = array( 'score' => (int)($wc*10/$wt), 'bl_id' => $s,'note' => "Partial Billing Address (addr:$check_address/city:$billing_city/country:$billing_country) match id:$s" );

					if( ($s = GetField( "select min(bl_id) from blacklist where lower(bl_shipping_address1) regexp '$check_address'
						AND '$billing_city' like lower(bl_shipping_address_city)
						AND $billing_country = bl_shipping_address_country" ) ) > 0 )
						$add = array( 'score' => (int)($wc*10/$wt), 'bl_id' => $s,'note' => "Partial Shipping Address (addr:$check_address/city:$billing_city/country:$billing_country) match id:$s" );

					if( ($s = GetField( "select min(bl_id) from blacklist where lower(bl_billing_address1) regexp '$check_address'
						AND '$billing_zip' like bl_billing_address_zip
						AND $billing_country = bl_billing_address_country" ) ) > 0 )
						$add = array( 'score' => (int)($wc*10/$wt), 'bl_id' => $s,'note' => "Partial Billing Address (addr:$check_address/zip:$billing_zip/country:$billing_country) match id:$s" );

					if( ($s = GetField( "select min(bl_id) from blacklist where lower(bl_shipping_address1) regexp '$check_address'
						AND '$billing_zip' like bl_shipping_address_zip
						AND $billing_country = bl_shipping_address_country" ) ) > 0 )
						$add = array( 'score' => (int)($wc*10/$wt), 'bl_id' => $s,'note' => "Partial Shipping Address (addr:$check_address/zip:$billing_zip/country:$billing_country) match id:$s" );
				}
			}
		}
		if( $add )
			$ret[] = $add;

		usort( $ret, 'ShopSystem_BlackListAdministration::score_sort' );
	}
	else
		ss_log_message( "No us_id passed in ".$this->ATTRIBUTES['us_id'] );


	ss_log_message_r(  'Blacklist check returning:'.__FILE__.':'.__LINE__, $ret );

	return $ret;

?>
