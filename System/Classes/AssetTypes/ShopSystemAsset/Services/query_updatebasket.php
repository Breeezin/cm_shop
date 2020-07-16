<?php
	// Build a structure of discounts
	$totalDiscounts = 0;
	$_SESSION['Shop']['Basket']['Discounts'] = array();

	require_once('inc_updatebasket.php');

	$this->param('Mode','Add');

	$gateway = NULL;
	if( array_key_exists('Gateway', $this->ATTRIBUTES))
		$gateway = (int)  $this->ATTRIBUTES['Gateway'];

	ss_log_message( "updatebasket gateway $gateway" );

//	ss_log_message_r(  'Log:'.__FILE__.':'.__LINE__, $_SESSION['Shop']['Basket'] );

	if (array_key_exists('Remove_x',$this->ATTRIBUTES)) {
		$this->ATTRIBUTES['Mode'] = 'Set';	
		$this->ATTRIBUTES['Qty'] = 0;	
	}

	if (array_key_exists('AddService',$this->ATTRIBUTES))
	{
		$note = '';

		$sv_id = (int) $this->ATTRIBUTES['AddService'];

		if( array_key_exists( 'DoIt', $this->ATTRIBUTES ) && ($this->ATTRIBUTES['DoIt'] == 1) )
			$note = "Adding service $sv_id";
		else
			$note = "Removing service $sv_id";
		ss_log_message( $note );

		$sv = getRow( "select * from product_service_options join shopsystem_products on pr_id = sv_pr_id_service where sv_id = $sv_id" );
		if( $sv )
		{
			if( $sv_id > 0 )
			{
				$PrID_for = $sv[ 'sv_pr_id' ];
				ss_log_message("... for product $PrID_for");

				if( array_key_exists( 'DoIt', $this->ATTRIBUTES ) && ($this->ATTRIBUTES['DoIt'] == 1) )
				{
					// add this as service to this product
					for	($index=0;$index<count($_SESSION['Shop']['Basket']['Products']);$index++)
					{
						if ($PrID_for == $_SESSION['Shop']['Basket']['Products'][$index]['Product']['pr_id'])
						{
							if( array_key_exists('AddService', $_SESSION['Shop']['Basket']['Products'][$index] )
							 && is_array( $_SESSION['Shop']['Basket']['Products'][$index]['AddService'] ) )
							{
								if( !in_array($sv_id, $_SESSION['Shop']['Basket']['Products'][$index]['AddService'] ) )
									$_SESSION['Shop']['Basket']['Products'][$index]['AddService'][] = $sv_id;
								else
									;

								if( $sv['pr_service_exclude'] > 0 )
								{
									$service = getField( "select sv_id from product_service_options where sv_pr_id_service = {$sv['pr_service_exclude']} and sv_pr_id = $PrID_for" );
									if( ($delme = array_search( $service, $_SESSION['Shop']['Basket']['Products'][$index]['AddService'] ) ) !== false )
									{
//										ss_log_message( "removing conflicting service ".$sv['pr_service_exclude']." from" );
//										ss_log_message_r(  'Log:'.__FILE__.':'.__LINE__, $_SESSION['Shop']['Basket']['Products'][$index]['AddService'] );
										unset( $_SESSION['Shop']['Basket']['Products'][$index]['AddService'][$delme]  );
									}
								}
							}
							else
								$_SESSION['Shop']['Basket']['Products'][$index]['AddService'] = array( $sv_id );


							break;
						}
					}
				}
				else
				{
					// remove this as service from this product
					for	($index=0;$index<count($_SESSION['Shop']['Basket']['Products']);$index++)
					{
						if ($PrID_for == $_SESSION['Shop']['Basket']['Products'][$index]['Product']['pr_id'])
						{
//							ss_log_message( "removing service $sv_id from ..." );
//							ss_log_message_r(  'Log:'.__FILE__.':'.__LINE__, $_SESSION['Shop']['Basket']['Products'][$index] );
							if( array_key_exists('AddService', $_SESSION['Shop']['Basket']['Products'][$index] )
							 && is_array( $_SESSION['Shop']['Basket']['Products'][$index]['AddService'] ) )
								foreach( $_SESSION['Shop']['Basket']['Products'][$index]['AddService'] as $i=>$serv )
									if( $serv == $sv_id )
										if( substr( $sv['pr_name'], 0, 4 ) != 'Must' )
											unset( $_SESSION['Shop']['Basket']['Products'][$index]['AddService'][$i] );
							break;
						}
					}
				}
			}
		}
		else
		{
			ss_log_message( "can't find service $sv_id" );
		}
	}
	else		// normal product
	{
		$this->setDefaultFreight();
		
		$note = '';
		
		switch ($this->ATTRIBUTES['Mode'])
		{
			case 'Empty':
				$_SESSION['Shop']['Basket'] = array();
				$_SESSION['Shop']['Basket']['Products'] = array();
				$this->setDefaultFreight();
				break;
			case 'FixTax':
				// do nothing. tax will update after
				break;
			case 'Refresh':
				// do nothing.. just refresh
				break;
			case 'FixDiscountCode':
				// do nothing. discount codes will update after
				break;

			default:// Set?

				if (array_key_exists('Key',$this->ATTRIBUTES))
				{
					$this->ATTRIBUTES['pr_id'] = ListFirst($this->ATTRIBUTES['Key'],'_');	
					$this->ATTRIBUTES['Options'] = ListLast($this->ATTRIBUTES['Key'],'_');	
				}

				$this->param('pr_id');
				$this->param('Qty');

				ss_paramKey($_SESSION['Shop'],'Basket',array());
				ss_paramKey($_SESSION['Shop']['Basket'],'Products',array());

				if (array_key_exists('Keys',$this->ATTRIBUTES))
				{
					// Add multiple products and options 
					foreach ($this->ATTRIBUTES['Keys'] as $key => $qty) {
						$theSettings = array(
							'pr_id'		=>	ListFirst($key,'_'),
							'Options'	=>	ListRest($key,'_'),
							'Qty'		=>	$qty,
							'Mode'		=>	$this->ATTRIBUTES['Mode'],
						);
						$found = false;
						for	($index=0;$index<count($_SESSION['Shop']['Basket']['Products']);$index++)
						{
							if ($key == $_SESSION['Shop']['Basket']['Products'][$index]['Key']) 
							{
								$updateResult = updateBasket($key,$theSettings,$index,$this);
								if( $updateResult )
									$note .= $updateResult;
								$found = true;

								break;
							}
						}
						if (!$found)
						{
							$updateResult = updateBasket($key,$theSettings,null,$this);
							if( $updateResult )
								$note .= $updateResult;
						}
					}
				}
				else
				{
					$this->param('Options', 0);
					if (is_array($this->ATTRIBUTES['Options']))
					{
						// Add multiple options
						$options = $this->ATTRIBUTES['Options'];
						foreach ($options as $option)
						{
							$theSettings = array(
								'pr_id'		=>	$this->ATTRIBUTES['pr_id'],
								'Options'	=>	$option,
								'Qty'		=>	$this->ATTRIBUTES['Qty'],
								'Mode'		=>	$this->ATTRIBUTES['Mode'],
							);
							$key = $theSettings['pr_id'].'_'.$theSettings['Options'];
							$found = false;
							for	($index=0;$index<count($_SESSION['Shop']['Basket']['Products']);$index++) {
								if ($key == $_SESSION['Shop']['Basket']['Products'][$index]['Key'])
								{
									$updateResult = updateBasket($key,$theSettings,$index,$this);
									if( $updateResult )
										$note .= $updateResult;
									$found = true;

									break;
								}
							}
							if (!$found)
							{
								$updateResult = updateBasket($key,$theSettings,null,$this);
								if( $updateResult )
									$note .= $updateResult;
							}
						}
					}
					else		// for acme, this is it.
					{
						if( !$this->ATTRIBUTES['Options'] )			// there is only one????  usually
							$this->ATTRIBUTES['Options'] = getField( "select pro_id from shopsystem_product_extended_options where pro_pr_id = ".((int)$this->ATTRIBUTES['pr_id']) );

						// Add single product options
						$key = $this->ATTRIBUTES['pr_id'].'_'.$this->ATTRIBUTES['Options'];
						$found = false;
						for	($index=0;$index<count($_SESSION['Shop']['Basket']['Products']);$index++)
						{
							if ($key == $_SESSION['Shop']['Basket']['Products'][$index]['Key'])
							{
								$updateResult = updateBasket($key,$this->ATTRIBUTES,$index,$this);
								if( $updateResult )
									$note .= $updateResult;
								$found = true;

								if( $this->ATTRIBUTES['Mode'] == 'NewPrice' )
								{
									if( array_key_exists('NewPrice', $_GET ) && (float) $_GET['NewPrice'] >= 0 )
									{
										ss_log_message( "NewPrice ".$_GET['NewPrice'] );
										if( array_key_exists('user_groups', $_SESSION['User']) and in_array(1, $_SESSION['User']['user_groups']) )
										{
											// munge currency

											$newPrice = $_GET['NewPrice'];

	/*
											if( $GLOBALS['cfg']['ChargeCurrency'][$_SESSION['DefaultCurrency']]['CurrencyCode'] 
												!= $_SESSION['Shop']['Basket']['Products'][$index]['Product']['pro_source_currency'] )
											{
												//$newPrice *= ss_getExchangeRate( $_SESSION['Shop']['Basket']['Products'][$index]['Product']['pro_source_currency'], $GLOBALS['cfg']['ChargeCurrency'][$_SESSION['DefaultCurrency']]['CurrencyCode'] );
												$newPrice *= ss_getExchangeRate($GLOBALS['cfg']['ChargeCurrency'][$_SESSION['DefaultCurrency']]['CurrencyCode'], $_SESSION['Shop']['Basket']['Products'][$index]['Product']['pro_source_currency']  );
											}
	*/

											$_SESSION['Shop']['Basket']['Products'][$index]['Product']['FixPrice'] = number_format( $newPrice, getDefaultCurrencyPrecision( ), '.', '' );
											$_SESSION['Shop']['Basket']['Products'][$index]['Product']['pro_price'] = number_format( $newPrice, getDefaultCurrencyPrecision( ), '.', '' );
											$_SESSION['Shop']['Basket']['Products'][$index]['Product']['pro_special_price'] = number_format( $newPrice, getDefaultCurrencyPrecision( ), '.', '' );
											$_SESSION['Shop']['Basket']['Products'][$index]['Product']['Price'] = number_format( $newPrice, getDefaultCurrencyPrecision( ), '.', '' );
											$_SESSION['Shop']['Basket']['Products'][$index]['Product']['pro_source_currency'] = getDefaultCurrencyCode( );
										}
									}
								}

								break;
							}
						}
						if( !$found )
						{
							$updateResult = updateBasket($key,$this->ATTRIBUTES,null,$this);
							if( $updateResult )
								$note .= $updateResult;

						}
					}
				}
		
		}
	}

	$cartCount = 0;

