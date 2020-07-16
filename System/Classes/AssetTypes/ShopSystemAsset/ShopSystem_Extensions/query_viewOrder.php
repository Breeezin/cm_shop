<?php 

	requireOnceClass('Field');

	$this->param('or_id');
	$this->param('tr_id');
	$this->param('as_id');

	$this->display->layout = 'Administration';
	$this->display->title = $this->ATTRIBUTES['BreadCrumbs'];
	
	ss_audit( 'view', 'Orders', $this->ATTRIBUTES['or_id'], serialize(print_r( $this->ATTRIBUTES, true )) );

	$Order = getRow("SELECT * FROM shopsystem_orders, transactions left join payment_gateways on tr_bank = pg_id WHERE or_id = {$this->ATTRIBUTES['or_id']} AND tr_id = or_tr_id");
	$Shop = getRow("SELECT * FROM assets WHERE as_id = {$this->ATTRIBUTES['as_id']}");
	
	$shopSetting = unserialize($Shop['as_serialized']);
		
	if( strlen( $Order['or_basket'] ) == 0 )
		ss_DumpVarDie( $Order );

	$OrderDetails = unserialize($Order['or_basket']);

	$Q_MarkNotNew = query("
		UPDATE shopsystem_orders
		SET or_not_new = 1
		WHERE or_id = ".safe($this->ATTRIBUTES['or_id'])."
		and or_not_new IS NULL
	");
	
	$Q_OrderNotes = query("
		SELECT * FROM shopsystem_order_notes
		WHERE orn_or_id = ".safe($this->ATTRIBUTES['or_id'])."
		ORDER BY orn_timestamp 
	");


	/*
	$blackListcheck = new Request('shopsystem_blacklist.CheckClient', array(
			'or_id'	=>	$this->ATTRIBUTES['or_id'],
	));
	// not enough info return  in a request
	*/
	
	$Q_Order = getRow("
		SELECT us_bl_id, or_shipping_details,or_purchaser_email FROM shopsystem_orders join users on us_id = or_us_id
		WHERE or_id = {$this->ATTRIBUTES['or_id']}
	");

	$purchaserEmail = escape($Q_Order['or_purchaser_email']);
	$tr_id = (int) $this->ATTRIBUTES['tr_id'];

	$OrderListNumber = '';
	$QbatchNumber = query( "select orsi_ors_id, ors_date, orsi_pr_name from shopsystem_order_sheets_items join shopsystem_order_sheets on ors_id = orsi_ors_id where orsi_or_id = ".safe($this->ATTRIBUTES['or_id']) );
	while( $brow = $QbatchNumber->fetchRow( ) )
	{
	// http://acmerockets.local/index.php?act=shopsystem_order_sheets%2EViewPacking&ors_id=1638&BreadCrumbs=Administration%20%3A%20%3CA%20HREF%3D%22http%3A%2F%2Facmeexpress.local%2Findex.php%3FBackStructure%3D94%22%3E1638%2C26%20Feb%202010%2C%2C134320%2C134333%2C1362.31%2C%3C%2FA%3E&BackURL=http%3A%2F%2Facmeexpress.local%2Findex.php%3FBackStructure%3D94
	//http://acmerockets.local/index.php?act=shopsystem_order_sheets%2EViewPacking&ors_id=1638&BackURL=/
		$OrderListNumber .= "<a href='index.php?act=shopsystem_order_sheets%2EViewPacking&ors_id={$brow['orsi_ors_id']}&BackURL=/index.php?act=ShopSystem.ViewOrder%26OrID=".safe($this->ATTRIBUTES['or_id'])."%26TrID=".safe($this->ATTRIBUTES['tr_id'])."%26AssetID=".safe($this->ATTRIBUTES['as_id'])."'>".$brow['orsi_ors_id']."</a>(".$brow['orsi_pr_name'].'-'.$brow['ors_date']."),";
	}

	$AwaitingList = '';
	$Qwait = query( "select * from shopsystem_order_items where oi_eos_id IS NULL and oi_or_id = ".safe($this->ATTRIBUTES['or_id']) );
	while( $wrow = $Qwait->fetchRow( ) )
	{
		$AwaitingList .= $wrow['oi_name']." ";
	}

	$blackListedClient = '';
	$match_desc = false;
	$bl_req = new Request('shopsystem_blacklist.CheckOrder', array( 'tr_id' => $tr_id ) );
	$bl_matches = $bl_req->value;
	$bl_ids = array();
	if( count( $bl_matches ) )
	{
		foreach( $bl_matches as $match )
			if( $match['score'] == 100 )
			{
				$match_desc = 'Exact';
				$blackListedClient .= "<SPAN STYLE=\"color:FF0000\"><a href='javascript:showBlackList({$match['bl_id']});'>Exact Match {$match['note']}</a>&nbsp;";
				$blackListedClient .= "<input type='Button' name='BlackList' value='Remove this Black List Entry' onClick='removeBlackListEntry({$match['bl_id']})' /></span>";
				break;
			}
			else
			{
				$match_desc = 'Possible';
				if( !in_array( $match['bl_id'], $bl_ids ) )
				{
					$blackListedClient .= "<SPAN STYLE=\"color:FF0000\"><a href='javascript:showBlackList({$match['bl_id']});'>Score {$match['score']} Blacklist {$match['note']}</a></span>&nbsp;";
					$bl_ids[] = $match['bl_id'];
				}
			}
	}

	$bl_idstring = implode( ',', $bl_ids );

	if( $match_desc == 'Exact' )
	{
		$blackListedClient .= "<input type='Button' name='BlackList' value='Remove this user from Black List' onClick='removeBlackListByUser()'>";
		$blackListedClient .= "<input type='Button' name='BlackList' value='Add another Black List Entry' onClick='addBlackList()'>";
	}
	else
	{
		if( $Q_Order['us_bl_id'] == -1 )
			$blackListedClient .= "<div>User is whitelisted, blacklist will not apply to new orders</div>";
		else
			$blackListedClient .= "<input type='Button' name='WhiteList' value='Add this user to White List' onClick='addWhiteList()'>";
		$blackListedClient .= "<input type='Button' name='BlackList' value='Add this user to Black List' onClick='addBlackList()'>";
	}

	$chargeListHTML = $Order['pg_name'].'&nbsp';
	if( strlen( $Order['tr_payment_details_szln'] ) )
		if ($Order['or_charge_list'] == null)
			$chargeListHTML .= "<input type='Button' name='ChargeList' value='Add To Charge List' onClick='addToChargeList()'>";
		else
			$chargeListHTML .= "<input type='Button' name='ChargeList' value='Remove From Charge List' onClick='removeFromChargeList()'>";

	// Make a date object for date shipped
	$or_shipped = new DateField(array(
		'name'			=>	'or_shipped',
		'displayName'	=>	'Shipping Date',
		'note'			=>	null,			
		'required'		=>	false,
		'class'			=>	'formborder',
		'verify'		=>	false,
		'unique'		=>	false,
		'defaultValue'	=>	'Now',
		'showCalendar'	=> 	false,
		'size'	=>	'8',	'maxLength'	=>	'10',
		'rows'	=>	'6',	'cols'		=>	'40',			
	));	

	if( count( $_POST ) > 0 && array_key_exists( 'or_authorisation_number', $_POST ) )
	{
		if( strlen( $_POST['or_authorisation_number'] ) )
		{
			$Q_Update = query("
				UPDATE shopsystem_orders
				SET or_authorisation_number = '".safe( $_POST['or_authorisation_number'] )."'
				WHERE or_id = {$this->ATTRIBUTES['or_id']}
			");	
			$Order = getRow("SELECT * FROM shopsystem_orders, transactions WHERE or_id = {$this->ATTRIBUTES['or_id']} AND tr_id = or_tr_id");
		}
	}

	if( count( $_POST ) > 0 && array_key_exists( 'or_follow_up_status', $_POST ) )
	{
		if( strlen( $_POST['or_follow_up_status'] ) )
			$Q_Update = query("
				UPDATE shopsystem_orders
				SET or_follow_up_status = '".safe( $_POST['or_follow_up_status'] )."'
				WHERE or_id = {$this->ATTRIBUTES['or_id']}
			");	
		else
			$Q_Update = query("
				UPDATE shopsystem_orders
				SET or_follow_up_status = NULL
				WHERE or_id = {$this->ATTRIBUTES['or_id']}
			");	

		$Order = getRow("SELECT * FROM shopsystem_orders, transactions WHERE or_id = {$this->ATTRIBUTES['or_id']} AND tr_id = or_tr_id");
	}

	// update or_paid_not_shipped too, Rex 2006-10-10
	if (array_key_exists('or_shipped',$this->ATTRIBUTES)) {
		$or_shipped->value = $this->ATTRIBUTES['or_shipped'];
		$or_shipped->processFormInputValues(null);
		$errors = $or_shipped->validate();
		if ($errors === null) {
			$Q_Update = query("
				UPDATE shopsystem_orders
				SET or_shipped = ".$or_shipped->valueSQL().",
				or_paid_not_shipped = NULL
				WHERE or_id = {$this->ATTRIBUTES['or_id']}
			");	
		}
	} else {
		$or_shipped->value = $Order['or_shipped'];
		$or_shipped->processDatabaseInputValues(null);
	}
	
?>
