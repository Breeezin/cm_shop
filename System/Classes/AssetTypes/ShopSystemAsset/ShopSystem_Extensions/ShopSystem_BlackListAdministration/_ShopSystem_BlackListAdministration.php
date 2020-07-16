<?php
requireOnceClass('Administration');
class ShopSystem_BlackListAdministration extends Administration {

	var $junkwords = array( 'unit', 'building', 'road', 'rd', 'street', 'st', 'ave', 'rue', 'th', 'avenue', 'way', 'west', 'north', 'south', 'east', 'blvd', 'boulevard', 'po', 'box', 'room', 'lane', 'no', 'flat' );

	static function score_sort($a, $b) { return ($a['score'] < $b['score']) ? 1 : (($a['score'] > $b['score']) ? -1 : 0); }

	function exposeServices() {

		return array_merge(array(
				'shopsystem_blacklist.AddClient' => array('method'	=>	'addClient'), 
				'shopsystem_blacklist.updateClient' => array('method'	=>	'updateClient'), 
				'shopsystem_blacklist.AddWhiteList' => array('method'	=>	'addWhiteList'), 
				'shopsystem_blacklist.RemoveClient' => array('method'	=>	'removeClient'), 
				'shopsystem_blacklist.RemoveID' => array('method'	=>	'removeID'), 
				'shopsystem_blacklist.CheckClient' => array('method' =>	'checkClient'), 
				'shopsystem_blacklist.CheckOrder' => array('method' =>	'checkOrder'), 
			),
			Administration::exposeServicesUsing('ShopSystem_BlackList'));
	}

	function inputFilter() {
		$this->param('BreadCrumbs','Administration');
		$this->display->layout = 'Administration';
//		$result = new Request('Security.Authenticate',array( 'Permission'	=>	'CanAdministerAtLeastOneAsset'));
		
	}

	function addClient() {
		$result = new Request('Security.Authenticate',array( 'Permission'	=>	'CanAdministerAtLeastOneAsset'));
		require('view_addClient.php');
		require('model_addClient.php');
	}

	function updateClient() {
//		$result = new Request('Security.Authenticate',array( 'Permission'	=>	'CanAdministerAtLeastOneAsset'));
		require('model_updateClient.php');
	}

	function addWhiteList() {
		$result = new Request('Security.Authenticate',array( 'Permission'	=>	'CanAdministerAtLeastOneAsset'));
		require('model_addWhiteList.php');
	}

	function removeClient() {
		$result = new Request('Security.Authenticate',array( 'Permission'	=>	'CanAdministerAtLeastOneAsset'));
		require('model_removeClient.php');
	}

	function removeID() {
		$result = new Request('Security.Authenticate',array( 'Permission'	=>	'CanAdministerAtLeastOneAsset'));
		require('model_removeID.php');
	}

	function checkClient() {
		return require('model_checkClient.php');
	}

	function checkOrder() {
		return require('model_checkOrder.php');
	}

