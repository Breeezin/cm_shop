<?php
	$this->param('ImportUsersList');
	$this->param('Code');
	
	$counter = 0;
	
	/* Testing.. delete out the rubbish */
	/*$del = query("DELETE FROM shopsystem_categories WHERE ca_id >= 14");
	$del = query("DELETE FROM shopsystem_product_extended_options WHERE pro_pr_id >= 14");
	$del = query("DELETE FROM shopsystem_products WHERE pr_id >= 14");*/

	// Get the data
	$Q_Import = query("
		SELECT * FROM import_users
		WHERE imu_id IN (".$this->ATTRIBUTES['ImportUsersList'].")
			AND imu_user_code LIKE '".escape($this->ATTRIBUTES['Code'])."'
	");

	// Get the shop asset's configuration stuff
	$ShopAsset = getRow("
		SELECT as_id, as_serialized FROM assets
		WHERE as_type LIKE 'ShopSystem'
			AND (as_deleted IS NULL OR as_deleted = 0)
	");
	$shop = $ShopAsset['as_id'];
	$assetDetails = unserialize($ShopAsset['as_serialized']);
	$attributes = unserialize($assetDetails['AST_SHOPSYSTEM_ATTRIBUTES']);
	$options = unserialize($assetDetails['AST_SHOPSYSTEM_PRODUCT_OPTIONS']);

	// Dump some interesting values
	ss_DumpVarHide($attributes,'attributes');
	ss_DumpVarHide($options,'options');

	// Get a product admin object
	requireOnceClass("ShopSystem_ProductsAdministration");
	$productsAdmin = new ShopSystem_ProductsAdministration($shop);
	
	$errorMessages = '';
	$errorCount = 0;
	$lastProductID = null;
	
	print("<table>");
	while ($row = $Q_Import->fetchRow()) {
		$importData = unserialize($row['imu_user_data']);
		$initData = $importData;
		// Display the values
		ss_DumpVarHide($importData,'before');
		
		// Param some basics
		ss_paramKey($importData,'Category','');
		ss_paramKey($importData,'Product Name','');
		ss_paramKey($importData,'Long Description','');
		ss_paramKey($importData,'Long Description HTML','');
		ss_paramKey($importData,'Short Description','');
		ss_paramKey($importData,'Keywords','');
		ss_paramKey($importData,'Stock Code','');
		ss_paramKey($importData,'Price','');
		ss_paramKey($importData,'Special Price','');
		ss_paramKey($importData,'Member Price','');
		ss_paramKey($importData,'RRP','');
		ss_paramKey($importData,'Donation','0');
		ss_paramKey($importData,'Featured','0');
		ss_paramKey($importData,'Discount Group',null);

		// First.. check for the category
		$categoryID = null;
		if (strlen($importData['Category'])) {
			$categoryPath = ListToArray($importData['Category'],'>');
			$parent = null;
			foreach ($categoryPath as $category) {
				$category = trim($category);
				
				// Search for the category
				$Q_Cat = query("
					SELECT * FROM shopsystem_categories
					WHERE ca_as_id = $shop
						AND ca_name = '".escape($category)."'
						AND ca_parent_ca_id ".($parent === null?'IS NULL':'= '.$parent)."
				");
				if ($Q_Cat->numRows()) {
					// Found it.. just move on to the next category item
					$cat = $Q_Cat->fetchRow();
					$parent = $cat['ca_id'];		
				} else {
					// Didn't find it, insert a category
					$id = newPrimaryKey('shopsystem_categories','ca_id',1);
					$insert = query("
						INSERT INTO shopsystem_categories
							(ca_id, ca_name, ca_parent_ca_id, ca_as_id)
						VALUES
							($id, '".escape($category)."', ".($parent === null?'NULL':$parent).", $shop)
					");
					$parent = $id;
				}
			}
			$categoryID = $parent;
		}
		
		// Now check for the discount group
		$discountGroup = null;
		if (strlen($importData['Discount Group'])) {
			$Q_DiscountGroup = query("
				SELECT * FROM shopsystem_discount_groups
				WHERE dig_as_id = $shop
					AND dig_name LIKE '".escape($importData['Discount Group'])."'
			");
			if ($Q_DiscountGroup->numRows()) {
				$discountGroup = $Q_DiscountGroup->fetchRow();
				$discountGroup = $discountGroup['dig_id'];	
			}
		}
		
		// Feed in some of the basic values
		$insertData['pr_ca_id'] =	$categoryID;
		$insertData['as_id'] = $shop;
		$insertData['pr_name'] = $importData['Product Name'];
		if (strlen($importData['Long Description HTML'])) {
			$insertData['pr_long'] = $importData['Long Description HTML'];
		} else {
			$insertData['pr_long'] = ss_HTMLEditFormat($importData['Long Description']);
		}
		$insertData['pr_short'] = ss_HTMLEditFormat($importData['Short Description']);
		$insertData['pr_keywords'] = $importData['Keywords'];
		$insertData['pr_donation'] = $importData['Donation'];
		$insertData['pr_featured'] = $importData['Featured'];
		$insertData['pr_dig_id'] = $discountGroup;
	
		// Param the image fields
		for ($i=1;$i<=ss_optionExists('Shop Product Images');$i++) {
			$_REQUEST["PrImage{$i}Thumb_Action"] = null;
			$_REQUEST["PrImage{$i}Normal_Action"] = null;
			$_REQUEST["PrImage{$i}Large_Action"] = null;

			foreach(array('Thumb','Normal','Large') as $type) {
				if (array_key_exists("Image $i $type",$importData)) {
					// 1) set the action and empty original image
					$_REQUEST["PrImage{$i}{$type}_Action"] = 'None';
					$_REQUEST["PrImage{$i}{$type}_Original"] = '';
					
					// 2) copy the file into the incoming folder
					//if (array_key_exists("Image $i $type", $importData)) {
					if (strlen($importData["Image $i $type"])) {
						
						if (file_exists(expandPath('Custom/ContentStore/ImportExport/ProductImages/'.$importData["Image $i $type"]))) {
							
							$extension = array_pop(explode('.', $importData["Image $i $type"]));
							$result = new Request("UID.Get");
							// 3) set the file name into pr_image1_normal					
							$insertData["PrImage{$i}{$type}"] = md5($result->value).".".$extension;
							$_REQUEST["PrImage{$i}{$type}_Action"] = 'Upload';
							
							copy(expandPath('Custom/ContentStore/ImportExport/ProductImages/'.$importData["Image $i $type"]),expandPath('Custom/Cache/Incoming/'.$insertData["PrImage{$i}{$type}"]));
						} else {
							$errorCount++;
							$errorMessages .= "<p>Image missing for {$insertData['pr_name']} : ".$importData["Image $i $type"]." - ".expandPath('Custom/ContentStore/ImportExport/ProductImages/'.$importData["Image $i $type"])."</p>";
						}
					//}
					} 
					//ss_DumpVarHide($importData, "Image $i $type");
					
					// 4) Profit!
				}
			}
		}
		
		
		// Prepare some arrays for our option details
		$insertData['ExtendedOptions'] = array();
		$insertData['ExtendedOptions_StockCode'] = array();
		$insertData['ExtendedOptions_Price'] = array();
		$insertData['ExtendedOptions_SpecialPrice'] = array();
		$insertData['ExtendedOptions_MemberPrice'] = array();
		$insertData['ExtendedOptions_RRPrice'] = array();
		$insertData['ExtendedOptions_IsMain'] = array();
		$insertData['ExtendedOptions_FrCode'] = array();
		//$insertData['ExtendedOptions_FrType'] = array();
		$insertData['ExtendedOptions_HasCodes'] = null;
		
		// Insert the main option values
		$mainOptionPrice = $importData['Price'];
		
		array_push($insertData['ExtendedOptions'],null);
		array_push($insertData['ExtendedOptions_StockCode'],$importData['Stock Code']);
		array_push($insertData['ExtendedOptions_Price'],$importData['Price']);
		array_push($insertData['ExtendedOptions_SpecialPrice'],$importData['Special Price']);
		array_push($insertData['ExtendedOptions_MemberPrice'],$importData['Member Price']);
		array_push($insertData['ExtendedOptions_RRPrice'],$importData['RRP']);
		
		array_push($insertData['ExtendedOptions_IsMain'],1);

		// ignore the other options for now..

		
		// Find attribute values
		foreach ($attributes as $attribute) {
			ss_paramKey($attribute,'name','');	
			if (array_key_exists('Attr: '.$attribute['name'],$importData)) {
				if (count($attribute['options'])) {
					// Find the uuid if there's an array of options
					foreach($attribute['options'] as $attributeOption) {
						if ($attributeOption['name'] == $importData['Attr: '.$attribute['name']]) {
							$insertData['Pr'.$attribute['uuid']] = $attributeOption['uuid'];
						}						
					}	
				} else {
					// Otherwise insert the raw value
					$insertData['Pr'.$attribute['uuid']] = $importData['Attr: '.$attribute['name']];
				}
			}
		}

		// Loop through all the options
		foreach ($importData['Options'] as $optionDef) {

			// Default some values
			ss_paramKey($optionDef,'Stock Code','');
			ss_paramKey($optionDef,'Price','');
			ss_paramKey($optionDef,'Special Price','');
			ss_paramKey($optionDef,'Member Price','');
			ss_paramKey($optionDef,'RRP','');			


			// Find option values and construct a key for the option combinations
			$key = '';
			foreach ($options as $optionKey => $option) {
				ss_paramKey($option,'name','');
				if (array_key_exists('Opt: '.$option['name'],$optionDef)) {
					if (strlen(trim($optionDef['Opt: '.$option['name']]))) {
//					if (count($option['options'])) {
						// Find the uuid if there's an array of options
						$addedToKey = false;
						foreach($option['options'] as $optionOption) {
							//echo trim($optionDef['Opt: '.$option['name']]).' - '.$optionOption['name'].'<br>';

							if ($optionOption['name'] == trim($optionDef['Opt: '.$option['name']])) {
								$key .= ",{$option['uuid']}={$optionOption['uuid']}";
								$addedToKey = true;
								break;
							}
						}
						//echo $key .'<br>';
						if (!$addedToKey) {
							// need to add a new option
							$uuidresult = new Request("UID.Get",array('Count' => 5));
							$uuid = array_pop($uuidresult->value);
							$newOption = array(
								'name'	=>	trim($optionDef['Opt: '.$option['name']]),
								'uuid'	=>	$uuid,
							);
							
							$Q_InsertOption = query("
								INSERT INTO select_field_options
									(sfo_parent_uuid, sfo_uuid, sfo_value)
								VALUES
									('".$option['uuid']."', '".$uuid."', '".escape(trim($optionDef['Opt: '.$option['name']]))."')
							
							");
							array_push($options[$optionKey]['options'],$newOption);
							$key .= ",{$option['uuid']}={$uuid}";
						}
					}
//					}
				}
			}

			// Add them into the options
			array_push($insertData['ExtendedOptions'],$key);
			array_push($insertData['ExtendedOptions_StockCode'],$optionDef['Stock Code']);
			array_push($insertData['ExtendedOptions_Price'],$optionDef['Price']);
			array_push($insertData['ExtendedOptions_SpecialPrice'],$optionDef['Special Price']);
			array_push($insertData['ExtendedOptions_MemberPrice'],$optionDef['Member Price']);
			array_push($insertData['ExtendedOptions_RRPrice'],$optionDef['RRP']);
			array_push($insertData['ExtendedOptions_IsMain'],"");
			// If this is the main option..
			/*if ($optionDef['Price'] == $insertData['ExtendedOptions_Price'][0]) {
				// ..then copy in the values
				$insertData['ExtendedOptions'][0] = array_pop($insertData['ExtendedOptions']);
				$insertData['ExtendedOptions_StockCode'][0] = array_pop($insertData['ExtendedOptions_StockCode']);
				$insertData['ExtendedOptions_Price'][0] = array_pop($insertData['ExtendedOptions_Price']);
				$insertData['ExtendedOptions_SpecialPrice'][0] = array_pop($insertData['ExtendedOptions_SpecialPrice']);
				$insertData['ExtendedOptions_MemberPrice'][0] = array_pop($insertData['ExtendedOptions_MemberPrice']);
				$insertData['ExtendedOptions_RRPrice'][0] = array_pop($insertData['ExtendedOptions_RRPrice']);
				$insertData['ExtendedOptions_FrCode'][0] = array_pop($insertData['ExtendedOptions_FrCode']);
				$insertData['ExtendedOptions_IsMain'][0] = $insertData['ExtendedOptions'][0];
			}*/
			
			
		}
		/*
		// Check we found a main in the options
		if (count($importData['Options'])) {
			if ($insertData['ExtendedOptions_IsMain'][0] == null) {
				// If not.. grab the last option and set it as main
				$insertData['ExtendedOptions'][0] = array_pop($insertData['ExtendedOptions']);
				$insertData['ExtendedOptions_StockCode'][0] = array_pop($insertData['ExtendedOptions_StockCode']);
				$insertData['ExtendedOptions_Price'][0] = array_pop($insertData['ExtendedOptions_Price']);
				$insertData['ExtendedOptions_SpecialPrice'][0] = array_pop($insertData['ExtendedOptions_SpecialPrice']);
				$insertData['ExtendedOptions_MemberPrice'][0] = array_pop($insertData['ExtendedOptions_MemberPrice']);
				$insertData['ExtendedOptions_RRPrice'][0] = array_pop($insertData['ExtendedOptions_RRPrice']);
				$insertData['ExtendedOptions_FrCode'][0] = array_pop($insertData['ExtendedOptions_FrCode']);
				$insertData['ExtendedOptions_IsMain'][0] = $insertData['ExtendedOptions'][0];
			}
		}
		*/
        if (count($importData['Options'])) {
			$mainUUID = null;
			$key = '';
			$optionDef = $importData;
			foreach ($options as $optionKey => $option) {
				ss_paramKey($option,'name','');
				if (array_key_exists('Opt: '.$option['name'],$optionDef)) {
					if (strlen(trim($optionDef['Opt: '.$option['name']]))) {
						// Find the uuid if there's an array of options
						$addedToKey = false;
						foreach($option['options'] as $optionOption) {
							if ($optionOption['name'] == $optionDef['Opt: '.$option['name']]) {
								$key .= ",{$option['uuid']}={$optionOption['uuid']}";
								$mainUUID .= $optionOption['uuid'];
								$addedToKey = true;
								break;
							}
						}
						if (!$addedToKey) {
							// need to add a new option
							$uuidresult = new Request("UID.Get",array('Count' => 5));
							$uuid = array_pop($uuidresult->value);
							$newOption = array(
								'name'	=>	trim($optionDef['Opt: '.$option['name']]),
								'uuid'	=>	$uuid,
							);
							
							$Q_InsertOption = query("
								INSERT INTO select_field_options
									(sfo_parent_uuid, sfo_uuid, sfo_value)
								VALUES
									('".$option['uuid']."', '".$uuid."', '".escape(trim($optionDef['Opt: '.$option['name']]))."')
							
							");
							array_push($options[$optionKey]['options'],$newOption);
							$key .= ",{$option['uuid']}={$uuid}";
						}
						
					}	
				}
			}
			// Add them into the options			
			$insertData['ExtendedOptions'][0] = $key;
			$insertData['ExtendedOptions_IsMain'][0] = $key;
			
			
		}
		// Preview our data
		ss_DumpVarHide($insertData, 'insertData after');
		
		// Load the values
		$productsAdmin->primaryKey = null;
		$productsAdmin->ATTRIBUTES = $insertData;
		$productsAdmin->loadFieldValuesFromForm($insertData);	
		
		// Insert the new product
		$errors = $productsAdmin->insert();	
		
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
	
	// save any updated options just in case
	$assetDetails['AST_SHOPSYSTEM_PRODUCT_OPTIONS']	= serialize($options);
	$shopCereal = serialize($assetDetails);
	$ShopAsset = getRow("
		UPDATE assets
		SET as_serialized = '".escape($shopCereal)."'
		WHERE as_id = $shop
	");
	
	// Delete the rubbish
	$res = query("
		DELETE FROM import_users
		WHERE imu_id IN (".$this->ATTRIBUTES['ImportUsersList'].")
			AND imu_user_code LIKE '".escape($this->ATTRIBUTES['Code'])."'
	"); 
	
?>
