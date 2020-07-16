<?php

	// Default some values
	$this->param('BackURL','10');
	
	// query the database
	if (count($this->tableOrderBy)) {
		foreach ($this->tableOrderBy as $customOrder => $description) {
			break;	
		}
	} else {
		$customOrder = $this->tableSortOrderField;
	}
	
	$result = $this->query(array('ForModifySortOrder'=>1,'CustomOrder' => array($customOrder)));

	$totalRows = $this->query(array('ForModifySortOrder'=>1,'CountOnly'=>true));
	
	
?>