	function __construct() {		

		//$this->Administration(array(
		parent::__construct( array(
			'prefix'					=>	'ShopSystem_Blacklist',
			'singular'					=>	'Black Listed Client',
			'plural'					=>	'Black Listed Clients',
			'tableName'					=>	'shopsystem_blacklist',
			'tablePrimaryKey'			=>	'bl_id',
			'tableDisplayFields'		=>	array('bl_id', 'bl_us_id', 
				'bl_billing_name', 'bl_billing_company', 'bl_billing_address1', 'bl_billing_address_city', 'bl_billing_address_state',
				'bl_billing_address_phone',
				'bl_shipping_name', 'bl_shipping_company', 'bl_shipping_address1', 'bl_shipping_address_city', 'bl_shipping_address_state',
				'bl_shipping_address_phone',
				'bl_email_address', 'bl_reason', 'bl_last_tr_id'  ),
			'tableSearchFields'			=>	array('bl_id', 'bl_us_id', 
				'bl_billing_name', 'bl_billing_company', 'bl_billing_address1', 'bl_billing_address_city', 'bl_billing_address_state', 'bl_billing_address_country',
				'bl_billing_address_phone', 'bl_billing_address_zip',
				'bl_shipping_name', 'bl_shipping_company', 'bl_shipping_address1', 'bl_shipping_address_city', 'bl_shipping_address_state', 'bl_shipping_address_country',
				'bl_shipping_address_phone', 'bl_shipping_address_zip',
				'bl_email_address', 'bl_reason', 'bl_last_tr_id', 'bl_notes' ),
			'tableDisplayFieldTitles'	=>	array('Blacklist ID', 'User ID', 
				'Billing Name', 'Billing Company', 'Billing Address', 'Billing City', 'Billing State',
				'Billing Phone',
				'Shipping Name', 'Shipping Company', 'Shipping Address', 'Shipping City', 'Shipping State',
				'Shipping Phone',
				'Email Address', 'Reason', 'Last Order Number' ),
			'tableOrderBy'				=>	array('bl_id' => 'Blacklist Ident', 'bl_us_id' => 'User Ident', 'bl_billing_name' => 'Billing Name','bl_shipping_name' => 'Shipping Name','bl_email_address' => 'Email'),			
			'tableSortOrderField'		=>	'bl_id',
		));

		$this->addLinkedTable(new LinkedTable(array(
			'tableName'	=>	'blacklist_ip_addresses',
			'ourKey'	=>	'blip_bl_id',
		)));		

		$this->addLinkedTable(new LinkedTable(array(
			'tableName'	=>	'blacklist_cc_details',
			'ourKey'	=>	'blcc_bl_id',
		)));		

		$t = new TextField (array(
			'name'			=>	'bl_billing_name',
			'displayName'	=>	'Billing Name',
			'note'			=>	null,
            'required'        =>    false,
			'verify'		=>	false,
			'trim'			=>	true,
			'unique'		=>	false,
			'size'	=>	'40',	'maxLength'	=>	'255',
		));
		$this->addField( $t );

		$t = new TextField (array(
			'name'			=>	'bl_billing_company',
			'displayName'	=>	'Billing Company',
			'note'			=>	null,
            'required'        =>    false,
			'verify'		=>	false,
			'trim'			=>	true,
			'unique'		=>	false,
			'size'	=>	'40',	'maxLength'	=>	'255',
		));
		$this->addField( $t );

		$t = new TextField (array(
			'name'			=>	'bl_billing_address1',
			'displayName'	=>	'Billing Address',
			'note'			=>	null,
            'required'        =>    false,
			'verify'		=>	false,
			'trim'			=>	true,
			'unique'		=>	false,
			'size'	=>	'40',	'maxLength'	=>	'255',
		));
		$this->addField( $t );

		$t = new TextField (array(
			'name'			=>	'bl_billing_address_city',
			'displayName'	=>	'Billing City',
			'note'			=>	null,
            'required'        =>    false,
			'verify'		=>	false,
			'trim'			=>	true,
			'unique'		=>	false,
			'size'	=>	'40',	'maxLength'	=>	'255',
		));
		$this->addField( $t );

		$t = new TextField (array(
			'name'			=>	'bl_billing_address_state',
			'displayName'	=>	'Billing State',
			'note'			=>	null,
            'required'        =>    false,
			'verify'		=>	false,
			'trim'			=>	true,
			'unique'		=>	false,
			'size'	=>	'40',	'maxLength'	=>	'255',
		));
		$this->addField( $t );

        $t = new SelectField (array(
            'name'            =>    'bl_billing_address_country',
            'displayName'    =>    'Billing Country',
            'note'            =>    null,
            'required'        =>    false,
            'verify'        =>    false,
            'unique'        =>    false,
            'size'    =>    '30',    'maxLength'    =>    '255',
            'rows'    =>    '6',    'cols'        =>    '40',
			'linkQuery'		=>	'select * from countries',
			'linkQueryValueField'	=>	'cn_id',
			'linkQueryDisplayField'	=>	array( 'cn_id', 'cn_name' ),
        ));
		$this->addField( $t );

		$t = new TextField (array(
			'name'			=>	'bl_billing_address_phone',
			'displayName'	=>	'Billing Phone',
			'note'			=>	null,
            'required'        =>    false,
			'verify'		=>	false,
			'trim'			=>	true,
			'unique'		=>	false,
			'size'	=>	'40',	'maxLength'	=>	'255',
		));
		$this->addField( $t );

		$t = new TextField (array(
			'name'			=>	'bl_billing_address_zip',
			'displayName'	=>	'Billing Zipcode',
			'note'			=>	null,
            'required'        =>    false,
			'verify'		=>	false,
			'trim'			=>	true,
			'unique'		=>	false,
			'size'	=>	'40',	'maxLength'	=>	'255',
		));
		$this->addField( $t );

		$t = new TextField (array(
			'name'			=>	'bl_shipping_name',
			'displayName'	=>	'Shipping Name',
			'note'			=>	null,
            'required'        =>    false,
			'verify'		=>	false,
			'trim'			=>	true,
			'unique'		=>	false,
			'size'	=>	'40',	'maxLength'	=>	'255',
		));
		$this->addField( $t );

		$t = new TextField (array(
			'name'			=>	'bl_shipping_company',
			'displayName'	=>	'Shipping Company',
			'note'			=>	null,
            'required'        =>    false,
			'verify'		=>	false,
			'trim'			=>	true,
			'unique'		=>	false,
			'size'	=>	'40',	'maxLength'	=>	'255',
		));
		$this->addField( $t );

		$t = new TextField (array(
			'name'			=>	'bl_shipping_address1',
			'displayName'	=>	'Shipping Address',
			'note'			=>	null,
            'required'        =>    false,
			'verify'		=>	false,
			'trim'			=>	true,
			'unique'		=>	false,
			'size'	=>	'40',	'maxLength'	=>	'255',
		));
		$this->addField( $t );

		$t = new TextField (array(
			'name'			=>	'bl_shipping_address_city',
			'displayName'	=>	'Shipping City',
			'note'			=>	null,
            'required'        =>    false,
			'verify'		=>	false,
			'trim'			=>	true,
			'unique'		=>	false,
			'size'	=>	'40',	'maxLength'	=>	'255',
		));
		$this->addField( $t );

		$t = new TextField (array(
			'name'			=>	'bl_shipping_address_state',
			'displayName'	=>	'Shipping State',
			'note'			=>	null,
            'required'        =>    false,
			'verify'		=>	false,
			'trim'			=>	true,
			'unique'		=>	false,
			'size'	=>	'40',	'maxLength'	=>	'255',
		));
		$this->addField( $t );

        $t = new SelectField (array(
            'name'            =>    'bl_shipping_address_country',
            'displayName'    =>    'Shipping Country',
            'note'            =>    null,
            'required'        =>    false,
            'verify'        =>    false,
            'unique'        =>    false,
            'size'    =>    '30',    'maxLength'    =>    '255',
            'rows'    =>    '6',    'cols'        =>    '40',
			'linkQuery'		=>	'select * from countries',
			'linkQueryValueField'	=>	'cn_id',
			'linkQueryDisplayField'	=>	array( 'cn_id', 'cn_name' ),
        ));
		$this->addField( $t );

		$t = new TextField (array(
			'name'			=>	'bl_shipping_address_phone',
			'displayName'	=>	'Shipping Phone',
			'note'			=>	null,
            'required'        =>    false,
			'verify'		=>	false,
			'trim'			=>	true,
			'unique'		=>	false,
			'size'	=>	'40',	'maxLength'	=>	'255',
		));
		$this->addField( $t );

		$t = new TextField (array(
			'name'			=>	'bl_shipping_address_zip',
			'displayName'	=>	'Shipping Zipcode',
			'note'			=>	null,
            'required'        =>    false,
			'verify'		=>	false,
			'trim'			=>	true,
			'unique'		=>	false,
			'size'	=>	'40',	'maxLength'	=>	'255',
		));
		$this->addField( $t );

		$t = new TextField (array(
			'name'			=>	'bl_email_address',
			'displayName'	=>	'Email Address',
			'note'			=>	null,
            'required'        =>    false,
			'verify'		=>	false,
			'trim'			=>	true,
			'unique'		=>	false,
			'size'	=>	'40',	'maxLength'	=>	'255',
		));
		$this->addField( $t );

        $t = new SelectField (array(
            'name'            =>    'bl_reason',
            'displayName'    =>    'Reason',
            'note'            =>    null,
            'required'        =>    false,
            'verify'        =>    false,
            'unique'        =>    false,
            'size'    =>    '30',    'maxLength'    =>    '255',
            'rows'    =>    '6',    'cols'        =>    '40',
			'tableName'	=>	'blacklist',
			'enumField'	=>	'bl_reason',
        ));
		$this->addField( $t );

		$t = new IntegerField($t2 = array(
			'name'			=>	'bl_last_tr_id',
			'displayName'	=>	'Last Order Number',
			'note'			=>	NULL,
			'required'		=>	FALSE,
			'verify'		=>	FALSE,
			'unique'		=>	FALSE,
			'size'	=>	'10',	'maxLength'	=>	'8',					
		));
		$this->addField( $t );

		$t = new MemoField (array(
			'name'			=>	'bl_notes',
			'displayName'	=>	'Notes',
			'required'		=>	false,
			'verify'		=>	false,
			'trim'			=>	true,
			'unique'		=>	false,
			'size'	=>	'30',	'maxLength'	=>	'255',
			'cols'	=>	40,	'rows'	=>	10,
		));								
		$this->addField( $t );
	}

