<?php 
	
	$data = array(
		'or_id'	=>	$this->ATTRIBUTES['or_id'],
		'tr_id'	=>	$this->ATTRIBUTES['tr_id'],
		'as_id'	=>	$this->ATTRIBUTES['as_id'],
		'BreadCrumbs'	=>	$this->ATTRIBUTES['BreadCrumbs'],
		'Q_OrderNotes'	=>	$Q_OrderNotes,
	);

	$data['transactions'] = getRow("select * from transactions where tr_id = {$this->ATTRIBUTES['tr_id']}" );
	if( $data['transactions']['tr_reship_link'] > 0 )
		$data['ReshipOrID'] = getField("select or_id from shopsystem_orders where or_tr_id = {$data['transactions']['tr_reship_link']}");

	$_SESSION['DefaultCurrency'] = $data['transactions']['tr_currency_code'];

	$Q = getRow( "select count(*) as count from unusable_emails where email_address like '$purchaserEmail'" );
	$data['killEmail'] = ( $Q['count'] > 0 );

	if( strlen( $data['transactions']['tr_ip_address'] ) )
	{
		$Q = getRow( "select count(*) as count from proxy_addresses where ip_address = '{$data['transactions']['tr_ip_address']}'" );
		$data['isProxy'] = ( $Q['count'] > 0 );
	}
	else
		$data['isProxy'] = false;

	$Q_ReshippedIn = getRow( "select transactions.*, shopsystem_orders.or_id from transactions join  shopsystem_orders on or_tr_id = tr_id where tr_reship_link = ".safe($this->ATTRIBUTES['tr_id']) );
	if( strlen( $Q_ReshippedIn['tr_reship_link'] ) )
	{
		$data['ReshippedInTransaction'] = $Q_ReshippedIn['tr_id'];
		$data['ReshippedInOrder'] = $Q_ReshippedIn['or_id'];
	}

	// calculate the number of work days from the shipped date to now.

	if( strlen( $Order['or_shipped'] ) )
		$data['Transit'] = days_in_transit( $Order['or_shipped'], $Order['or_country'] );
	else
		$data['Transit'] = 0;

	$data['bl_idstring'] = $bl_idstring;
	$data['or_shipped'] = $or_shipped;
	$data['Shop'] = $OrderDetails;
	$data['OrderListNumber'] = $OrderListNumber;
	$data['AwaitingList'] = $AwaitingList;
	$data['BlackListedClient'] = $blackListedClient;
	$data['ChargeListHTML'] = $chargeListHTML;
	
	$data['DisplayOrID'] = $Order['tr_id'];
	$data['or_recorded'] = $Order['or_recorded'];
	
	$data['or_purchaser_firstname'] = $Order['or_purchaser_firstname'];
	$data['or_purchaser_lastname'] = $Order['or_purchaser_lastname'];	
	$data['or_us_id'] = $Order['or_us_id'];	
	$data['UserNotes'] = GetField( "select us_notes from users where us_id = {$Order['or_us_id']}" );
	$data['AddressCheckedBy'] = GetField( "select us_first_name from users join audit on us_id = au_userid where au_key = {$Order['tr_id']} and au_table = 'transactions' and au_notes = 'Address Checked' order by au_id limit 1" );
	$data['tr_reference'] = $Order['tr_reference'];	
	$data['Order'] = $Order;
		   
	$details = unserialize($Order['or_details']);
	/*
	$shopSession = unserialize($Order['or_basket']);
	*/
	
	$accessCode = '';
	if (array_key_exists('AccessCode', $_REQUEST))
		$accessCode = $_REQUEST['AccessCode'];
	else if (array_key_exists('AccessCode', $_SESSION)) 
		$accessCode = $_SESSION['AccessCode'];
	$data['AccessCode'] = $accessCode;
	
	
	$shippingDetails = unserialize($Order['or_shipping_details']);
	$data['ShippingDetailsHTML'] = '';
	foreach($shippingDetails['ShippingDetails'] as $key => $aValue) {
		if ($key != 'first_name' and $key != 'last_name') {
			$data['ShippingDetailsHTML'] .= $aValue."<BR>";
		}
	}
	
	$data['PurchaserDetailsHTML'] = '';
	foreach($shippingDetails['PurchaserDetails'] as $key => $aValue) {
		if ($key != 'first_name' and $key != 'last_name') {
			$data['PurchaserDetailsHTML'] .= $aValue."<BR>";
		}
	}
	
	ss_paramKey($details, 'BasketHTML', '');
	$data['BasketHTML'] = $details['BasketHTML'];
	ss_paramKey($details, 'GiftMessage', '');		
	$data['GiftMessage'] = $details['GiftMessage'];
	ss_paramKey($details, 'PastedStuff', '');		
	$data['PastedStuff'] = $details['PastedStuff'];
	/*$data['ThankYouNote'] = $shopSetting['AST_SHOPSYSTEM_THANKYOUNOTE'];	
	
	
	$data['LogoHTML'] = $GLOBALS['cfg']['website_name'];
	if(file_exists(expandPath('Custom/ContentStore/Layouts/Images/Shop/invoiceLogo.gif'))) {
		$data['LogoHTML'] = "<img src='Custom/ContentStore/Layouts/Images/Shop/invoiceLogo.gif'>";
	}
	*/
	$shippingDetails = unserialize($Order['or_shipping_details']);
	$data['PurchaserDetailsHTML'] = '';
	foreach($shippingDetails['PurchaserDetails'] as $key=> $aValue) {
		if ($key != 'first_name' and $key != 'last_name') {
			$data['PurchaserDetailsHTML'] .= $aValue."<BR>";
		}
	}	
	
	$this->useTemplate('Order', $data);
	print("<!--\n");
	ss_DumpVar($data,'$data');
	ss_DumpVar($OrderDetails,'$orderDetails');
	print("\n-->");
?>
