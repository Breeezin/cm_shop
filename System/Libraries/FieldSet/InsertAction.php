<?php
	global $sql;

	// Validate the data for each field
	$errors = $this->validate();
	
	// Insert if no errors messages when validating data
	if (count($errors) == 0) {
	
		// Construct the SQL
		$insertFields = '';
		$insertValues = '';
		foreach ($this->fields as $field) {
			$insertSQLField = $this->fields[$field->name]->insertSQLField();
			$insertSQLValue = $this->fields[$field->name]->insertSQLValue();			

			if( strlen($insertSQLValue) && ($insertSQLValue != 'NULL' ) )
			{
				$insertFields .= (strlen($insertSQLField)?', ':'').$insertSQLField;
				$insertValues .= (strlen($insertSQLValue)?', ':'').$insertSQLValue;
			}
			//print("$field->name  $insertSQLValue<br>");
		}
		
		
		// Add parent link field if this table has a parent		
		if ($this->parentTable != NULL) {
			$insertFields .= ', '.$this->parentTable->linkField;
			if ($this->parentKey != NULL and strlen($this->parentKey) and ($this->parentKey != 'NULL')) {
				$insertValues .= ', '.$this->parentKey;
				//$insertFields .= ', '.$this->parentTable->linkField;  WTF?
			} else {
				$insertValues .= ', NULL';
			}
		}
		
		/*
		if ($this->parentTable != NULL) {
			$insertFields .= ', '.$this->parentTable->linkField;
			if (array_key_exists($this->parentTable->linkField,$this->ATTRIBUTES) &&
				strlen($this->ATTRIBUTES[$this->parentTable->linkField])) {
				$insertValues .= ', '.$this->ATTRIBUTES[$this->parentTable->linkField];
			} else {
				$insertValues .= ', NULL';
			}
		}*/
		
		
			
		// Lock the table	
		startTransaction();	

		// Get a new PrimaryKey
		if ($this->primaryKey === null) {
			if ($this->tablePrimaryMinValue != null) 
				$this->primaryKey = newPrimaryKeyWithMin($this->tableName,$this->tablePrimaryKey, $this->tablePrimaryMinValue);
			else
				$this->primaryKey = newPrimaryKey($this->tableName,$this->tablePrimaryKey);
		}

		if ($this->tableTimeStamp !== null) {
			$insertFields .= ', '.$this->tableTimeStamp;
			$insertValues .= ', NOW()';
		}
		
		if ($this->tableAssetLink != null AND $this->assetLink != null) {
			$insertFields .= ', '.$this->tableAssetLink;
			$insertValues .= ', '.$this->assetLink;
		}
		
		ss_log_message( "InsertAction: \$this follows" );
		ss_log_message_r(  'Log:'.__FILE__.':'.__LINE__, $this );

		// Insert the fields
		$result = $sql->query("
			INSERT INTO $this->tableName ($this->tablePrimaryKey $insertFields)
			VALUES ($this->primaryKey $insertValues)
		");

		// Now handle the special fields.. e.g MultiSelectField
		foreach ($this->fields as $field) {
			$this->fields[$field->name]->specialInsert();
		}
		
		// Unlock the table		
		commit();

	}
	
	return $errors;
	
?>
