<?php
	global $cfg;

	if ($basket === null)
		$basket = $_SESSION['Shop']['Basket'];

	$trackingCost = 0;

	$_SESSION['TrackingChoice'] = false;
	if( array_key_exists( 'tracking', $_GET ) )
		if( $_GET['tracking'] == 'true' )
		{
			ss_log_message( "Tracking enabled by URL" );
			$_SESSION['TrackingChoice'] = true;
		}

	if( !array_key_exists( 'cn_box_tracking_cost_currency', $_SESSION['ForceCountry'] ) )
	{
		ss_log_message( "No cn_box_tracking_cost_currency, reloading country" );
		$_SESSION['ForceCountry'] = getRow( "select * from countries where cn_id = ".$_SESSION['ForceCountry']['cn_id']);
	}

	if( array_key_exists( 'User', $_SESSION ) && array_key_exists('us_id', $_SESSION['User']) &&  $_SESSION['User']['us_id'] > 0 )
	{
		$trackoptions = getRow( "select * from users where us_id = ".((int)  $_SESSION['User']['us_id'] ) );
		if( $trackoptions['us_permanent_tracking'] || $trackoptions['us_temporary_tracking'] )
		{
			$boxes = 0;
			for	($index=0;$index<count($_SESSION['Shop']['Basket']['Products']);$index++)
				$boxes += $_SESSION['Shop']['Basket']['Products'][$index]['Qty'];
			$c = $cfg['ShippingTracking'];
			if( $_SESSION['ForceCountry']['cn_box_tracking_cost_x100'] > 0 )
				$c = $_SESSION['ForceCountry']['cn_box_tracking_cost_x100'];
			$trackingCost = $c / 100.0;
			//$trackingCost = round($boxes/2) * $c / 100.0;
			$_SESSION['TrackingChoice'] = true;
			ss_log_message( "Tracking enabled by User ".$_SESSION['User']['us_id']." for country ".$_SESSION['ForceCountry']['cn_name'] );
		}
	}

	if( $_SESSION['ForceCountry']['cn_shipping_tracking'] == 'Mandatory' || ($_SESSION['ForceCountry']['cn_shipping_tracking'] == 'Optional' && array_key_exists( 'TrackingChoice', $_SESSION) && $_SESSION['TrackingChoice'] == true ) )
	{
		$boxes = 0;
		for	($index=0;$index<count($_SESSION['Shop']['Basket']['Products']);$index++)
			if( $_SESSION['Shop']['Basket']['Products'][$index]['Product']['pr_is_service'] == 'false' )
				$boxes += $_SESSION['Shop']['Basket']['Products'][$index]['Qty'];
		$c = $cfg['ShippingTracking'];
		if( $_SESSION['ForceCountry']['cn_box_tracking_cost_x100'] > 0 )
			$c = $_SESSION['ForceCountry']['cn_box_tracking_cost_x100'];
		$trackingCost = $c / 100.0;
		//$trackingCost = round($boxes/2) * $c / 100.0;
		$_SESSION['TrackingChoice'] = true;
		ss_log_message( "Tracking enabled by Country Cost:$c Boxes:$boxes -> $trackingCost for country ".$_SESSION['ForceCountry']['cn_name'] );
	}

	$accessoryShippingCost = getAccessoryShippingCost();

//		$trackingCost *= count( $basket['Products'] );

	$exchangeTracking = 1;
	if( getDefaultCurrencyCode( ) != $_SESSION['ForceCountry']['cn_box_tracking_cost_currency'] )
		$exchangeTracking = ss_getExchangeRate( $_SESSION['ForceCountry']['cn_box_tracking_cost_currency'], getDefaultCurrencyCode( ) );

	return ($trackingCost * $exchangeTracking) + $accessoryShippingCost;


?>
