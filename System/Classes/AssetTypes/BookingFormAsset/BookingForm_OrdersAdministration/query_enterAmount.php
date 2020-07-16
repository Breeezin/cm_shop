<?php
	requireOnceClass('FieldSet');
	
	$this->param('tr_id');
	
	$this->fieldSet = new FieldSet(array(
		'tablePrimaryKey'	=>	'tr_id',
		'tableName'	=>	'transactions',
		'primaryKey'	=>	$this->ATTRIBUTES['tr_id'],
	));	

	$this->fieldSet->addField(new MoneyField(array(
		'name'			=>	'tr_total',
		'displayName'	=>	'Amount',
		'required'		=>	TRUE,
		'size'	=>	20,
		'maxLength'	=>	10,
	)));

	$this->fields = getRow("
		SELECT * FROM transactions
		WHERE tr_id = ".safe($this->ATTRIBUTES['tr_id'])."
	");
	
	$this->fieldSet->loadFieldValues($this->ATTRIBUTES,$this->fields);
	
?>