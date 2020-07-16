<?php

	$result = new Request("Security.Sudo",array('Action'=>'Start'));
	$allCategoriesResult = new Request("shopsystem_categories.QueryAll",array('as_id'	=>	$asset->getID()));
	$Q_Categories = $allCategoriesResult->value;
	$result = new Request("Security.Sudo",array('Action'=>'Finish'));	

	$error = '';
	
?>