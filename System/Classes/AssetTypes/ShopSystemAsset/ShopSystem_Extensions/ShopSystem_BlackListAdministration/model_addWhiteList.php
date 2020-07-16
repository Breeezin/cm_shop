<?php 
	$this->param('or_id');
	$this->param('BackURL');

	//ss_DumpVarDie($this->ATTRIBUTES);
	$userRow = getRow("
		SELECT or_us_id, or_tr_id FROM shopsystem_orders 
		WHERE or_id = {$this->ATTRIBUTES['or_id']}
	");

	query( "Update users set us_bl_id = -1 where us_id = {$userRow['or_us_id']}" );
	query( "Update transactions set tr_fraud_score = 0, tr_fraud = '' where tr_id = {$userRow['or_tr_id']}" );

	locationRelative($this->ATTRIBUTES['BackURL']);
?>