//	ss_log_message( "fixing services according to products" );

	for	($index=0;$index<count($_SESSION['Shop']['Basket']['Products']);$index++)
		if( $_SESSION['Shop']['Basket']['Products'][$index]['Product']['pr_is_service'] == 'true' )
		{
			ss_log_message( "index $index is a service, set to zero" );
			$_SESSION['Shop']['Basket']['Products'][$index]['Qty'] = 0;
		}
		else
			$cartCount += $_SESSION['Shop']['Basket']['Products'][$index]['Qty'];

	for	($index=0;$index<count($_SESSION['Shop']['Basket']['Products']);$index++)
	{
		$entry = $_SESSION['Shop']['Basket']['Products'][$index];

		if( ( $entry['Qty'] > 0 ) && ( $entry['Product']['pr_is_service'] == 'false' ) )
		{
			if( array_key_exists( 'AddService', $entry )
			 && is_array( $entry['AddService'] )
			 && count( $entry['AddService'] ) )
				foreach( $entry['AddService'] as $sv_idi )
				{
					ss_log_message( "As part of UpdateBasket {$entry['Product']['pr_name']} adding service id $sv_idi" );

					$svi = getRow( "select * from product_service_options where sv_id = $sv_idi" );
					if( $svi )
					{
						$this->ATTRIBUTES['pr_id'] = $addServicePrID = $svi['sv_pr_id_service'];
						$this->ATTRIBUTES['Options'] = $options 
								= getField( "select pro_id from shopsystem_product_extended_options where pro_pr_id = ".$this->ATTRIBUTES['pr_id'] );
						$this->ATTRIBUTES['Mode'] = 'Add';
						$this->ATTRIBUTES['Qty'] = $entry['Qty'];
						$key = $addServicePrID.'_'.$options;

						$found = false;
						for	($sindex=0;$sindex<count($_SESSION['Shop']['Basket']['Products']);$sindex++)
							if( $_SESSION['Shop']['Basket']['Products'][$sindex]['Key'] == $key )
							{
								$updateResult = updateBasket($key,$this->ATTRIBUTES,$sindex,$this);
								ss_log_message_r(  'Log:'.__FILE__.':'.__LINE__, "updated service id $sv_idi at index $sindex" );
								$found = true;
							}
						if( !$found )
						{
							$updateResult = updateBasket($key,$this->ATTRIBUTES,NULL,$this);
							ss_log_message_r(  'Log:'.__FILE__.':'.__LINE__, "service id $sv_idi added to end" );
//								ss_log_message_r(  'Log:'.__FILE__.':'.__LINE__, $_SESSION['Shop']['Basket'] );
						}
					}
					else
					{
						ss_log_message( "Unable to retrieve product_service_options:sv_id:$sv_idi" );
					}
				}
		}
	}

	// Remove deleted products and calculate tax
	// calculate prices for all products in the basket
	$calcResult = calculatePrices($this, $gateway);

	$freePrID = $calcResult['freePrID'];

	if( array_key_exists('Reship',$this->ATTRIBUTES) )			// admin doesn't want any of this malarky when doing a reship
		$freePrID = NULL;

	if( array_key_exists( 'User', $_SESSION ) && array_key_exists( 'us_wholesaler', $_SESSION['User'] ) && strlen( $_SESSION['User']['us_wholesaler'] ) )
		$freePrID = NULL;

	if( array_key_exists('user_groups', $_SESSION['User'])
	 and in_array(1, $_SESSION['User']['user_groups']) 
	 and array_key_exists('Remove_x',$this->ATTRIBUTES))
		$freePrID = NULL;

	// remove anything added by the free box stuff

	ss_log_message( "AFTER ADDED PRODUCT" );
	ss_log_message_r(  'Log:'.__FILE__.':'.__LINE__, $_SESSION['Shop']['Basket'] );

	$newerBasket = array();
	for	($index=0;$index<count($_SESSION['Shop']['Basket']['Products']);$index++)
	{
		$entry = $_SESSION['Shop']['Basket']['Products'][$index];
		ss_log_message_r(  'Log:'.__FILE__.':'.__LINE__, $entry );
		if(!array_key_exists( 'FreeGift', $entry['Product'] ) )
			array_push($newerBasket,$entry);
	}
	$_SESSION['Shop']['Basket']['Products'] = $newerBasket;

	ss_log_message( "After removing free boxes there are ".count($_SESSION['Shop']['Basket']['Products'])." products in the basket" );

	if( $freePrID && !array_key_exists( 'REMOVED_FREE', $_SESSION ) )
	{
		ss_log_message( "Adding free box pr_id:$freePrID to basket" );

		$freeBox = getRow(" SELECT pr_id, pro_id, pro_stock_available FROM shopsystem_products, shopsystem_product_extended_options
								WHERE pr_id = pro_pr_id
									AND pr_id  = ".(int)$freePrID );

		if( $freeBox['pro_stock_available'] > 0 )
		{
			// add it to our basket
			$theSettings = array(
				'pr_id'		=>	$freeBox['pr_id'],
				'Options'	=>	$freeBox['pro_id'],
				'Qty'		=>	1,
				'Mode'		=>	'Set',
				);

			$key = $freeBox['pr_id'].'_'.$freeBox['pro_id'];
			$updateResult = updateBasket($key,$theSettings,null,$this, TRUE);
			if( $updateResult )
				$note .= $updateResult;
			$calcResult = calculatePrices($this, $gateway);
		}
	}

//	ss_log_message_r(  'Log:'.__FILE__.':'.__LINE__, "after free box machinations" );
//	ss_log_message_r(  'Log:'.__FILE__.':'.__LINE__, $_SESSION['Shop']['Basket'] );

//	ss_log_message( "AAAAAAA ".count( $_SESSION['Shop']['Basket']['Products'] ) );

	// Update basket totals
	$total = $calcResult['total'];
	$totalTax = $calcResult['totalTax'];
	$totalUnits = $calcResult['totalUnits'];
	$foundLoyaltyPointsDiscount = $calcResult['foundLoyaltyPointsDiscount'];
	$loyaltyPointsDiscount = $calcResult['loyaltyPointsDiscount'];

	
	$_SESSION['Shop']['Basket']['SubTotal'] = $total;
	
	if ($totalTax['Amount'] == 0) {
		$_SESSION['Shop']['Basket']['Tax'] = false;
	} else {
		if (ss_optionExists('Shop Tax Excluded')) {
			$_SESSION['Shop']['Basket']['TaxIncluded'] = false;
			$total += $totalTax['Amount'];
			$_SESSION['Shop']['Basket']['SubTotal'] = $total;
		} else {
			$_SESSION['Shop']['Basket']['TaxIncluded'] = true;
		}
		$_SESSION['Shop']['Basket']['Tax'] = $totalTax;
	}

	// check for a xx% discount if purchasing more than x products
	if (ss_optionExists('Shop Discount For Units') !== false) {
		$disc = ss_optionExists('Shop Discount For Units');
		$minUnits = ListLast($disc);
		$percentage = ListFirst($disc);
		if ($totalUnits >= $minUnits) {
			// We get the discount, yay!
			
			// check if its a dollar amount or percentage
			if (strstr($percentage,'$') !== false) {
				$dollar = str_replace('$','',$percentage);
				$_SESSION['Shop']['Basket']['Discounts'][$percentage.' discount']	=	-$dollar;
			} else {
				$_SESSION['Shop']['Basket']['Discounts'][$percentage.'% discount']	=	0-ss_roundMoney($total * $percentage / 100);
			}
		}
	}
	
	// check for acme loyalty points discount
	if ($foundLoyaltyPointsDiscount) {
		$_SESSION['Shop']['Basket']['Discounts']['Frequent Buyer Program Points Discount']	= 0-ss_roundMoney($loyaltyPointsDiscount);
	}
	
	if (ss_optionExists('Shop Per Member Discounts')) {
		if (array_key_exists('User',$_SESSION) and array_key_exists('UsDiscountPercentage',$_SESSION['User'])) {
			$percentage = $_SESSION['User']['UsDiscountPercentage'];
			if (strlen($percentage)) {
				$_SESSION['Shop']['Basket']['Discounts']['Member discount']	= 0-ss_roundMoney($total * $percentage / 100);
			}
		}
	}

	// user discount?
	if( array_key_exists('User', $_SESSION )
		 and array_key_exists('us_discount', $_SESSION['User'])
		 and array_key_exists('us_discount_expires', $_SESSION['User'])
		 and ($_SESSION['User']['us_discount'] > 0 ) )
	{
		ss_log_message( "User has discount of {$_SESSION['User']['us_discount']}, expires {$_SESSION['User']['us_discount_expires']}" );

		$diff = strncmp($_SESSION['User']['us_discount_expires'], strftime('%F'), 10 );
		if( $diff > 0 )
		{
			$new_total = $total * (100 - $_SESSION['User']['us_discount'])/100;
			if( !array_key_exists( 'Discounts', $_SESSION['Shop']['Basket'] )
			  || !is_array( $_SESSION['Shop']['Basket']['Discounts'] ) )
				$_SESSION['Shop']['Basket']['Discounts'] = array();

			$_SESSION['Shop']['Basket']['Discounts']['Personal Discount'] = number_format(  $new_total-$total, getDefaultCurrencyPrecision( ) );
		}
		else
			ss_log_message( "Expired ".strftime('%F')." ".$diff );
	}

	// user discount?
	if( !array_key_exists( 'Discounts', $_SESSION['Shop']['Basket'] )
	  || !is_array( $_SESSION['Shop']['Basket']['Discounts'] ) )
		$_SESSION['Shop']['Basket']['Discounts'] = array();

	unset( $_SESSION['Shop']['Basket']['Discounts']['Account Credit'] );

	$credit = 0;

	if( array_key_exists('User', $_SESSION )
		 and array_key_exists('us_account_credit', $_SESSION['User']) )
/*		 and ($_SESSION['User']['us_account_credit'] > 0 ) )	*/
	{
		$creditCurrency = 'EUR';
		if( $foo = getCurrencyEntry( $_SESSION['User']['us_credit_from_gateway_option'] ) )
			$creditCurrency = $foo['po_currency'];

		$credit =  $_SESSION['User']['us_account_credit'];

		ss_log_message( "User has credit of $credit $creditCurrency" );

		$credit *=  ss_getExchangeRate($creditCurrency, getDefaultCurrencyCode( ) );

		ss_log_message( "Now User has credit of $credit ".getDefaultCurrencyCode( ) );

		$new_total = $total - $credit;

		$_SESSION['Shop']['Basket']['Discounts']['Account Credit'] = number_format(  -$credit, getDefaultCurrencyPrecision( ), '.', '' );
	}


//	ss_log_message_r(  'Log:'.__FILE__.':'.__LINE__, "after credit calcs" );
//	ss_log_message_r(  'Log:'.__FILE__.':'.__LINE__, $_SESSION['Shop']['Basket'] );


	// figure out the new sub total
	foreach($_SESSION['Shop']['Basket']['Discounts'] as $discount => $amount) {
		ss_log_message( "discount $discount is $amount" );
		$totalDiscounts += $amount;	
	}
	$total += $totalDiscounts;

	// Calculate freight calculateFreight()
	$_SESSION['Shop']['Basket']['Freight']['Amount'] = $this->calculateExtraFreight();
	$_SESSION['Shop']['Basket']['Total'] = $total+$_SESSION['Shop']['Basket']['Freight']['Amount'];
	$_SESSION['Shop']['Basket']['CartNumber'] = $cartCount;
	$_SESSION['Shop']['Basket']['CartTotal'] = getDefaultCurrencyCode( ).'&nbsp;'.number_format($_SESSION['Shop']['Basket']['Total'], getDefaultCurrencyPrecision( ), '.', '' );
	ss_log_message( "Basket total is {$_SESSION['Shop']['Basket']['Total']}" );

	if( array_key_exists('Discounts', $_SESSION['Shop']['Basket'])
	 	&& array_key_exists('DiscountCode', $_SESSION['Shop'] )
		&& is_array( $_SESSION['Shop']['DiscountCode'] )
	 	&& array_key_exists('di_code', $_SESSION['Shop']['DiscountCode'] )
	 	&& array_key_exists($_SESSION['Shop']['DiscountCode']['di_code'], $_SESSION['Shop']['Basket']['Discounts'] ) )
	{

		$EURTotal = ( $_SESSION['Shop']['Basket']['Total'] + $credit  ) * ss_getExchangeRate(getDefaultCurrencyCode( ), 'EUR' );
//		if( $EURTotal < 100 )						// TODO need to be config item.
//			$_SESSION['Shop']['Basket']['Discounts'][$_SESSION['Shop']['DiscountCode']['di_code']] = 0;
	}

	if( array_key_exists( 'Vacuum', $_SESSION['Shop']['Basket'] ) && ( $_SESSION['Shop']['Basket']['Vacuum'] > 0 ) )
		$_SESSION['Shop']['Basket']['Total'] += $_SESSION['Shop']['Basket']['Vacuum'];

	if (array_key_exists('BackURL',$this->ATTRIBUTES)) {
		ss_log_message( "Redirecting to ".$this->ATTRIBUTES['BackURL'] );
		location($this->ATTRIBUTES['BackURL']);
	}

	if( array_key_exists('CloseWindow', $this->ATTRIBUTES))
	{
		echo "Order Updated";
		echo "<script> window.setTimeout('self.close()', 500); </script>";
		die;
	}

	if( array_key_exists('Async', $this->ATTRIBUTES))
	{
		$pop = 'Your shopping cart is empty!';

		if( array_key_exists( 'Shop', $_SESSION )
		 && array_key_exists( 'Basket', $_SESSION['Shop'] )
		 && array_key_exists( 'Products', $_SESSION['Shop']['Basket'] ) )
		{
			if( count($_SESSION['Shop']['Basket']['Products']) > 0 )
			{
				$pop = '';
				$log = '';
				foreach( $_SESSION['Shop']['Basket']['Products'] as $product )
				{
					if( $product['Product']['pr_is_service'] == 'true' )
						;
					else
					{
						$pop .= '<p class="something">';
						if( array_key_exists( 'Qty', $product ) )
						{
							$pop .= $product['Qty'].' x ';
							$log .= $product['Qty'].' x ';
						}
						$log .= 'pr_id:'.$product['Product']['pr_id'].',';
						if( array_key_exists( 'Product', $product )
							&& array_key_exists( 'pr_name', $product[ 'Product' ] ) )
							$pop .= $product['Product']['pr_name'];
						$pop .= '</p>';
					}
				}
			}
		}

		echo "cartNumber=$cartCount;cartTotal='".$_SESSION['Shop']['Basket']['CartTotal']."';cartContent='".escape($pop)."';";
		ss_log_message( "cartNumber=$cartCount;cartTotal='".$_SESSION['Shop']['Basket']['CartTotal']."';cartContent='".escape($log)."';" );
		die;
	}

	if (!array_key_exists('AsService',$this->ATTRIBUTES))		// this is a person ordering something...
	{
		if ($note != '') {
			locationRelative(ss_withoutPreceedingSlash($asset->getPath()).'/Service/Basket?Note='.ss_URLEncodedFormat($note));
		} else {
			locationRelative(ss_withoutPreceedingSlash($asset->getPath()).'/Service/Basket');
		}
	}
	else				// this is the admin person, save potential note in the database against new order.
	{
		if( $note != '' )
			$_SESSION['NastyHackNoteValue'] = $note;

//		ss_log_message_r(  'Log:'.__FILE__.':'.__LINE__, $_SESSION['Shop']['Basket'] );
	}
	
?>
