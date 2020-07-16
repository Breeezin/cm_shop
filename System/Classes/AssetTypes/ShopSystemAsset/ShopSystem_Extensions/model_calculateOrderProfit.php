<?php 

	function ss_getOldExchangeRate( $source, $dest, $index )
	{
		// a:10542:{s:7:"USD_BTC";s:19:"0.00026389673717529";s:7:"BT
		/// str:unserialize(): Error at offset 65533 of 65535 bytes where:/var/www/chroot/acmerockets/System/Classes/AssetTypes/ShopSystemAsset/ShopSystem_Extensions/model_calculateOrderProfit.php:11
		$row = getRow( "select * from OldExchangeRates where OERID = $index" );
		if( $row )
		{
			if( array_key_exists('OERValues', $row )
				 && strlen( $row['OERValues'] ) )
			{
				ss_log_message( 'Needle "'.$source.'_'.$dest.'"' );
				if( $pos = strpos( $row['OERValues'], '"'.$source.'_'.$dest.'"' ) )
				{
					ss_log_message( "found at pos $pos" );
					$pos += strlen( '"'.$source.'_'.$dest.'"' ) + 2;
					$foo = substr( $row['OERValues'], $pos, 100 );
					ss_log_message( "chopped to $foo" );
					if( $pos2 = strpos( $foo, '"' ) )
					{
						ss_log_message( "found at pos2 $pos2" );
						return (float) substr( $foo, ++$pos2 );
					}
				}
				/* not robust, serialized array too big
				$rates = unserialize( $row['OERValues'] );
				if( array_key_exists( $source.'_'.$dest, $rates ) )
					return $rates[$source.'_'.$dest];
				*/
			}
		}
		return false;
	}

//	ss_log_message( "\n\nprofit for ".$this->ATTRIBUTES['or_id'] );
	// grab the order

	if (array_key_exists('or_id',$this->ATTRIBUTES)) {
		$q = "SELECT * FROM shopsystem_orders JOIN transactions on tr_id = or_tr_id LEFT JOIN payment_gateways on tr_bank = pg_id WHERE tr_completed = 1 and or_id = {$this->ATTRIBUTES['or_id']}";
	} else {
		if (array_key_exists('MinOrID',$this->ATTRIBUTES) && array_key_exists('MaxOrID',$this->ATTRIBUTES)) {
			$q = "SELECT * FROM shopsystem_orders JOIN transactions on tr_id = or_tr_id LEFT JOIN payment_gateways on tr_bank = pg_id  where tr_completed = 1 and or_id >= {$this->ATTRIBUTES['MinOrID']} and or_id <= {$this->ATTRIBUTES['MaxOrID']}";
			if( array_key_exists('Reship', $this->ATTRIBUTES) )
				$q .= " and or_reshipment IS NOT NULL";
		} else {
			$q = "SELECT * FROM shopsystem_orders JOIN transactions on tr_id = or_tr_id LEFT JOIN payment_gateways on tr_bank = pg_id  where tr_completed = 1";
			if( array_key_exists('Reship', $this->ATTRIBUTES) )
				$q .= " and or_reshipment IS NOT NULL";
		}
	}

	set_time_limit( 0 );

	$ProfitDescription = $q."<br />";

	$Q_Order = query( $q );

	$FixFreeBox = array_key_exists('FixFreeBox', $this->ATTRIBUTES);


	$shop = NULL;

	$isArchivedOrder = true;

