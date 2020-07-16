<?php
	//ss_DumpVarDie($this);
	
	if (!array_key_exists('noReturn',$this->ATTRIBUTES)) {
		$this->param('BackURL');
	}

	startTransaction();
	

	// Create a delete condition SQL	
	$setconditionSQL = '0=1';
	if (array_key_exists($this->tablePrimaryKey,$this->ATTRIBUTES) && strlen($this->ATTRIBUTES[$this->tablePrimaryKey])) {
		$setconditionSQL = "$this->tablePrimaryKey IN (".safe($this->ATTRIBUTES[$this->tablePrimaryKey]).")";
	} else if ($this->parentTable != NULL && array_key_exists($this->parentTable->linkField,$this->ATTRIBUTES)) {
		$setconditionSQL = "{$this->parentTable->linkField} IN (".safe($this->ATTRIBUTES[$this->parentTable->linkField]).")";
	}
	// Create a delete condition SQL	
	$unsetconditionSQL = '0=1';
	if (array_key_exists('Un'.$this->tablePrimaryKey,$this->ATTRIBUTES) && strlen($this->ATTRIBUTES['Un'.$this->tablePrimaryKey])) {
		$unsetconditionSQL = "$this->tablePrimaryKey IN (".safe($this->ATTRIBUTES['Un'.$this->tablePrimaryKey]).")";
	}

	// Perform the reset appearsInMenu	
	$result = query("
		UPDATE $this->tableName
		SET ca_appears_in_menu = NULL
		WHERE $unsetconditionSQL
	"); 
	// Perform the reset appearsInMenu	
	$result = query("
		UPDATE $this->tableName
		SET ca_appears_in_menu = 1
		WHERE $setconditionSQL
	"); 	


	commit();
	
	// Return to the list of records
	if (!array_key_exists('noReturn',$this->ATTRIBUTES)) {
		location($this->ATTRIBUTES['BackURL']);
	}	

?>
