<?php 
	$searchValue = '';
	
	foreach($this->fields as $field) {
		if(array_search($field->name,$this->searchableFields) !== false) {
			$searchValue .= ss_comma($searchValue).escape($field->displayValue($field->value));
		}
	}

	$Q_Update = query("
					UPDATE {$this->tableName}
					SET 
						DaCoSearch = '$searchValue'
					WHERE 
						{$this->tablePrimaryKey} = {$this->primaryKey}
				");
?>