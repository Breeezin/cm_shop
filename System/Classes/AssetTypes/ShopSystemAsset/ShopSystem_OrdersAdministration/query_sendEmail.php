<?php

	$this->param('or_id');
	$this->param('BackURL');

	/*$this->fieldSet = new FieldSet(array(
		'tablePrimaryKey'	=>	'or_id',
		'tableName'	=>	'shopsystem_orders',
		'primaryKey'	=>	$this->ATTRIBUTES['or_id'],
	));	*/

	/*$this->fieldSet->addField(new TextField(array(
		'name'			=>	'OrDocketNumber',
		'displayName'	=>	'Pickup Docket Number',
		'required'		=>	true,
		'size'	=>	20,
		'maxLength'	=>	30,
	)));*/

	/*$this->fields = getRow("
		SELECT * FROM shopsystem_orders
		WHERE or_id = ".safe($this->ATTRIBUTES['or_id'])."
	");
	
	$this->fieldSet->loadFieldValues($this->ATTRIBUTES,$this->fields);
	*/

			$Q_Order = getRow("
				SELECT * FROM shopsystem_orders
				WHERE or_id = ".safe($this->ATTRIBUTES['or_id'])."
			");
	
	
?>