<?php 
	$this->param('or_id');
	$this->param('BackURL');
	$this->param('or_authorisation_number');

	$Q_Update = query("
		UPDATE shopsystem_orders
		SET or_authorisation_number = '".escape($this->ATTRIBUTES['or_authorisation_number'])."'
		WHERE or_id = {$this->ATTRIBUTES['or_id']}
	");

	$Q = getRow( "select tr_currency_code, tr_total, or_paid_not_shipped, or_paid from shopsystem_orders join transactions on tr_id = or_tr_id WHERE or_id = {$this->ATTRIBUTES['or_id']}" );
	if( !strlen($Q['or_paid']) && !strlen( $Q['or_paid_not_shipped'] ) )
		$r = new Request( "ShopSystem.MarkPaidNotShipped", array( 'or_id' => $this->ATTRIBUTES['or_id'], 'SendEmail' => true ) );

	$chf = number_format( ss_getExchangeRate( $Q['tr_currency_code'], 'CHF' ) * $Q['tr_total'], 2 );
	$noteStr = "Manually charged, current CHF amount is $chf";

	query( "insert into shopsystem_order_notes (orn_text, orn_timestamp, orn_or_id) values ('$noteStr', NOW(), {$this->ATTRIBUTES['or_id']} )" );

	locationRelative($this->ATTRIBUTES['BackURL']);
?>
