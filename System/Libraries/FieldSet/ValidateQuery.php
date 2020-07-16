<?php
	// Set up the error array
	$errors = array();
	$this->param("ValidateFields", array());
	// Validate each field and record any errors reported
	foreach ($this->fields as $field) {
		if (!count($this->ATTRIBUTES['ValidateFields'])) {
			$result = $this->fields[$field->name]->fullValidate($this->tableName,$this->tablePrimaryKey,$this->primaryKey,$this->tableDeleteFlag, $this->tableAssetLink, $this->assetLink);
			$errors = array_merge($errors,$result);
		} else {
			if (array_search($field->name, $this->ATTRIBUTES['ValidateFields']) !== false) {
				$result = $this->fields[$field->name]->fullValidate($this->tableName,$this->tablePrimaryKey,$this->primaryKey,$this->tableDeleteFlag, $this->tableAssetLink, $this->assetLink);
				$errors = array_merge($errors,$result);
			}
		}
		
	}
		
	return $errors;
?>