//	flush();
	while ($Order = $Q_Order->fetchRow()) 
	{
		if( !$shop )
		{
			$shop = getRow(" SELECT as_serialized FROM assets
					WHERE as_id = {$Order['or_as_id']} ");
			$settings = unserialize($shop['as_serialized']);
			$generalDiscount = $settings['AST_SHOPSYSTEM_SUPPLIER_DISCOUNT'];
			$freeStockCode = $settings['AST_SHOPSYSTEM_FREE_PRODUCT_STOCK_CODE'];
			$freeBoxPriceLimit = $settings['AST_SHOPSYSTEM_FREE_PRODUCT_LIMIT'];
			if( strlen( $freeStockCode ) )
				$freeBox = GetRow( "select * from shopsystem_products join shopsystem_product_extended_options on pr_id = pro_pr_id where pro_stock_code = '$freeStockCode'" );
		}

		$ProfitDescription .= "<br/><b>Profit Calculations</b> for order/transaction ".$Order['or_tr_id']." or_id ".$Order['or_id']."<br/>";

		if( $Order['or_us_id'] == 19418 )
		{
			$ProfitDescription .= "Skipping Transfer order<br />";
			continue;
		}

		if( $Order['or_archive_year'] > 0 )
			$isArchivedOrder = true;
		else
			$isArchivedOrder = false;

		if (!strlen($generalDiscount))
			$generalDiscount = 0;	

		if( $generalDiscount > 0 )
			$ProfitDescription .= "AST_SHOPSYSTEM_SUPPLIER_DISCOUNT is ".$generalDiscount."<br/>";
		
		if( strlen( $freeStockCode ) > 0 )
			$ProfitDescription .= "Free box stock code is ".$freeStockCode."<br/>";

		$ProfitDescription .= "Currency Link is ".$Order['tr_currency_link']."<br/>";

		if( $Order['tr_currency_link'] > 0 )
		{
			$cl = getRow( "select * from countries where cn_id = {$Order['tr_currency_link']}" );
			$currency = array(  'CurrencyCode'	=>	$cl['cn_currency_code'],
								'Symbol'	=>	$cl['cn_currency_symbol'],
								'Appears'	=>	'before',
								);
		}
		else
		{
			$currency = array(  'CurrencyCode'	=>	'EUR',
								'Symbol'	=>	'&euro;',
								'Appears'	=>	'before',
								);
		}

		$OrderDetails = unserialize($Order['or_basket']);
		$saveOrderDetails = false;

		$realTotalCharged = 0;
		if( $Order['or_reshipment'] === null )
			$realTotalCharged = $Order['tr_order_total'];			// total before any discounts applied

		$ProfitDescription .= "Total Charged is ".$realTotalCharged."<br/>";

		$processor_cut = $realTotalCharged * $Order['pg_skim'] / 100.0 + $Order['pg_skim_fixed'];

		$ProfitDescription .= "Payment Gateway (".$Order['pg_name'].") gets ".$processor_cut."<br/>";
	
		$totalProductsCost = 0;
		$refundAmount = 0;
		$totalIncludedFreight = 0;

		// we only lose money on products that have been shipped obviously..
		// so check what products which have been shipped
		$should_have = 0;
		$freeindex = NULL;
		$credit_as_at_order = 0;
		$actual_discount = 0;
		$used_credit = 0;
//	 	$ProfitDescription .= print_r( $OrderDetails['Basket'], true );
//	 	$ProfitDescription .= "<br/>";
		if( array_key_exists( 'Discounts', $OrderDetails['Basket'] ) )
		{
			$actual_discount = 0;
			foreach( $OrderDetails['Basket']['Discounts'] as $i=>$v )
				if( $i == 'Account Credit' )
					$credit_as_at_order = -$v;
				else
					$actual_discount += $v;

			$ProfitDescription .= "total credits is ".$actual_discount."<br/>";
		}

		$realTotalCharged += $actual_discount;
		$ProfitDescription .= "After discount charged is ".$realTotalCharged."<br/>";
		$ProfitDescription .= "Credit as at order time is ".$credit_as_at_order."<br/>";

		if( $credit_as_at_order > $realTotalCharged )
			$used_credit = $realTotalCharged;
		else
			$used_credit = $credit_as_at_order;

		if( strlen( $freeStockCode ) )
		{
			$has = 0;

			$ProfitDescription .= "Free Box code is $freeStockCode<br />";

			foreach ($OrderDetails['Basket']['Products'] as $id => $entry) 
			{
				if( $entry['Product']['pro_stock_code'] == $freeStockCode )
				{
					$has += $entry['Qty'];
					$freeindex = $id;
				}

				// count how many free boxes they should get
				$addGift = getField( "select pr_add_gift from shopsystem_products where pr_id = ". $entry['Product']['pr_id'] );

				if ( ($addGift == 1) and ($entry['Product']['pro_stock_code'] != $freeStockCode) and ($entry['Product']['pr_ve_id'] == $freeBox['pr_ve_id']) )
				{
					if ($entry['Product']['Price'] > $freeBoxPriceLimit)
						$should_have += $entry['Qty'];
				}
			}

			if( $has != $should_have )
				$ProfitDescription .= "<br/>################### Order {$Order['or_tr_id']} has $has free boxes, should have $should_have <br/>";

			if( $should_have > $has )
			{
				$saveOrderDetails = true;

				if( $freeindex )
				{
					// increase qty
					$OrderDetails['Basket']['Products'][$freeindex]['Qty'] = $should_have;
				}
				else
				{
					// add entry

					$product = getRow("
						SELECT * FROM shopsystem_products, shopsystem_product_extended_options
						WHERE pr_id = pro_pr_id
							AND pro_stock_code LIKE '".escape($freeStockCode)."'
						");	
						
					$key = $product['pr_id'].'_'.$product['pro_id'];

					$product['PrExOpFreightCodeLink'] = null;
					$product['pro_special_price'] = null;
					$product['pro_price'] = 0;
					$product['Price'] = 0;

					$entry = array(
						'Key'	=>	$key,
						'Product'	=>	$product,
						'Qty'	=>	$should_have - $has,
					);

					$OrderDetails['Basket']['Products'][] = $entry;
				}
			}
		}

		$ProfitDescription .= "Shipping <br />";

		$vendorShipping = array();

		// first figure out how many packages each vendor sends
		// then how much each package would be to $destination

		foreach ($OrderDetails['Basket']['Products'] as $id => $basketEntry) 
		{

			ss_paramKey($OrderDetails['Basket']['Products'][$id],'Refund',array());
			ss_paramKey($OrderDetails['Basket']['Products'][$id],'Shipped',array());
			ss_paramKey($OrderDetails['Basket']['Products'][$id],'Availabilities',array());

			$product = $basketEntry['Product'];
			$prod = getRow( "select * from shopsystem_products where pr_id = ".$product['pr_id'] );		// refresh fields

			$shippingMethod = getField( "select ve_shipping_method from vendor where ve_id = ".$prod['pr_ve_id'] );
			$ProfitDescription .= "<b>Stock code {$basketEntry['Product']['pro_stock_code']}</b> shipped via $shippingMethod<br />";

			// get shipping status...
			$NoStock = getField( "select count(orsi_no_stock) from shopsystem_order_sheets_items where orsi_stock_code = '{$basketEntry['Product']['pro_stock_code']}' and orsi_or_id = {$Order['or_id']}" );

			$ProfitDescription .= "$NoStock Marked out of stock<br />";

			$old_exchange_rate_index = $Order['tr_exchange_rate_index'];
			$zone = GetField( "select cn_post_zone from countries where cn_id = ".((int)$Order['or_country'] ) );
			if( !$zone )
			{
				ss_log_message( "Calc order profit on order tr_id {$Order['tr_id']} missing freight zone" );
				$zone = 'RestOfWorld';
			}

			if( $product['pro_stock_code'] == $freeStockCode )
				$boxesToCharge = 0;
			else
				$boxesToCharge = $basketEntry['Qty'] - $NoStock;

			$boxesToShip = 0;

			// figure out how many boxes are shipped or potentially shipped
			for ($qty=0; $qty < $basketEntry['Qty']; $qty++)
			{
				if( !array_key_exists( 'Availabilities', $basketEntry )
					or !array_key_exists($qty,$basketEntry['Availabilities'])
					or ($basketEntry['Availabilities'][$qty] != 'outofstock' )) 
					$boxesToShip++;

				// refunds still are refunds if the box is deleted
				if( array_key_exists('Refund',$basketEntry) && array_key_exists( $qty,$basketEntry['Refund']) )
				{
					if (ListFirst($basketEntry['Refund'][$qty],'_') == 'Refunded')
					{
						$ProfitDescription .= "Refunded. Adding cost of "
								.ListLast($basketEntry['Refund'][$qty],'_')."<br/>";
						$refundAmount += ListLast($basketEntry['Refund'][$qty],'_');
					}
				}
				// Was this returned?
				if( array_key_exists( 'Availabilities', $basketEntry )
				 && array_key_exists($qty,$basketEntry['Availabilities'] )
				 && ( $basketEntry['Availabilities'][$qty] == 'deleted' ) )
				{
					$ProfitDescription .= "Product returned, ignoring box cost<br/>";
					if( $boxesToCharge > 0 )
						$boxesToCharge--;
				}
			}

			if( $prod['pr_is_service'] == 'true')		// one doesn't ship a service
				$boxesToShip = 0;



			if( !array_key_exists($shippingMethod, $vendorShipping) )
			{
				$vendorShipping[$shippingMethod] = getRow("select * from included_freight where if_shipping_method =  '$shippingMethod' and if_destination_zone = '$zone'" );
				$vendorShipping[$shippingMethod]['current_package_size'] = 0;
			}

			if( $vendorShipping[$shippingMethod]['if_max_package_size'] > 1 )
				$vendorShipping[$shippingMethod]['current_package_size'] += ($prod['pr0_883_f'] * $boxesToShip);
			else
				$vendorShipping[$shippingMethod]['current_package_size'] += $boxesToShip;

			$ProfitDescription .= "Shipping method $shippingMethod now has {$vendorShipping[$shippingMethod]['current_package_size']} out of {$vendorShipping[$shippingMethod]['if_max_package_size']} at cost each of {$vendorShipping[$shippingMethod]['if_cost']}<br />";

			$ProfitDescription .= "There are potentially $boxesToShip to ship $boxesToCharge to charge<br />";

			if( $isArchivedOrder )
			{
				// figure out the price of product way back when...

				$prodOption = getRow(" SELECT pro_supplier_price, pro_supplier_disount, pro_source_currency FROM prexop_history
					WHERE pro_pr_id = ".$product['pr_id']." and pro_recorded = DATE('".$Order['or_recorded']."') ");

				if( !$prodOption || !is_array( $prodOption ) )
					$prodOption = getRow(" SELECT pro_supplier_price, pro_supplier_disount, pro_source_currency FROM shopsystem_product_extended_options
						WHERE pro_pr_id = ".$product['pr_id'] );

				$ProfitDescription .= "Product ".$product['pr_id']." box number ".($id+1).", Customer Paid ".$currency['CurrencyCode'].$product['Price']."<br/>";
				$ProfitDescription .= "Payment currency is ".$currency['CurrencyCode'].", source currency is ".$prodOption['pro_source_currency'].", ";

				$ProfitDescription .= "Old Exchange rate was ";
				if( $old_exchange_rate_index && ( $old_exchange_rate_index > 0 ) )
					if( ( $oer = ss_getOldExchangeRate($prodOption['pro_source_currency'], $currency['CurrencyCode'], $old_exchange_rate_index ) ) == false )
					{
						$ProfitDescription .= "broken<br />";
						$oer = ss_getExchangeRate($prodOption['pro_source_currency'], $currency['CurrencyCode'] );
						$ProfitDescription .= "Current exchange rate is $oer<br />";
					}
					else
						$ProfitDescription .= "$oer<br />";

				/*
				if( $old_exchange_rate_index && ( $old_exchange_rate_index > 0 ) )
				{
					$oer = ss_getOldExchangeRate($prodOption['pro_source_currency'], $currency['CurrencyCode'], $old_exchange_rate_index );
					$ProfitDescription .= "Old Exchange rate was $oer<br />";
				}
				else
				{
					$oer = ss_getExchangeRate($prodOption['pro_source_currency'], $currency['CurrencyCode'] );
					$ProfitDescription .= "Current Exchange rate is $oer<br />";
				}
				*/

				if( $prodOption['pro_source_currency'] != $currency['CurrencyCode'] )
				{
		//					$ProfitDescription .= "Calculating exchange rate<br/>";
					$ProfitDescription .= "Supplier price was ".$prodOption['pro_source_currency']." ".$prodOption['pro_supplier_price']."<br/>";

					if( IsSet( $prodOption['pro_supplier_price'] ) )
						$prodOption['pro_supplier_price'] = ($prodOption['pro_supplier_price'] * $oer );
					if( IsSet( $prodOption['pro_supplier_disount'] ) )
						$prodOption['pro_supplier_disount'] = ($prodOption['pro_supplier_disount'] * $oer );
				}

				$discount = 0;
				$price = 0;
				if (strlen($prodOption['pro_supplier_price'])) {
					$price = $prodOption['pro_supplier_price'];
				}
				$ProfitDescription .= "Supplier price was ".$currency['CurrencyCode']." ".$price."<br/>";

				if (strlen($prodOption['pro_supplier_disount']))
				{
					$discount = ($prodOption['pro_supplier_disount']*$price/100);
					$ProfitDescription .= "Supplier discount was ".$discount."<br/>";
				}
				else
				{
					if( $prodOption['pro_source_currency'] == $currency['CurrencyCode'] )
					{
						$discount = ($generalDiscount*$price/100);
						if( $discount != 0.0 )
							$ProfitDescription .= "General discount is ".$discount."<br/>";
					}
				}

				$productCost = ($price-$discount);		
				$ProfitDescription .= "Final Supplier price was ".$productCost."<br/>";
				$totalProductsCost += $productCost * $boxesToCharge;
			}
			else
			{
				// current data
				// figure out the price of product
				$prodOption = getRow("
					SELECT pro_supplier_price, pro_supplier_disount, pro_source_currency FROM shopsystem_product_extended_options
					WHERE pro_pr_id = ".$product['pr_id']."
				");

				$ProfitDescription .= "Product ".$product['pr_id']." box number ".($id+1).", Customer Paid ".$currency['CurrencyCode'].$product['Price']."<br/>";
				$ProfitDescription .= "Payment currency is ".$currency['CurrencyCode'].", source currency is ".$prodOption['pro_source_currency'].", ";

				$ProfitDescription .= "Old Exchange rate was ";
				if( $old_exchange_rate_index && ( $old_exchange_rate_index > 0 ) )
					if( ( $oer = ss_getOldExchangeRate($prodOption['pro_source_currency'], $currency['CurrencyCode'], $old_exchange_rate_index ) ) == false )
					{
						$ProfitDescription .= "broken<br />";
						$oer = ss_getExchangeRate($prodOption['pro_source_currency'], $currency['CurrencyCode'] );
						$ProfitDescription .= "Current exchange rate is $oer<br />";
					}
					else
						$ProfitDescription .= "$oer<br />";

				/*
				if( $old_exchange_rate_index && ( $old_exchange_rate_index > 0 ) )
				{
					$oer = ss_getOldExchangeRate($prodOption['pro_source_currency'], $currency['CurrencyCode'], $old_exchange_rate_index );
					$ProfitDescription .= "Old Exchange rate is $oer<br />";
				}
				else
				{
					$oer = ss_getExchangeRate($prodOption['pro_source_currency'], $currency['CurrencyCode'] );
					$ProfitDescription .= "Current Exchange rate is $oer<br />";
				}
				*/

				if( $prodOption['pro_source_currency'] != $currency['CurrencyCode'] )
				{
//					$ProfitDescription .= "Calculating exchange rate<br/>";
					$ProfitDescription .= "Supplier price is ".$prodOption['pro_source_currency']." ".$prodOption['pro_supplier_price']."<br/>";

					if( IsSet( $prodOption['pro_supplier_price'] ) )
						$prodOption['pro_supplier_price'] = ($prodOption['pro_supplier_price'] * $oer );
					if( IsSet( $prodOption['pro_supplier_disount'] ) )
						$prodOption['pro_supplier_disount'] = ($prodOption['pro_supplier_disount'] * $oer );
				}

				$discount = 0;
				$price = 0;
				if (strlen($prodOption['pro_supplier_price'])) {
					$price = $prodOption['pro_supplier_price'];
				}
				$ProfitDescription .= "Supplier price is ".$currency['CurrencyCode']." ".$price."<br/>";

				if (strlen($prodOption['pro_supplier_disount']))
				{
					$discount = ($prodOption['pro_supplier_disount']*$price/100);
					$ProfitDescription .= "Supplier discount is ".$discount."<br/>";
				}
				else
				{
					if( $prodOption['pro_source_currency'] == $currency['CurrencyCode'] )
					{
						$discount = ($generalDiscount*$price/100);
						if( $discount != 0.0 )
							$ProfitDescription .= "General discount is ".$discount."<br/>";
					}
				}

				$productCost = ($price-$discount);		
				$ProfitDescription .= "Final Supplier price is ".$productCost."<br/>";
				$totalProductsCost += $productCost * $boxesToCharge;
			}

		}

		// figure out shipping here...
		foreach( $vendorShipping as $shippingMethod=>$entry )
		{
			$packages = (int)( $entry['current_package_size'] / $entry[ 'if_max_package_size' ] + 0.99999 );
			if( $packages == 0 )
				$packages = 1;
			$ProfitDescription .= "Shipping Method $shippingMethod total is {$entry['current_package_size']} maximum is {$entry[ 'if_max_package_size' ]} packages is $packages <br/>";

			// include freight in the price :)
			$includedFreightUSD = $entry['if_cost'];
			$exchangeRateUSD = ss_getExchangeRate('USD', $currency['CurrencyCode'] );
			$includedFreight = $includedFreightUSD * $exchangeRateUSD;

			$ProfitDescription .= "Included freight on all products in this zone is USD $includedFreightUSD -> {$currency['CurrencyCode']} $includedFreight <br/>";

			$totalIncludedFreight += $includedFreight * $packages;
		}

		$ProfitDescription .= "Total Included Freight is {$currency['CurrencyCode']} $totalIncludedFreight <br/>";
		$extraFreight = $Order['tr_excl_shipping'];
		$ProfitDescription .= "Total Extra Freight is {$currency['CurrencyCode']} $extraFreight<br/>";

		$real_profit = $realTotalCharged - $processor_cut - $totalIncludedFreight - $extraFreight - $totalProductsCost - $refundAmount;
//		ss_log_message( "Total freight = ".$totalFreightCost );
//		ss_log_message( "Total cost = ".$totalProductsCost );
		
		$ProfitDescription .= "<br /><br /><strong>Summary</strong><br />";
		if ($Order['or_standby'] !== null or $Order['or_cancelled'] !== null or $Order['or_card_denied'] !== null) 
		{
			$ProfitDescription .= "Potential Charge:".$currency['CurrencyCode']." $realTotalCharged<br/>"
									." - Processor:".$currency['CurrencyCode']." $processor_cut <br/>"
									." - Included Freight:".$currency['CurrencyCode']." $totalIncludedFreight <br/>"
									." - Extra Freight:".$currency['CurrencyCode']." $extraFreight <br/>"
									." - SupplierCost:".$currency['CurrencyCode']." $totalProductsCost<br/>"
									." - Refund:".$currency['CurrencyCode']." $refundAmount<br/>"
									;
			$ProfitDescription .= "Potential Profit = ".$currency['CurrencyCode']." $real_profit<br/>";
			$ProfitDescription .= "Profit = 0 as order cancelled, on standby or card denied<br/>";
			$real_profit = 0;	
		}
		else
		{
			$ProfitDescription .= "Actual Charge:".$currency['CurrencyCode']." ".$realTotalCharged."<br/>"
									." - Processor:".$currency['CurrencyCode']." $processor_cut <br/>"
									." - included_freight:".$currency['CurrencyCode']." $totalIncludedFreight <br/>"
									." - Extra Freight:".$currency['CurrencyCode']." $extraFreight <br/>"
									." - SupplierCost:".$currency['CurrencyCode']." $totalProductsCost<br/>"
									." - Refund:".$currency['CurrencyCode']." $refundAmount<br/>"
									;
			$ProfitDescription .= " = Profit = ".$currency['CurrencyCode']." $real_profit<br/>";
		}


		if( $saveOrderDetails && $FixFreeBox )
		{
			// needs saving back into order.
			$OrderDetailsSerialized = serialize($OrderDetails);
			
			$ProfitDescription .= "<br/>Fixing order<br/>";
			// Update the order
			$Q_UpdateOrder = query("
				UPDATE shopsystem_orders
				SET or_basket = '".escape($OrderDetailsSerialized)."',
				or_profit = $real_profit
			WHERE or_id = {$Order['or_id']}
			");
		}
		else
			$Q_UpdateOrder = query("
				UPDATE shopsystem_orders 
				SET or_profit = $real_profit
				WHERE or_id = {$Order['or_id']}
			");

		// save into ordered_products
		// get baseline
		$ordered_products = array();
		if( $q = query( "select * from ordered_products  where op_or_id = {$Order['or_id']}" ) )
			while( $r = $q->fetchRow( ) )
				$ordered_products[] = $r;

		$op_supplier_ttl = 0;
		$op_ttl = 0;
		$op_cc = '';
		$op_cnt = 0;
		foreach( $ordered_products as $op )
		{
			$op_cc = $op['op_currency_code'];
			$op_supplier_ttl += $op['op_quantity']*$op['op_supplier_price'];
			$op_ttl += $op['op_price_paid'] * $op['op_quantity'];
			$op_cnt += $op['op_quantity'];
		}

		// profit needs to be pro-rata'ed across all products in order according to what?
		// product cost?
		// Margin is no good, it's all over the place including zero (free products), but clearly profit can be attributed to those free products. 
		// Perhaps normal margin? seems synthetic.
		// no, product cost it will be.

		ss_log_message( "ordered_products total = $op_cc $op_ttl supplier ttl = $op_supplier_ttl, count = $op_cnt" );

		// $used_credit

		for( $i = 0; $i < count( $ordered_products ); $i++ )
		{
			if( $op_ttl > 0 )
				$cu = $ordered_products[$i]['op_credit_used'] = $used_credit * $ordered_products[$i]['op_price_paid'] / $op_ttl;
			else
				$cu = $ordered_products[$i]['op_credit_used'] = $used_credit * $op['op_quantity'] / $op_cnt;

			if( $op_supplier_ttl > 0 )
				$pr = $ordered_products[$i]['op_profit'] = $real_profit * $ordered_products[$i]['op_supplier_price'] / $op_supplier_ttl;
			else
				$pr = $ordered_products[$i]['op_profit'] = $real_profit *  $op['op_quantity'] / $op_cnt;

			query( "update ordered_products set op_credit_used = $cu, op_profit = $pr where op_id = {$ordered_products[$i]['op_id']}" );
		}

		ss_log_message_r(  'Log:'.__FILE__.':'.__LINE__, $ordered_products );

		if( ( $real_profit == 0 ) and ($Order['or_cancelled'] !== null or $Order['or_card_denied'] !== null) )
		{
			$Q_UpdateOrder = query("
				UPDATE shopsystem_orders 
				SET or_summarised = 1
				WHERE or_id = {$Order['or_id']}
			");
		}

		$Q_UpdateTransaction = query("
			UPDATE transactions
			SET tr_profit = $real_profit,
				tr_used_credit = $used_credit,
				tr_incl_shipping = $totalIncludedFreight,
				tr_box_cost = $totalProductsCost,
				tr_processor_cost = $processor_cut
			WHERE tr_id = {$Order['or_tr_id']}
		");

	}

	print $ProfitDescription;
		
?>
