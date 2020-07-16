<?php 
	$this->param('or_id');
	$this->param('BackURL');
	$this->param('rfd_authorisation_number');

	$tr = getField( "select sum(rfd_amount) from shopsystem_refunds where rfd_or_id = {$this->ATTRIBUTES['or_id']} and rfd_pending = true" );

	$noteStr = "Manual refund of $tr done, auth:".escape($this->ATTRIBUTES['rfd_authorisation_number']);
	query( "insert into shopsystem_order_notes (orn_text, orn_timestamp, orn_or_id) values ('$noteStr', NOW(), {$this->ATTRIBUTES['or_id']} )" );

	ss_log_message( "setting manual refund amount:$tr auth:'".escape($this->ATTRIBUTES['rfd_authorisation_number'])."' on or_id {$this->ATTRIBUTES['or_id']}" );

	$Q_Update = query("
		UPDATE shopsystem_refunds
		SET rfd_authorisation_number = '".escape($this->ATTRIBUTES['rfd_authorisation_number'])."',	
			rfd_pending = false
		WHERE rfd_or_id = {$this->ATTRIBUTES['or_id']}
		  and rfd_pending = true
	");


	locationRelative($this->ATTRIBUTES['BackURL']);
?>
