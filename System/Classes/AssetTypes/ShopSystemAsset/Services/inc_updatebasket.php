<?php

	function calculatePrices(&$this0, $gateway = NULL ) 
	{
		global $thisAsset, $cfg;

		$logging = false;

		// do they get any free stuff?

		$promos = array();
		$promQ = Query( "select free_giveaways.*, pr_ve_id from free_giveaways join shopsystem_products on pr_id = fg_pr_id" );
		while( $rw = $promQ->fetchRow() )
		{
			$rw['Total'] = 0;
			$promos[] = $rw;
		}
		ss_log_message_r(  'Log:'.__FILE__.':'.__LINE__, $promos );

		$currency = $thisAsset->getDisplayCurrency();

		if( $logging )
			ss_log_message( "as part of update basket, recalculating prices in basket" );

		$result = array();

		$local_tax = $_SESSION['ForceCountry'][ 'cn_tax_x100' ];

		$site_discount = 0;
		if( array_key_exists( $cfg['multiSites'][$cfg['currentServer']], $cfg['multiSiteDiscount'] ) )
			$site_discount = $cfg['multiSiteDiscount'][$cfg['multiSites'][$cfg['currentServer']]];

		$loyaltyPointsDiscount = -1;
		$foundLoyaltyPointsDiscount = false;
		$canUsePoints = false;

		$total = 0;
		$totalTax = array(
			'Code'	=>	'',
			'Amount'	=>	0,
		);

		// Remove deleted products and calculate tax
		$totalUnits = 0;

		$includedFreight = 0;
		$total_code_discount = 0;

		for	($index=0;$index<count($_SESSION['Shop']['Basket']['Products']);$index++)
        {
			$entry = $_SESSION['Shop']['Basket']['Products'][$index];

			if( $logging )
				ss_log_message_r(  'Log:'.__FILE__.':'.__LINE__, $entry );

			if ($entry['Qty'] > 0)
            {
				// Figure out the price to charge
				if( array_key_exists( 'Product', $entry )
				 && array_key_exists( 'pro_price', $entry['Product'] ) )
				{
					$price = $entry['Product']['pro_price'];

					$dest3Code = $_SESSION['ForceCountry']['cn_three_code'];
					if( strlen( $entry['Product']['pro_country_price_override'] ) )
					{
						ss_log_message( "parsing {$entry['Product']['pro_country_price_override']}" );
						if( ( $pos = strpos( $entry['Product']['pro_country_price_override'], $dest3Code.'=' ) ) !== false )
						{
							$ps = substr( $entry['Product']['pro_country_price_override'], $pos+4 );
							ss_log_message( "found at position $pos to extract $ps" );
							$price = (float) $ps;
						}
						else
							ss_log_message( "didn't find $dest3Code=" );
					}

					if( array_key_exists( 'pr_specials_sales_zone', $entry['Product'] ) 
					 && strlen( $entry['Product']['pr_specials_sales_zone'] ) )
					{
						// which zones is the current country in?
						$zones = explode(',', $_SESSION['ForceCountry'][ 'cn_sales_zones' ] );
//						ss_log_message( "Specials Zone is {$entry['Product']['pr_specials_sales_zone']}" );
//						ss_log_message_r(  'Log:'.__FILE__.':'.__LINE__, $zones );
						if( !in_array( $entry['Product']['pr_specials_sales_zone'], $zones ) )
						{
							$entry['Product']['pro_special_price'] = $entry['Product']['pro_price'];
							$price = $entry['Product']['pro_price'];
						}
					}
					else
						if ($entry['Product']['pro_special_price'] !== null)
							$price = $entry['Product']['pro_special_price'];

					if( array_key_exists( 'pr_restrict_special_to_gateway', $entry['Product'] ) 
					 && strlen( $entry['Product']['pr_restrict_special_to_gateway'] ) )
					{
						if( $gateway 
						  && ($entry['Product']['pr_restrict_special_to_gateway'] == $gateway )
						  && ($entry['Product']['pro_special_price'] < $entry['Product']['pro_price']) )
							$price = $entry['Product']['pro_special_price'];
						else
							ss_log_message( "NOT apply special price, wrong gateway ($gateway vs {$entry['Product']['pr_restrict_special_to_gateway']})" );
					}
					else
						if ($entry['Product']['pro_special_price'] !== null)
							$price = $entry['Product']['pro_special_price'];

					if( array_key_exists( 'FixPrice', $entry['Product'] ) && ss_isAdmin() )
					{
						$price = $entry['Product']['FixPrice'];

						if( $logging )
							ss_log_message_r(  'Log:'.__FILE__.':'.__LINE__, "admin setting price to be $price" );
					}
					else
					{
						if( $logging )
							ss_log_message( "pre-discount $price" );

						if (ss_optionExists("Shop Discount Codes"))
						{
							if (is_array($_SESSION['Shop']['DiscountCode']))
							{
								if( $logging )
									ss_log_message( "Discount code {$_SESSION['Shop']['DiscountCode']['di_code']} in session" );
								/*
								Array
								(
									[di_id] => 1
									[di_code] => XYZ123
									[di_description] => Test discount code
									[di_asset_link] => 514
									[di_active] => true
									[di_amount] => 1.5
									[di_type] => euro
									[di_discount_group] => 
									[di_starting] => 2011-04-01 00:00:00
									[di_ending] => 2011-05-01 00:00:00
									[di_left] => 
									[di_minimum_stock] => 10
								)
								*/
								// $price = $entry['Product']['pro_price'];		// Does NOT apply to things on special.  // oh yes it fuking does.
								$applicable = true;
								$now = date( 'Y-m-d H:i:s' );

								if( $_SESSION['Shop']['DiscountCode']['di_active'] == 'false' )		// this discount applies to products
								{
									if( $logging )
										ss_log_message( "discount disabled" );
									$applicable = false;
								}
								else
									if( strcmp( $now, $_SESSION['Shop']['DiscountCode']['di_starting'] ) < 0 )
									{
										if( $logging )
											ss_log_message( "discount period not started yet" );
										$applicable = false;
									}
									else
										if( strcmp( $now, $_SESSION['Shop']['DiscountCode']['di_ending'] ) > 0 )
										{
											if( $logging )
												ss_log_message( "discount period finished" );
											$applicable = false;
										}
										else
											if( strlen( $_SESSION['Shop']['DiscountCode']['di_left'] ) && ( $_SESSION['Shop']['DiscountCode']['di_left'] <= 0 ) )
											{
												if( $logging )
													ss_log_message( "Discount codes all used up, none left" );
												$applicable = false;
											}
											else
												if( strlen($_SESSION['Shop']['DiscountCode']['di_discount_group']) )		// this discount applies to products
												{
													if( $logging )
														ss_log_message( "discount applies to group ".$_SESSION['Shop']['DiscountCode']['di_discount_group'] );
													$applicable = false;

													$dg = getField( "select pr_dig_id from shopsystem_products where pr_id = {$entry['Product']['pr_id']}" );
													if( $dg == $_SESSION['Shop']['DiscountCode']['di_discount_group'] )
													{
														if( $logging )
															ss_log_message( "product is in group" );
														$applicable = true;

														$sa = getField( "select pro_stock_available from shopsystem_product_extended_options where pro_pr_id = {$entry['Product']['pr_id']}" );
														if( $sa <= $_SESSION['Shop']['DiscountCode']['di_minimum_stock'] )
														{
															if( $logging )
																ss_log_message( "insufficient stock" );
															$applicable = false;
														}
													}
													else
													{
														if( $logging )
															ss_log_message( "product is not in group" );
														$dg = getField( "select ca_dig_id from shopsystem_categories join shopsystem_products on ca_id = pr_ca_id where pr_id = {$entry['Product']['pr_id']}" );
														if( $dg == $_SESSION['Shop']['DiscountCode']['di_discount_group'] )
														{
															if( $logging )
																ss_log_message( "category is in group" );
															$applicable = true;

															$sa = getField( "select pro_stock_available from shopsystem_product_extended_options where pro_pr_id = {$entry['Product']['pr_id']}" );
															if( $sa <= $_SESSION['Shop']['DiscountCode']['di_minimum_stock'] )
															{
																if( $logging )
																	ss_log_message( "insufficient stock" );
																$applicable = false;
															}
														}
													}
												}

								if( $applicable || ss_isAdmin() )
								{
									// yup, in this group

									if( $logging )
										ss_log_message( "applying discount ".$_SESSION['Shop']['DiscountCode']['di_amount']." ".$_SESSION['Shop']['DiscountCode']['di_type']." to price of ".$price );
									if(  $_SESSION['Shop']['DiscountCode']['di_type'] == 'percent' )
									{
										// include freight in the price :)
										$product = $entry['Product'];
										$includedFreightUSD = includedFreight( $product, $_SESSION['ForceCountry']['cn_id'] );
										$exchangeRateUSD = ss_getExchangeRate('USD', $entry['Product']['pro_source_currency']);
										$includedFreight = $includedFreightUSD * $exchangeRateUSD;

										if( $logging )
											ss_log_message( "applying discount ".$_SESSION['Shop']['DiscountCode']['di_amount']." ".$_SESSION['Shop']['DiscountCode']['di_type']." to price of ".($price + $includedFreight));

/*
										if( $entry['Product']['pro_special_price'] !== null )
										{
											if( $logging )
												ss_log_message( "using special" );

											$price = $entry['Product']['pro_special_price'];
										}
*/
										$new_price = (($price+$includedFreight) * (1.0-($_SESSION['Shop']['DiscountCode']['di_amount']/100.0)));

										if( $logging )
											ss_log_message( "after discount, price is $new_price" );

										if( $_SESSION['Shop']['DiscountCode']['di_amount'] > 99 )		// free box at work
											$total_code_discount += 1*($price + $includedFreight -$new_price)* ss_getExchangeRate($entry['Product']['pro_source_currency'],$currency['CurrencyCode']);
										else
											$total_code_discount += $entry['Qty']*($price + $includedFreight -$new_price)* ss_getExchangeRate($entry['Product']['pro_source_currency'],$currency['CurrencyCode']);
										if( $logging )
											ss_log_message( "code discount is $total_code_discount {$currency['CurrencyCode']}" );
									}
									else
									{
										$sc = getField( "select pro_source_currency from shopsystem_product_extended_options where pro_pr_id = {$entry['Product']['pr_id']}" );
										if( $sc == $_SESSION['Shop']['DiscountCode']['di_type'] )
										{
											$new_price = $entry['Product']['pro_price'] - $_SESSION['Shop']['DiscountCode']['di_amount'];
											if( $entry['Product']['pro_special_price'] !== null )
												if( $new_price > $entry['Product']['pro_special_price'] )
												{
													if( $logging )
														ss_log_message( "special is lower, using that" );
													$price = $entry['Product']['pro_special_price'];
												}
												else
												{
													ss_log_message( "Applying discount $price-$new_price * {$entry['Qty']} exch rt ".ss_getExchangeRate($entry['Product']['pro_source_currency'],$currency['CurrencyCode']) );
													$total_code_discount += $entry['Qty']*($price-$new_price)* ss_getExchangeRate($entry['Product']['pro_source_currency'],$currency['CurrencyCode']);
//													$price = ss_decimalFormat($new_price);
												}
											else
											{
												$total_code_discount += $entry['Qty']*($price-$new_price)* ss_getExchangeRate($entry['Product']['pro_source_currency'],$currency['CurrencyCode']);
//												$price = ss_decimalFormat($new_price);
											}
										}
									}
									if( $logging )
										ss_log_message( "result price is ".$price );
								}
							}
						}

						// are we a wholesaler ???
						if( array_key_exists( 'User', $_SESSION )
							&& array_key_exists( 'us_wholesaler', $_SESSION['User'] )
							&& strlen( $_SESSION['User']['us_wholesaler'] )
							&& array_key_exists( 'pro_wholesaler_price', $entry['Product'] ) 
							&& strlen( $entry['Product']['pro_wholesaler_price'] )
						  )
						{
							if( $logging )
								ss_log_message( "is wholesaler" );
							$price = $entry['Product']['pro_wholesaler_price'];
						}

						if( $logging )
							ss_log_message( "pre-freight $price" );

						$currency = $thisAsset->getDisplayCurrency();

						// do we include freight on this?
						$addIncludedFreight = true;

						if( array_key_exists( 'FreeGift', $entry['Product'] ) )
						{
							if( $logging )
								ss_log_message( "NOT including freight on this free box" );

							$addIncludedFreight = false;
						}

/*
						if(  array_key_exists('Reship',$this0->ATTRIBUTES) )
								$addIncludedFreight = false;
*/

						if( $addIncludedFreight )
						{
							// include freight in the price :)
							$product = $entry['Product'];
							$includedFreightUSD = 1.0 * includedFreight( $product, $_SESSION['ForceCountry']['cn_id'] );
							$exchangeRateUSD = ss_getExchangeRate('USD', $entry['Product']['pro_source_currency'] );
							$includedFreight = $includedFreightUSD * $exchangeRateUSD;

							if( $logging )
								ss_log_message( "included freight on product id {$entry['Product']['pr_id']} is USD $includedFreightUSD -> {$currency['CurrencyCode']} $includedFreight ($exchangeRateUSD)" );

							$price += $includedFreight;
						}
						else
							if( $logging )
								ss_log_message( "Skipping adding freight for ".$entry['Product']['pro_stock_code'] );

						$wantTax = true;
						if (ss_optionExists('Shop Donations'))
						{
							if ($entry['Product']['pr_donation'])
								$wantTax = false;
						}
						// Figure out the tax
						$taxForThisProduct = 0;
						if ($wantTax)
						{
							$tax = $thisAsset->calculateTax($price);
							if ($tax !== false)
							{
								if (ss_optionExists('Shop Tax Excluded'))
								{
									// Dont include tax in the product prices, just add to the total
								}
								else
								{
									// Add tax to price of the product
									$price += $tax['Amount'];
								}
								$totalTax['Code'] = $tax['Code'];
								$taxForThisProduct = $tax['Amount']*$entry['Qty'];
								$totalTax['Amount'] += $taxForThisProduct;
							}
						}

						if( $logging )
						{
							ss_log_message( "Display Currency" );
							ss_log_message_r(  'Log:'.__FILE__.':'.__LINE__, $currency );
						}

						if( $logging )
							ss_log_message( "pre-currency-munge $price" );

						if( array_key_exists('pro_source_currency', $entry['Product'])
						 && $entry['Product']['pro_source_currency'] != $currency['CurrencyCode'] )
						{
							// scale the price according to the two above
							if( $logging )
								ss_log_message( "scale as ".$entry['Product']['pro_source_currency']." != ".$currency['CurrencyCode'] );
							$price = $price * ss_getExchangeRate($entry['Product']['pro_source_currency'],$currency['CurrencyCode']);
							// $price = ss_roundMoney($price);
						}


						// Round the amount
						if (false && ss_optionExists('Shop Round Prices'))
						{
							$price = ss_roundMoney($price);
						}

						// acme loyalty points
						if (false && ss_optionExists("Shop Acme Rockets") and $canUsePoints)
						{
							if ($entry['Product']['pr_points'] == 1)
							{
								if ($price+$taxForThisProduct > $loyaltyPointsDiscount)
								{
									$loyaltyPointsDiscount = $price+$taxForThisProduct;
									$foundLoyaltyPointsDiscount = true;
								}
							}
						}

						if( $logging )
							ss_log_message( "pre-tax $price" );

						if( $entry['Product']['pr_sales_tax_exempt'] == 'false' )
							if( $local_tax > 0 )
								$price += ($price * $local_tax / 10000 );

						if( $site_discount != 0 )
							$price -= ($price * $site_discount / 100 );
					}

					if( $logging )
						ss_log_message( "Final price on ".$entry['Product']['pro_stock_code']." is $price" );

					$entry['Product']['Price'] = $price;

					for($i = 0; $i < count( $promos ); $i++)
					{
						if( $entry['Product']['pr_ve_id'] == $promos[$i]['pr_ve_id'] )
						{
							if( ($promos[$i]['fg_ca_id_list'] == '*') || strstr( $promos[$i]['fg_ca_id_list'], ' '.$entry['Product']['pr_ca_id'].',' ) )		// in list
							{
								ss_log_message( "Product {$entry['Product']['pr_name']} category {$entry['Product']['pr_ca_id']} in this list {$promos[$i]['fg_ca_id_list']}" );
								$promos[$i]['Total'] += $entry['Product']['Price']*$entry['Qty'] * ss_getExchangeRate( $currency['CurrencyCode'], 'EUR' );
							}
						}
					}
					$_SESSION['Shop']['Basket']['Products'][$index] = $entry;
				}
				$total += $entry['Qty']*$entry['Product']['Price'];
				$totalUnits += $entry['Qty'];
			}
		}


		if( $local_tax > 0 )
		{
			$total_code_discount += ( $total_code_discount * $local_tax / 10000 );
			if( $logging )
				ss_log_message( "Total code discount is $total_code_discount {$currency['CurrencyCode']}" );
		}

		// update the basket
		if( $total_code_discount > 0 )
				$_SESSION['Shop']['Basket']['Discounts'][$_SESSION['Shop']['DiscountCode']['di_code']] = -$total_code_discount;

		$_SESSION['Shop']['Basket']['included_freight'] = $includedFreight;

		$result['totalTax'] = $totalTax;
		$result['totalUnits'] = $totalUnits;
		$result['total'] = $total;
		$result['foundLoyaltyPointsDiscount'] = $foundLoyaltyPointsDiscount;
		$result['loyaltyPointsDiscount'] = $loyaltyPointsDiscount;
		$result['freePrID'] = NULL;

		ss_log_message_r(  'Log:'.__FILE__.':'.__LINE__, $promos );
		for($i = 0; $i < count( $promos ); $i++)
			if( $promos[$i]['Total'] >= $promos[$i]['fg_minimum_total'] )
				$result['freePrID'] = $promos[$i]['fg_pr_id'];

//		ss_log_message( "Basket:calcResult returning" );
//		ss_log_message_r(  'Log:'.__FILE__.':'.__LINE__, $result );

		return $result;

	}













	function updateBasket($key,&$settings,$index = null,&$this0, $free = FALSE)
	{
		global $thisAsset;

		$sa = print_r( $settings, TRUE );
		ss_log_message( "updateBasket($key,$sa,$index, this, $free )" );
		if ($index === null) 			// Its a new entry in the basket
		{
			$getDiscountGroup = '';
			if (ss_optionExists("Shop Discount Codes")) {
				$getDiscountGroup = ',pr_dig_id';
			}

			$getDonation = '';
			if (ss_optionExists("Shop Donations")) {
				$getDonation = ',pr_donation';
			}

			$getComboProduct = '';
			if (ss_optionExists('Shop Acme Rockets') or ss_optionExists('Shop Como Products')) {
				$getComboProduct = ',pr_combo';
			}

			$getStockAvailable = '';
			if (ss_optionExists('Shop Acme Rockets')) {
				$getStockAvailable = ',pro_stock_available';
			}

			$getPoints = '';
			if (ss_optionExists('Shop Acme Rockets')) {
				$getPoints = ',pr_points,pr_needs_extra_padding';
			}

			if( !ss_isAdmin() )
				if( strlen($_SESSION['ForceCountry']['cn_sales_zones']) )
					if( ss_AuthdCustomer( ) )
						$externalSQL = "and pr_authd_sales_zone in (".$_SESSION['ForceCountry']['cn_sales_zones'].")";
					else
						$externalSQL = "and pr_sales_zone in (".$_SESSION['ForceCountry']['cn_sales_zones'].")";
				else
					$externalSQL = '';
			else
				$externalSQL = ' ';

			if( !ss_isAdmin() )
			{
				// check shippping country
				/*
				$r = getRow( "select * from countries where cn_two_code = '".safe(  $_SESSION['ForceCountry'] )."'" );
				if( $r && strlen( $r['cn_redirect_url'] ) && !array_key_exists( 'ForceCountry', $GLOBALS['cfg'] ) )
				{
					ss_log_message( "Dope alert, redirecting to ".$r['cn_redirect_url'] );
					header( "Location: ".$r['cn_redirect_url'] );
					die;
				}
				*/
			}

			$sql = "SELECT pr_id,pr_short,pr_name,pr_ve_id,pr_quote_shipping,pr_add_gift,pro_uuids,pro_stock_code,pro_price,pro_special_price,pro_member_price,pro_country_price_override,pr_ca_id{$getDiscountGroup}{$getDonation}{$getComboProduct}{$getStockAvailable}{$getPoints}, pr_upsell, pro_source_currency,pro_wholesaler_price, pro_weight, pr_is_service, pr_restrict_product_to_gateway, pr_restrict_special_to_gateway, pr_sales_zone, pr_authd_sales_zone, pr_specials_sales_zone, pr_sales_tax_exempt
				FROM shopsystem_products,shopsystem_product_extended_options
				WHERE pr_id = ".safe($settings['pr_id'])."
					AND pr_id = pro_pr_id
					AND pro_id = ".safe($settings['Options'])."
					$externalSQL";
			ss_log_message( "UpdateBasket:$sql" );
			$product = getRow( $sql );
//				if ($product === null) die('Unknown product/option combination - '.$settings['pr_id'].'-'.$settings['Options']);

			if ($product === null)
			{
				ss_log_message( "UpdateBasket Failed : unable to select product : $sql" );
				die('<html><b>I am sorry, I am unable to add product ID '.$settings['pr_id'].' to your basket, please reload the previous page and try again.</b></html>');
			}
			if (ss_optionExists("Shop Acme Rockets") or ss_optionExists("Shop Combo Products")) {
				if ($product['pr_combo']) {
					// count, and record the products in the combo.
					$Q_Combo = query("
						SELECT * FROM shopsystem_combo_products
						WHERE cpr_element_pr_id = {$product['pr_id']}
					");
					$boxQty = 0;
					while ($cp = $Q_Combo->fetchRow()) {
						$boxQty += $cp['cpr_qty'];
					}
					$product['BoxQty'] = $boxQty;
				}
			}

			// ,0_170A7=0_1FD53,0_1A0CD=0_1FD56,0_1A6CD=0_1FD5A
			$options = '';
			foreach(ListToArray($product['pro_uuids'],',') as $option) {
				$parent = ListFirst($option,'=');
				$uuid = ListLast($option,'=');
				$option = getRow("
					SELECT * FROM select_field_options
					WHERE sfo_uuid = '".escape($uuid)."'
				");
				if ($option !== null) {
					$options .= ss_comma($options,', ').$option['sfo_value'];
				}
			}
			$product['Options'] = $options;

			// Check if its a donation field
			if (ss_optionExists("Shop Donations")) {
				if (array_key_exists('pr_donation',$product)) {
					if (is_numeric($settings['DonationAmount'])) {
						$product['pro_price'] = $settings['DonationAmount'];
						$product['pro_special_price'] = null;
						$product['pr_dig_id'] = null;
					}
				}
			}

			$entry = array(
				'Key'	=>	$key,
				'Product'	=>	$product,
				'Qty'	=>	0,
				'AddService' => array(),
			);
			$index = count($_SESSION['Shop']['Basket']['Products']);

			if( !array_key_exists('Reship',$this0->ATTRIBUTES) )
			{
				// any default services for this new entry?				pr_service_default = "true"
				$selectedServices = query( 'select * from product_service_options join shopsystem_products on sv_pr_id_service = pr_id where sv_pr_id = '
													.$product['pr_id'].' and pr_offline IS NULL and pr_service_default != "false"' );

				while( $service = $selectedServices->fetchRow() )
					$entry['AddService'][] = $service['sv_id'];
			}
		}
		else		// existing entry... 
		{
			$entry = $_SESSION['Shop']['Basket']['Products'][$index];
		}

		// Sanity check on values entered
		if (!is_numeric($settings['Qty'])) $settings['Qty'] = 1;
		if (!is_int($settings['Qty'])) $settings['Qty'] = round($settings['Qty']);

		switch ($settings['Mode']) {
			case 'Remove':
				$entry['Qty'] -= $settings['Qty'];
				break;
			case 'Add':
				$entry['Qty'] += $settings['Qty'];
				break;
			case 'Set':
				$entry['Qty'] = $settings['Qty'];
				break;
		}

		if( $entry['Qty'] == 0 && array_key_exists( 'FreeGift', $entry['Product'] ) )
		{
			ss_log_message( "User doesn't want free box" );
			$_SESSION['REMOVED_FREE'] = true;
		}

//		ss_log_message( "update basket: ".$settings['Mode']." to index $index this..." );
//		ss_log_message_r(  'Log:'.__FILE__.':'.__LINE__, $entry );

		$result = null;
		if (ss_optionExists('Shop Acme Rockets'))
		{
			if( array_key_exists( 'pr_combo', $entry['Product'] ) && ( $entry['Product']['pr_combo'] >= 1 ) )
			{			// TODO, combo products

			}
			else
				if ( !ss_isAdmin()
					and array_key_exists('pro_stock_available',$entry['Product']) 
					and $entry['Product']['pro_stock_available'] !== null 
					and $entry['Product']['pro_stock_available'] < $entry['Qty'])
				{
					$entry['Qty'] = $entry['Product']['pro_stock_available'];
					ss_log_message( "Stock available = ". $entry['Product']['pro_stock_available'] );
	//				$result = 'Quantity reduced due to stock availability.';
				}
		}

		if( $free )
		{
			$entry['Product']['pro_special_price'] = null;
			$entry['Product']['pro_price'] = 0;
			$entry['Product']['Price'] = 0;
			$entry['Product']['FreeGift'] = 1;
		}

		$_SESSION['Shop']['Basket']['Products'][$index] = $entry;

        return $result;
	}
?>
