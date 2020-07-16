<?php
	$Q_Products = query("
		SELECT * FROM shopsystem_products, shopsystem_product_extended_options
		WHERE pro_pr_id = pr_id
	 		AND pr_deleted IS NULL
		ORDER BY pr_name
	");

?>