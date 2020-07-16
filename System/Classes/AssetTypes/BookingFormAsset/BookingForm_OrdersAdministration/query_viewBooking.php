<?php 

	requireOnceClass('FieldSet');

	$this->param('bo_id');
	$this->param('tr_id');
	$this->param('as_id');
	
	
	/*$this->display->layout = 'none';
	
	$Q_Order = getRow("SELECT * FROM shopsystem_orders, transactions WHERE or_id = {$this->ATTRIBUTES['or_id']} AND tr_id = or_tr_id");
	$Q_Shop = getRow("SELECT * FROM assets WHERE as_id = {$this->ATTRIBUTES['as_id']}");
	
	$shopSetting = unserialize($Q_Shop['as_serialized']);*/
	$Booking = getRow("
		SELECT * FROM booking_form_bookings, transactions
		WHERE bo_id = ".safe($this->ATTRIBUTES['bo_id'])."
			AND bo_tr_id = tr_id
	");

	$this->fieldSet = new FieldSet(array(
		'tablePrimaryKey'	=>	'tr_id',
		'tableName'	=>	'transactions',
		'primaryKey'	=>	$this->ATTRIBUTES['tr_id'],
	));	

	$this->fieldSet->addField(new MoneyField(array(
		'name'			=>	'tr_total',
		'displayName'	=>	'Amount',
		'required'		=>	TRUE,
		'size'	=>	10,
		'maxLength'	=>	10,
	)));

	$this->fields = getRow("
		SELECT * FROM transactions
		WHERE tr_id = ".safe($this->ATTRIBUTES['tr_id'])."
	");
	
	$this->fieldSet->loadFieldValues($this->ATTRIBUTES,$this->fields);
		
	
?>