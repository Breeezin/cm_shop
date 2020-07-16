	<input type="hidden" name="Options" value="<? print($data['ProductOptions'][0]['ID']);?>">
	<?	$someInStock = 'false'; if (strlen($data['ProductOptions'][0]['StockAvailable']) >= 0 and (int)$data['ProductOptions'][0]['StockAvailable'] > 0) $someInStock = 'true'; ?>
	<?php
		// check if any of the combo products are out of stock
		$check = getRow("
			SELECT pr_combo FROM shopsystem_products
			WHERE pr_id = {$data['pr_id']}
		");
		if ($check['pr_combo'] >= 1) {
			$Q_Combos = query("
				SELECT pro_stock_available, cpr_qty FROM shopsystem_products, shopsystem_product_extended_options, shopsystem_combo_products
				WHERE cpr_element_pr_id = {$data['pr_id']}
					AND cpr_pr_id = pr_id
					AND pr_id = pro_pr_id
			");
			while ($com=$Q_Combos->fetchRow()) {
				if ($com['pro_stock_available'] == null) {
					// its ok.. not limitied
				} else if ($com['pro_stock_available'] < $com['cpr_qty']) {
					// out of stock
					$someInStock = 'false';	
				}
			}
		}

	ss_paramKey($_REQUEST,'ProductStockLevels',array());
	$_REQUEST['ProductStockLevels'][$data['pr_id']] = $someInStock;
?>
