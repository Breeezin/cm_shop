<?php
	// Load the fields
	require("inc_menuFields.php");	
	foreach (array('Footer Menu Settings') as $fieldTypes) {
		foreach ($menuFields[$fieldTypes] as $name => $values) {
			if ($values[2] == '(nothing)') $values[2] = '';	
			$this->param($name,$values[2]);
			if ($this->ATTRIBUTES[$name] == null) $this->ATTRIBUTES[$name] = $values[2];	
		}
	}
	
	$result = query("
		SELECT as_name, as_menu_name FROM assets
		WHERE as_parent_as_id = {$this->ATTRIBUTES['AST_MENU_FOOTER_ROOT_ASSETID']}
			AND as_appear_in_menus = 1
			AND (as_deleted IS NULL OR as_deleted = 0)
		ORDER BY as_sort_order,as_name					
	");
	
?>
