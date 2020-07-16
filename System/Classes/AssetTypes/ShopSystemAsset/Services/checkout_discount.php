<?php
	// into bitcoin offer, TODO to be removed.
	// if( $_SESSION['Shop']['CurrencyCountry']['cn_currency_code'] == 'BTC' )

	$intro_discount_rate = getDefaultCurrencyDiscount( );
	$intro_discount_name = getDefaultCurrencyName( )." Intro Discount";

	$chargeTotal = $_SESSION['Shop']['Basket']['SubTotal'];
//	if( $pg['Gateway']['po_option_discountx100'] > 0 )

	$has_freight = false;
	if( array_key_exists( 'Freight', $_SESSION['Shop']['Basket'] ) )
		if( array_key_exists( 'Amount', $_SESSION['Shop']['Basket']['Freight'] ) )
			if( $_SESSION['Shop']['Basket']['Freight']['Amount'] > 0 )
				$has_freight = true;

//	add penalty of 15 Euro for not ordering over Euro 150.
	$to_EUR = ss_getExchangeRate( getDefaultCurrencyCode( ), 'EUR' );
	$cnMin = 150;
	if( array_key_exists( 'ForceCountry', $_SESSION )
	 && array_key_exists( 'cn_shipping_penalty_min_total', $_SESSION['ForceCountry'] ) )
		$cnMin = $_SESSION['ForceCountry']['cn_shipping_penalty_min_total'];

	if( !$has_freight && ( $chargeTotal * $to_EUR < $cnMin ) && count($_SESSION['Shop']['Basket']['Products']) )
	{
		ss_log_message( "Order under EUR150, adding EUR15" );
		$_SESSION['Shop']['Basket']['Discounts']['Spend over 150 EURO to get free shipping'] = 15 / $to_EUR;
	}

	$credit = 0;
	$credit_discount = 0;
	if( array_key_exists( 'us_account_credit', $_SESSION['User'] )
		  && array_key_exists( 'us_credit_from_gateway_option', $_SESSION['User'] )
		  && ( $_SESSION['User']['us_account_credit'] > 0 ) )
	{
		if( $foo = getCurrencyEntry( $_SESSION['User']['us_credit_from_gateway_option'] ) )
		{
			$credit = $_SESSION['User']['us_account_credit'] * ss_getExchangeRate($foo['po_currency'], getDefaultCurrencyCode( ) );
			if( $foo['po_option_discountx100'] > 0 )
			{
				if( $chargeTotal > $credit )
					$credit_discount = $credit * ( $foo['po_option_discountx100'] / 10000.0 ) / (1 - $foo['po_option_discountx100'] / 10000.0 );
				else
					// $credit_discount = $chargeTotal * ( $foo['po_option_discountx100'] / 10000.0 ) / (1 - $foo['po_option_discountx100'] / 10000.0 );
					$credit_discount = $chargeTotal * ( $foo['po_option_discountx100'] / 10000.0 );
				$intro_discount_name = $foo['po_currency']." Intro Discount";
			}
		}
	}

	$left = $chargeTotal-$credit-$credit_discount;
	if( $left < 0 )
		$left = 0;

	ss_log_message( "chargeTotal = $chargeTotal, credit = $credit+$credit_discount, left = $left" );
//  sjv0:2014-10-16 11:19:40:chargeTotal = 447, credit = 1012.32+49.666666666667, left = 0
//	ss_log_message_r(  'Log:'.__FILE__.':'.__LINE__, $pg['Gateway']['po_option_discountx100'] );

	unset( $_SESSION['Shop']['Basket']['Discounts'][$intro_discount_name] );
	if( ( $intro_discount_rate > 0 ) || ($credit_discount > 0 ) )
			$_SESSION['Shop']['Basket']['Discounts'][$intro_discount_name] = -($intro_discount_rate * $left / 100.0 + $credit_discount);

	// figure out the new sub total

	$_SESSION['Shop']['TotalDiscount'] = 0;
	foreach($_SESSION['Shop']['Basket']['Discounts'] as $discount => $amount)
		$_SESSION['Shop']['TotalDiscount'] += $amount;	

	$_SESSION['Shop']['Basket']['Total'] = $_SESSION['Shop']['Basket']['SubTotal'] + $_SESSION['Shop']['Basket']['Freight']['Amount'] + $_SESSION['Shop']['TotalDiscount'];
?>
