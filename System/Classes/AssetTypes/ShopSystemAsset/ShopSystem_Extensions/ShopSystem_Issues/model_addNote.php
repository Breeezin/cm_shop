<?php 
	$this->param('or_id');
	$this->param('BackURL');

	$chargeList = 1;
	$currency = getField( "select tr_currency_link from transactions join shopsystem_orders on tr_id = or_tr_id where or_id = {$this->ATTRIBUTES['or_id']}" );

	if( array_key_exists( "ChargeList", $GLOBALS['cfg'] )
	 && is_array( $GLOBALS['cfg']['ChargeList'] )
	 && array_key_exists( $currency, $GLOBALS['cfg']['ChargeList'] ) )
	 	$chargeList = $GLOBALS['cfg']['ChargeList'][$currency];

	$Q_Update = query("
		UPDATE shopsystem_orders
		SET or_charge_list = $chargeList
		WHERE or_id = {$this->ATTRIBUTES['or_id']}
	");
	
	locationRelative($this->ATTRIBUTES['BackURL']);
?>
