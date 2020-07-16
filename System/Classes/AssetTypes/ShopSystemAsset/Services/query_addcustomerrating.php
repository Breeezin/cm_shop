<?php

	$this->param('Product');
	
	if ($this->ATTRIBUTES['Product'] == '..') die('Invalid Product');
	if ($this->ATTRIBUTES['Product'] == '') die('Invalid Product');

	if( is_numeric( $this->param('Product') ) )
		$product = getRow("
			SELECT pr_name FROM shopsystem_products
			WHERE pr_id = ".safe($this->ATTRIBUTES['Product'])."
		");
	else
	{
//		header("Location: /index.php");
		die;
	}
	
?>
