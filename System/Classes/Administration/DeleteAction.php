<?php
	//ss_DumpVarDie($this);
	if (!array_key_exists('noReturn',$this->ATTRIBUTES)) {
		$this->param('BackURL');
	}

	startTransaction();
	
	// Check if this table has child tables. If it does,
	// then we need to delete the rows from child tables also
	if (count($this->children) > 0) {

		// Get a query of ids if we need to
		if ($this->parentTable != NULL && array_key_exists($this->parentTable->linkField,$this->ATTRIBUTES)) {
			$result = query("
				SELECT $this->tablePrimaryKey FROM $this->tableName
				WHERE {$this->parentTable->linkField} IN (".safe($this->ATTRIBUTES[$this->parentTable->linkField]).")
			");
			// Create list
			$list = ''; $comma = '';
			while($row = $result->fetchRow()) {
				$list .= $comma.$row[$this->tablePrimaryKey];
				$comma = ',';
			}
			$this->ATTRIBUTES[$this->tablePrimaryKey] = $list;
		}
		// Delete the rows
		if (strlen($this->ATTRIBUTES[$this->tablePrimaryKey])) {
			foreach ($this->children as $child) {
				$result = new Request("{$child->prefix}Administration.Delete",array(
					$child->linkField	=>	$this->ATTRIBUTES[$this->tablePrimaryKey],
					'noReturn'			=>  1,
				));
			}
		}

	}

	// Create a delete condition SQL	
	$deleteConditionSQL = '0=1';
	if (array_key_exists($this->tablePrimaryKey,$this->ATTRIBUTES) && strlen($this->ATTRIBUTES[$this->tablePrimaryKey])) {
		$deleteConditionSQL = "$this->tablePrimaryKey IN (".safe($this->ATTRIBUTES[$this->tablePrimaryKey]).")";
	} else if ($this->parentTable != NULL && array_key_exists($this->parentTable->linkField,$this->ATTRIBUTES)) {
		$deleteConditionSQL = "{$this->parentTable->linkField} IN (".safe($this->ATTRIBUTES[$this->parentTable->linkField]).")";
	}

	// Perform the delete
	if ($this->tableDeleteFlag != NULL) {
		$result = query("
			UPDATE $this->tableName
			SET $this->tableDeleteFlag = 1
			WHERE $deleteConditionSQL
		"); 
	} else {
		// Get a list of ids to delete
		$Q_IDsToDelete = query("
			SELECT $this->tablePrimaryKey FROM $this->tableName
			WHERE $deleteConditionSQL
		");
		// Now handle the special fields.. e.g MultiSelectField
		while ($row = $Q_IDsToDelete->fetchRow()) {
			$this->primaryKey = $row[$this->tablePrimaryKey];
			foreach ($this->fields as $field) {
				$this->fields[$field->name]->delete();
			}
			
			// Delete from the additional linked tables
			foreach($this->linkedTables as $linkedTable) {
				$Q_DeleteLinkedItems = query("
					DELETE FROM {$linkedTable->tableName}
					WHERE {$linkedTable->ourKey} = {$this->primaryKey}
				");
			}
			
		}

		// Delete the row	
		$result = query("
			DELETE FROM $this->tableName
			WHERE $deleteConditionSQL
		"); 
	}

	commit();
	
	// Return to the list of records
	if (!array_key_exists('noReturn',$this->ATTRIBUTES)) {
		location($this->ATTRIBUTES['BackURL']);
	}	

?>
