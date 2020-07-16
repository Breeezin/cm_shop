<?php
	$this->param('ImportCarsList');
	$this->param('Code');
	
	$counter = 0;
	
	/* Testing.. delete out the rubbish */
	/*$del = query("DELETE FROM shopsystem_categories WHERE ca_id >= 14");
	$del = query("DELETE FROM shopsystem_product_extended_options WHERE pro_pr_id >= 14");
	$del = query("DELETE FROM shopsystem_products WHERE pr_id >= 14");*/

	// Get the data
	$Q_Import = query("
		SELECT * FROM import_users
		WHERE imu_id IN (".$this->ATTRIBUTES['ImportCarsList'].")
			AND imu_user_code LIKE '".escape($this->ATTRIBUTES['Code'])."'
	");

	// Get the shop asset's configuration stuff
	$ShopAsset = getRow("
		SELECT as_id, as_serialized FROM assets
		WHERE as_type LIKE 'ShopSystem'
			AND (as_deleted IS NULL OR as_deleted = 0)
	");
	$shop = $ShopAsset['as_id'];
	/*$assetDetails = unserialize($ShopAsset['as_serialized']);
	$attributes = unserialize($assetDetails['AST_SHOPSYSTEM_ATTRIBUTES']);
	$options = unserialize($assetDetails['AST_SHOPSYSTEM_PRODUCT_OPTIONS']);

	// Dump some interesting values
	ss_DumpVarHide($attributes,'attributes');
	ss_DumpVarHide($options,'options');

	// Get a product admin object
	requireOnceClass("ShopSystem_ProductsAdministration");
	$productsAdmin = new ShopSystem_ProductsAdministration($shop);	*/
	
	$errorMessages = '';
	$errorCount = 0;
	$lastProductID = null;
	
	print("<table>");
	while ($row = $Q_Import->fetchRow()) {
		$importData = unserialize($row['imu_user_data']);
		
		$id = newPrimaryKey('shopsystem_products','pr_id');
		
		$images = array();
		for ($i = 1; $i <= 4; $i++) {
			$images[$i] = 'NULL';
			$imageName = 'W'.sprintf("%05d",$importData['pro_stock_code']).$i.'.jpg';
			$imageFile = 'Custom/ContentStore/ImportExport/TopGear/'.$imageName;
			//print $importData['pro_stock_code'].($imageFile).'<br>';
			if (file_exists($imageFile)) {
				copy($imageFile,ss_secretStoreForAsset($shop,'ProductImages/').$imageName);
				$images[$i] = "'".escape($imageName)."'";
			}
			
		}
		
		$Q_InsertProduct = query("
			INSERT INTO shopsystem_products
				(pr_id, pr_name, pr_long, pr_as_id, pr_ca_id, pr_image1_large, pr_image2_large, pr_image3_large, pr_image4_large)
			VALUES
				($id, '".escape($importData['pr_name'])."', '".escape($importData['pr_long'])."', $shop, 2, {$images[1]}, {$images[2]}, {$images[3]}, {$images[4]});
		");
		$exid = newPrimaryKey('shopsystem_product_extended_options','pro_id');
		$Q_InsertProductOption = query("
			INSERT INTO shopsystem_product_extended_options
				(pro_id, pro_pr_id, pro_stock_code, pro_price, pro_is_main)
			VALUES
				($exid, $id, '".escape($importData['pro_stock_code'])."', '".escape($importData['pro_price'])."', 1)
		");
		
		$errors = array();
		// Report any errors
		if (count($errors)) {
			$errorCount++;
			$errorMessages .= "<p>Could not add {$insertData['pr_name']} :";
			foreach($errors as $errorList) {
				foreach($errorList as $error) {
					$errorMessages .= "<li>".$error."</li>";
				}
			}
			$errorMessages .= "</p>";
		}		
		
	}
	print("</table>");
	
	/*// save any updated options just in case
	$assetDetails['AST_SHOPSYSTEM_PRODUCT_OPTIONS']	= serialize($options);
	$shopCereal = serialize($assetDetails);
	$ShopAsset = getRow("
		UPDATE assets
		SET as_serialized = '".escape($shopCereal)."'
		WHERE as_id = $shop
	");*/
	
	// Delete the rubbish
	$res = query("
		DELETE FROM import_users
		WHERE imu_id IN (".$this->ATTRIBUTES['ImportCarsList'].")
			AND imu_user_code LIKE '".escape($this->ATTRIBUTES['Code'])."'
	"); 
	
?>