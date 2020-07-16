<?php 

	echo "Auditing stock levels<br />";

	$email_body = '';

	query( "create temporary table in_system as select oi_stock_code, count(*) as Qty from shopsystem_order_items where oi_eos_id IS NULL group by oi_stock_code" );
	query( "create temporary table on_hold as select or_id from shopsystem_orders where or_archive_year IS NULL and or_standby IS NOT NULL " );
	// remove those that have been through the packing system
	query( "delete from on_hold where or_id in (select oi_or_id from shopsystem_order_items)" );

	query( "create temporary table on_standby as select op_stock_code as StockCode, sum(op_quantity) as SQty from ordered_products join on_hold on op_or_id = or_id group by op_stock_code" );

	$Q_products = query( "select * from shopsystem_products join shopsystem_product_extended_options on pr_id = pro_pr_id join vendor on pr_ve_id = ve_id left join in_system on oi_stock_code = pro_stock_code left join on_standby on pro_stock_code = StockCode where pr_ve_id in (2,4) and pr_deleted IS NULL and pro_deleted is NULL and pr_combo IS NULL order by pr_ve_id, pro_stock_code" );
	while( $row = $Q_products->fetchRow() )
	{
		if( !strlen( $row['pro_stock_available'] ) )
			continue;

		if( $row['pro_stock_available'] <= 0 )
		{
			// was it above zero last time?
			$last_row = getRow( "select * from audit where au_table = 'Products' and au_key = {$row['pr_id']} and au_operation = 'view' order by au_id desc limit 1" );
			if( !strncmp( $last_row['au_notes'], 'stock is ', 9 ) )
			{
				$last_amt = substr( $last_row['au_notes'], 9 );
				if( $last_amt > 0 )
				{
					if( !strlen( $row['SQty'] ) )
						$row['SQty'] = 0;
					if( !strlen( $row['Qty'] ) )
						$row['Qty'] = 0;

					$email_body .= "\n".$row['ve_name']." Stock of ".$row['pr_id'].' / '.$row['pro_stock_code'].' / '.$row['pr_name']. '. Stock last was:'.$last_amt.', now available:'.$row['pro_stock_available'].', waiting to be packed:'.$row['Qty'].', reserved:'.$row['SQty'].', Showing on Shelf:'.($row['Qty'] + $row['SQty'] + $row['pro_stock_available']);
				}
			}
			ss_audit( 'view', 'Products', $row['pr_id'], 'stock is '.$row['pro_stock_available'] );
		}

//		$email_body .= "\n\nDO NOT ALTER STOCK LEVELS WITHOUT CONSULTING 'Showing on Shelf' above.";
		if( $row['pro_stock_available'] > 0 )
			ss_audit( 'view', 'Products', $row['pr_id'], 'stock is '.$row['pro_stock_available'] );
	}
	
	if( strlen( $email_body ) )
		$temp = new Request("Email.Send",array(
						'from'		=>	'webserver@acmerockets.com',
						'to'		=>	array('acme@admin.com', 'rolfbjork@gmail.com'),
						//'to'		=>	array('acme@admin.com'),
						'subject'	=>	'Stock hit zero',
						'text'		=>	$email_body
					));

	$cancelOrder = array();

	echo "looking for credit/debit on abandoned orders<br/>";
	ss_log_message( "looking for credit/debit on abandoned orders" );

	// 2 parts to the query, those who are trying to avoid debits/credits on an abandoned order
	// those who complete an order and indicate they are going to pay, then leave it

	$Q_abandoned = query( "select or_id, or_tr_id, or_basket, or_us_id, tr_currency_code from shopsystem_orders join transactions on tr_id = or_tr_id where or_archive_year IS NULL and tr_completed < 1 and or_cancelled IS NULL and or_standby IS NULL and or_recorded  < now() - interval 3 day"
		." UNION "
		."select or_id, or_tr_id, or_basket, or_us_id, tr_currency_code from shopsystem_orders join transactions on tr_id = or_tr_id where or_archive_year IS NULL and or_cancelled IS NULL and or_standby IS NOT NULL and ((or_standby < now() - interval 4 day and or_follow_up_status IS NULL) OR (or_standby < now() - interval 21 day and or_follow_up_status IS NOT NULL))" );
	while( $row = $Q_abandoned->fetchRow() )
	{
		echo "Cancelling abandonded order {$row['or_tr_id']}<br />";
		ss_log_message( "Cancelling abandonded order {$row['or_tr_id']}" );

		$OrderDetails = unserialize($row['or_basket']);

		// check ['Basket']['Discounts']['Account Credit']
		if( is_array( $OrderDetails )
		 && array_key_exists( 'Basket', $OrderDetails )
		 && array_key_exists( 'Discounts', $OrderDetails['Basket'] )
		 && array_key_exists( 'Account Credit', $OrderDetails['Basket']['Discounts'] )
		 && $OrderDetails['Basket']['Discounts']['Account Credit'] != 0 )
		{
			echo "Order {$row['or_tr_id']} user {$row['or_us_id']} has abandoned credit/debit of {$row['tr_currency_code']} {$OrderDetails['Basket']['Discounts']['Account Credit']}<br />";
			ss_log_message( "Order {$row['or_tr_id']} user {$row['or_us_id']} has abandoned credit/debit of {$row['tr_currency_code']} {$OrderDetails['Basket']['Discounts']['Account Credit']}" );
			$theUser = getRow( "select us_account_credit, us_credit_from_gateway_option from users where us_id = {$row['or_us_id']}" );
			if( $foo = getCurrencyEntry( $theUser['us_credit_from_gateway_option'] ) )
			{
				if( ( $row['tr_currency_code'] == $foo['po_currency'] ) || ( $theUser['us_account_credit'] == 0 ) )
				{
					$gateway_option = $theUser['us_credit_from_gateway_option'];
					if( $row['tr_currency_code'] != $foo['po_currency'] )
						$gateway_option = GetField( "select po_id from payment_gateway_options where po_currency = '{$row['tr_currency_code']}' and po_active = 1 and po_site = ".getSiteID( )." order by po_card_type limit 1" );

					$credit_used = -$OrderDetails['Basket']['Discounts']['Account Credit'];
					if( $OrderDetails['Basket']['Total'] < 0 )				// didn't use all credit on this order
						$credit_used = -$OrderDetails['Basket']['Total'] +$OrderDetails['Basket']['Discounts']['Account Credit'];
					query( "update users set us_account_credit = us_account_credit+$credit_used,
									us_credit_from_gateway_option = $gateway_option
								where us_id = {$row['or_us_id']}" );
					ss_audit( 'update', 'users', $row['or_us_id'], 
						"Now adding account credit of {$row['tr_currency_code']} $credit_used for cancelling order {$row['or_tr_id']}" );
				}
				else
				{
					// eh?  is this going to happen?
				}
			}
			else
			{
				$gateway_option = GetField( "select po_id from payment_gateway_options where po_currency = '{$foo['po_currency']}' and po_active = 1 and po_site = ".getSiteID( )." order by po_card_type limit 1" );
				$credit_used = -$OrderDetails['Basket']['Discounts']['Account Credit'];
				if( $OrderDetails['Basket']['Total'] < 0 )				// didn't use all credit on this order
					$credit_used = -$OrderDetails['Basket']['Total']+$OrderDetails['Basket']['Discounts']['Account Credit'];
				query( "update users set us_account_credit = us_account_credit+$credit_used,
								us_credit_from_gateway_option = $gateway_option
							where us_id = {$row['or_us_id']}" );
				ss_audit( 'update', 'users', $row['or_us_id'], 
					"Now adding account credit of {$row['tr_currency_code']} $credit_used for cancelling order {$row['or_tr_id']}" );
			}
		}

		if( $row['tr_completed'] )
		{
			echo "Cancelling order {$row['or_tr_id']}<br />";
			ss_log_message( "Cancelling order {$row['or_tr_id']}" );
			ss_audit( 'update', 'Orders', $row['or_tr_id'], "Automatically cancelling this order" );
			// add stock back
			$OrderDetails = unserialize($row['or_basket']);
			foreach ($OrderDetails['Basket']['Products'] as $id => $entry) 
			{
				// add back $entry['Qty'] of $OrderDetails['Basket']['Products'][$id]['Product']['pr_id']
				$Q_stockback = query("
						UPDATE shopsystem_product_extended_options 
						set pro_stock_available = pro_stock_available + ".$entry['Qty']." 
						where pro_pr_id = ".$OrderDetails['Basket']['Products'][$id]['Product']['pr_id']);
				echo "Adding ".$entry['Qty']." more of product id ".$OrderDetails['Basket']['Products'][$id]['Product']['pr_id']." back into stock<br/>";

				ss_audit( 'update', 'Products', $OrderDetails['Basket']['Products'][$id]['Product']['pr_id'], "Adding ".$entry['Qty']." more of product id ".$OrderDetails['Basket']['Products'][$id]['Product']['pr_id']." back into stock" );
			}
		}

		// mark as cancelled
		$cancelOrder[] = $row['or_id'];
	}


	echo "Cancelling orders marked as Card Denied older than 3 days<br/>";
	ss_log_message( "Cancelling orders marked as Card Denied older than 3 days" );

	$Q_cancelled = query( "select or_id, or_tr_id, or_basket from shopsystem_orders where or_archive_year IS NULL and or_cancelled IS NULL and or_card_denied IS NOT NULL and or_card_denied < now() - interval 3 day" );
	while( $row = $Q_cancelled->fetchRow() )
	{
		echo "Cancelling order {$row['or_tr_id']}<br />";
		ss_log_message( "Cancelling order {$row['or_tr_id']}" );
		// add stock back
		$OrderDetails = unserialize($row['or_basket']);
		foreach ($OrderDetails['Basket']['Products'] as $id => $entry) 
		{
			// add back $entry['Qty'] of $OrderDetails['Basket']['Products'][$id]['Product']['pr_id']
			$Q_stockback = query("
					UPDATE shopsystem_product_extended_options 
					set pro_stock_available = pro_stock_available + ".$entry['Qty']." 
					where pro_pr_id = ".$OrderDetails['Basket']['Products'][$id]['Product']['pr_id']);
			echo "Adding ".$entry['Qty']." more of product id ".$OrderDetails['Basket']['Products'][$id]['Product']['pr_id']." back into stock<br/>";
			ss_log_message( "Adding ".$entry['Qty']." more of product id ".$OrderDetails['Basket']['Products'][$id]['Product']['pr_id']." back into stock" );

			ss_audit( 'update', 'Products', $OrderDetails['Basket']['Products'][$id]['Product']['pr_id'], 'increasing available stock by '.$entry['Qty'] );

		}

		// mark as cancelled
		$cancelOrder[] = $row['or_id'];
	}


	// now do standby

/* now done further up
	echo "Cancelling orders marked as Standby older than 4 days<br/>";
	ss_log_message( "Cancelling orders marked as Standby older than 4 days" );

	$Q_cancelled = query( "select or_id, or_tr_id, or_basket from shopsystem_orders where or_archive_year IS NULL and or_cancelled IS NULL and or_standby IS NOT NULL and ((or_standby < now() - interval 4 day and or_follow_up_status IS NULL) OR (or_standby < now() - interval 21 day and or_follow_up_status IS NOT NULL))" );
	while( $row = $Q_cancelled->fetchRow() )
	{
		echo "Cancelling order {$row['or_tr_id']}<br />";
		ss_log_message( "Cancelling order {$row['or_tr_id']}" );
		ss_audit( 'update', 'Orders', $row['or_tr_id'], "Automatically cancelling this order" );
		// add stock back
		$OrderDetails = unserialize($row['or_basket']);
		foreach ($OrderDetails['Basket']['Products'] as $id => $entry) 
		{
			// add back $entry['Qty'] of $OrderDetails['Basket']['Products'][$id]['Product']['pr_id']
			$Q_stockback = query("
					UPDATE shopsystem_product_extended_options 
					set pro_stock_available = pro_stock_available + ".$entry['Qty']." 
					where pro_pr_id = ".$OrderDetails['Basket']['Products'][$id]['Product']['pr_id']);
			echo "Adding ".$entry['Qty']." more of product id ".$OrderDetails['Basket']['Products'][$id]['Product']['pr_id']." back into stock<br/>";

			ss_audit( 'update', 'Products', $OrderDetails['Basket']['Products'][$id]['Product']['pr_id'], "Adding ".$entry['Qty']." more of product id ".$OrderDetails['Basket']['Products'][$id]['Product']['pr_id']." back into stock" );
		}

		// mark as cancelled
		$cancelOrder[] = $row['or_id'];
	}
*/

	foreach( $cancelOrder as $or_id )
	{
		query( "update shopsystem_orders set or_cancelled = now() where or_id = $or_id");
		ss_log_message( "update shopsystem_orders set or_cancelled = now() where or_id = $or_id");
	}

?>