	function checkAddress( $address, $junk, $isShipping = false )
	{
		$ret = array();

		$fullname = strtolower(escape(rtrim(ltrim($address['name']))));
		$address1 = strtolower(escape(rtrim(ltrim($address['0_50A1']))));
		$email = strtolower(escape(rtrim(ltrim($address['email']))));
		$company = strtolower(escape(rtrim(ltrim($address['0_B4BF']))));
		$city = strtolower(escape(rtrim(ltrim($address['0_50A2']))));
		$zip = escape(rtrim(ltrim($address['0_B4C0'])));
		$phone = escape(rtrim(ltrim($address['0_B4C1'])));
		$country_state = escape(rtrim(ltrim($address['0_50A4'])));
		$country = (int)escape(rtrim(ltrim($address['0_50A4'])));
		$state = '';

		if( $pos = strrpos( $country_state, '>' ) )
		{
			$cname = substr( $country_state, ++$pos );
			$country = getField( "select cn_id from countries where cn_name = '$cname'" );
		}

		if( $pos = strrpos( $country_state, '<' ) )
		{
			$ccode = substr( $country_state, 0, $pos );
			$state = getField( "select StName from country_states where StCode = '$ccode'" );
			if( !strlen( $state ) )
				$state = $ccode;
		}

		ss_log_message( "Checking address blacklist for name:$fullname addr:$address1 comp:$company city:$city zip:$zip country:$country state:$state phone:$phone ($country_state)" );

		// in order of descending score

		// check email address alone
		if( ($s = GetField( "select min(bl_id) from blacklist where '$email' like lower(bl_email_address)" )) > 0 )
			$ret[] = array( 'score' => 99, 'bl_id' => $s,'note' => "Email address $email found in blacklist id:$s" );

		// check phone and country
		if( ($s = GetField( "select min(bl_id) from blacklist where '$phone' like bl_billing_address_phone AND $country = bl_billing_address_country" ) ) > 0 )
			$ret[] = array( 'score' => 99, 'bl_id' => $s,'note' => "Phone number $phone found in blacklist (billing) with same country id:$s" );

		if( ($s = GetField( "select min(bl_id) from blacklist where '$phone' like bl_shipping_address_phone AND $country = bl_shipping_address_country" ) ) > 0 )
			$ret[] = array( 'score' => 99, 'bl_id' => $s,'note' => "Phone number $phone found in blacklist (shipping) with same country id:$s" );

		// check address city country
		if( ($s = GetField( "select min(bl_id) from blacklist where '$address1' like lower(bl_billing_address1)
				AND '$city' like lower(bl_billing_address_city)
				AND $country = bl_billing_address_country" ) ) > 0 )
			$ret[] = array( 'score' => 99, 'bl_id' => $s,'note' => "Billing Address (addr/city/country) match id:$s" );

		if( ($s = GetField( "select min(bl_id) from blacklist where '$address1' like lower(bl_billing_address1)
				AND '$city' like lower(bl_shipping_address_city)
				AND $country = bl_shipping_address_country" ) ) > 0 )
			$ret[] = array( 'score' => 99, 'bl_id' => $s,'note' => "Shipping Address (addr/city/country) match id:$s" );

		// check address zip country
		if( ($s = GetField( "select min(bl_id) from blacklist where '$address1' like lower(bl_billing_address1)
				AND '$zip' like bl_billing_address_zip
				AND $country = bl_billing_address_country" ) ) > 0 )
			$ret[] = array( 'score' => 99, 'bl_id' => $s,'note' => "Billing Address (addr/zip/country) match id:$s" );

		if( ($s = GetField( "select min(bl_id) from blacklist where '$address1' like lower(bl_shipping_address1)
				AND '$zip' like bl_shipping_address_zip
				AND $country = bl_shipping_address_country" ) ) > 0 )
			$ret[] = array( 'score' => 99, 'bl_id' => $s,'note' => "Shipping Address (addr/zip/country) match id:$s" );

		// check full name
		if( ($s = GetField( "select min(bl_id) from blacklist where '$fullname' like lower(bl_billing_name)" )) > 0 )
			$ret[] = array( 'score' => 49, 'bl_id' => $s,'note' => "Name $fullname found in blacklist (billing name) id:$s" );

		if( ($s = GetField( "select min(bl_id) from blacklist where '$fullname' like lower(bl_shipping_name)" )) > 0 )
			$ret[] = array( 'score' => 49, 'bl_id' => $s,'note' => "Name $fullname found in blacklist (shipping name) id:$s" );

		// check company name and country
		if( strlen($company) && ($s = GetField( "select min(bl_id) from blacklist where '$company' like lower(bl_billing_company)
				AND $country = bl_billing_address_country" ) ) > 0 )
			$ret[] = array( 'score' => 10, 'bl_id' => $s,'note' => "Billing Address (company/country) match id:$s" );

		if( strlen($company) && ($s = GetField( "select min(bl_id) from blacklist where '$company' like lower(bl_shipping_company)
				AND $country = bl_shipping_address_country" ) ) > 0 )
			$ret[] = array( 'score' => 10, 'bl_id' => $s,'note' => "Shipping Address (company/country) match id:$s" );


		// check partial address / city / country
		$words = preg_split("/[\s,\.]+/", $address1 );

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
			if( strlen( $newword ) >= 1 )
				$words2[] = $newword;
		}

		$check_address = '';
		$wc = 0;
		$wt = count($words);
		$add = NULL;
		foreach( $words2 as $word2 )
		{
			if( !in_array( trim(strtolower( $word2 )), $junk ) )
			{
				$wc++;
				$check_address .= '[[:<:]]'.$word2.'[[:>:]].*';

				$weight = 99;
				if( $isShipping )
					$weight += 2;

				if( $wc > 1 )
				{
//					ss_log_message( "check address is $check_address" );
					if( ($s = GetField( "select min(bl_id) from blacklist where bl_billing_address1 regexp '$check_address'
						AND '$city' like lower(bl_billing_address_city)
						AND $country = bl_billing_address_country" ) ) > 0 )
						$add = array( 'score' => (int)($wc*99/$wt), 'bl_id' => $s,'note' => "A Partial Billing Address 1 (addr:$check_address/city:$city/country:$country) match id:$s" );

					if( ($s = GetField( "select min(bl_id) from blacklist where bl_shipping_address1 regexp '$check_address'
						AND '$city' like lower(bl_shipping_address_city)
						AND $country = bl_shipping_address_country" ) ) > 0 )
						$add = array( 'score' => (int)($wc*99/$wt), 'bl_id' => $s,'note' => "A Partial Shipping Address 2 (addr:$check_address/city:$city/country:$country) match id:$s" );

					if( ($s = GetField( "select min(bl_id) from blacklist where bl_shipping_address1 regexp '$check_address' AND ( bl_billing_address1 IS NULL OR bl_billing_address1 = '' )
						AND '$city' like lower(bl_shipping_address_city)
						AND $country = bl_shipping_address_country" ) ) > 0 )
						$add = array( 'score' => (int)($wc*$weight/$wt), 'bl_id' => $s,'note' => "A Partial Shipping Address 3 (addr:$check_address/city:$city/country:$country) match id:$s" );

					if( ($s = GetField( "select min(bl_id) from blacklist where bl_billing_address1 regexp '$check_address'
						AND '$zip' like bl_billing_address_zip
						AND $country = bl_billing_address_country" ) ) > 0 )
						$add = array( 'score' => (int)($wc*99/$wt), 'bl_id' => $s,'note' => "A Partial Billing Address 4 (addr:$check_address/zip:$zip/country:$country) match id:$s" );

					if( ($s = GetField( "select min(bl_id) from blacklist where bl_shipping_address1 regexp '$check_address'
						AND '$zip' like bl_shipping_address_zip
						AND $country = bl_shipping_address_country" ) ) > 0 )
						$add = array( 'score' => (int)($wc*99/$wt), 'bl_id' => $s,'note' => "A Partial Shipping Address 5 (addr:$check_address/zip:$zip/country:$country) match id:$s" );

					if( ($s = GetField( "select min(bl_id) from blacklist where bl_shipping_address1 regexp '$check_address'  AND ( bl_billing_address1 IS NULL OR bl_billing_address1 = '' )
						AND '$zip' like bl_shipping_address_zip
						AND $country = bl_shipping_address_country" ) ) > 0 )
						$add = array( 'score' => (int)($wc*$weight/$wt), 'bl_id' => $s,'note' => "A Partial Shipping Address 6 (addr:$check_address/zip:$zip/country:$country) match id:$s" );

				}
				else
					ss_log_message( "words = $wc (<= 1)" );
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
			if( !in_array( trim(strtolower( $word2 )), $junk ) )
			{
				$wc++;
				$check_address .= '[[:<:]]'.$word2.'[[:>:]].*';

				if( $wc > 1 )
				{
					if( ($s = GetField( "select min(bl_id) from blacklist where bl_billing_address1 regexp '$check_address'
						AND '$city' like lower(bl_billing_address_city)
						AND $country = bl_billing_address_country" ) ) > 0 )
						$add = array( 'score' => (int)($wc*10/$wt), 'bl_id' => $s,'note' => "B Partial Billing Address (addr:$check_address/city:$city/country:$country) match id:$s" );

					if( ($s = GetField( "select min(bl_id) from blacklist where bl_billing_address1 regexp '$check_address'
						AND '$city' like lower(bl_shipping_address_city)
						AND $country = bl_shipping_address_country" ) ) > 0 )
						$add = array( 'score' => (int)($wc*10/$wt), 'bl_id' => $s,'note' => "B Partial Shipping Address (addr:$check_address/city:$city/country:$country) match id:$s" );

					if( ($s = GetField( "select min(bl_id) from blacklist where bl_billing_address1 regexp '$check_address'
						AND '$zip' like bl_billing_address_zip
						AND $country = bl_billing_address_country" ) ) > 0 )
						$add = array( 'score' => (int)($wc*10/$wt), 'bl_id' => $s,'note' => "B Partial Billing Address (addr:$check_address/zip:$zip/country:$country) match id:$s" );

					if( ($s = GetField( "select min(bl_id) from blacklist where bl_shipping_address1 regexp '$check_address'
						AND '$zip' like bl_shipping_address_zip
						AND $country = bl_shipping_address_country" ) ) > 0 )
						$add = array( 'score' => (int)($wc*10/$wt), 'bl_id' => $s,'note' => "B Partial Shipping Address (addr:$check_address/zip:$zip/country:$country) match id:$s" );
				}
			}
		}
		if( $add )
			$ret[] = $add;

		return $ret;
	}

	static function xml_token_extract( $token, $in )
	{
		if( preg_match("/<$token>([^>]*)<\/$token>/", $in, $matches ) )
			return $matches[1];
		return NULL;
	}

	// thanks Matt on php.net
	static function print_r_reverse($in) {
		$lines = explode("\n", trim($in));
		if (trim($lines[0]) != 'Array') {
			// bottomed out to something that isn't an array
			return $in;
		} else {
			// this is an array, lets parse it
			if (preg_match("/(\s{5,})\(/", $lines[1], $match)) {
				// this is a tested array/recursive call to this function
				// take a set of spaces off the beginning
				$spaces = $match[1];
				$spaces_length = strlen($spaces);
				$lines_total = count($lines);
				for ($i = 0; $i < $lines_total; $i++) {
					if (substr($lines[$i], 0, $spaces_length) == $spaces) {
						$lines[$i] = substr($lines[$i], $spaces_length);
					}
				}
			}
			array_shift($lines); // Array
			array_shift($lines); // (
			array_pop($lines); // )
			$in = implode("\n", $lines);
			// make sure we only match stuff with 4 preceding spaces (stuff for this array and not a nested one)
			preg_match_all("/^\s{4}\[(.+?)\] \=\> /m", $in, $matches, PREG_OFFSET_CAPTURE | PREG_SET_ORDER);
			$pos = array();
			$previous_key = '';
			$in_length = strlen($in);
			// store the following in $pos:
			// array with key = key of the parsed array's item
			// value = array(start position in $in, $end position in $in)
			foreach ($matches as $match) {
				$key = $match[1][0];
				$start = $match[0][1] + strlen($match[0][0]);
				$pos[$key] = array($start, $in_length);
				if ($previous_key != '') $pos[$previous_key][1] = $match[0][1] - 1;
				$previous_key = $key;
			}
			$ret = array();
			foreach ($pos as $key => $where) {
				// recursively see if the parsed out value is an array too
				$ret[$key] = ShopSystem_BlackListAdministration::print_r_reverse(substr($in, $where[0], $where[1] - $where[0]));
			}
			return $ret;
		}
	} 
}
?>
