<?

	$this->param('Auth');
	if ($this->atts['Auth'] != '098dfgm23498dfgmn') die('Authentication Error');


	$upload_temp = ss_withTrailingSlash(dirname($_SERVER['SCRIPT_FILENAME'])).'Custom/ContentStore/ImportExport/TopGear/Import.txt';
	$Q_Cars = ss_ParseTabDelimitedFile($upload_temp,null,false,'Stock Code	Make	Model	Sub-Model	Year	Odometer	Transmission	Fuel Type	Colour	Body	Type	Engine Size	Number Plate	Price	Empty	Extras');
	$headers = array('Branch', 'Stock Code','Make','Model','Sub-Model','Year','Odometer','Transmission','Fuel Type','Colour','Body','Type', 'Engine Size','Number Plate','Extras','Price');


	$firstTime = true;
	$counter = 0;
	$allImportData = array();

	$code = md5(rand());

	// Get the shop asset's configuration stuff
	$ShopAsset = getRow("
		SELECT as_id, as_serialized FROM assets
		WHERE as_type LIKE 'ShopSystem'
			AND (as_deleted IS NULL OR as_deleted = 0)
	");
	$shop = $ShopAsset['as_id'];


    //briar put this in to clear out the redundant categories
    //clear all the products later so no harm in doing this?

    $Q_Delete = query("
	    DELETE FROM shopsystem_categories
    ");

    /*
	$Q_Categories = query("
		SELECT ca_id, ca_name FROM shopsystem_categories
		WHERE ca_as_id = $shop
	"); */

	$cats = array();
	/*while ($cat = $Q_Categories->fetchRow()) {
		$cats[$cat['ca_name']] = $cat['ca_id'];
	}   */

	// clear out any old user data
	/*query("
		DELETE FROM import_users
	");*/
	while ($car = $Q_Cars->fetchRow()) {
		if ($firstTime) {
			// Load all the custom fields also
/*				foreach($fields as $field) {
				ss_paramKey($field,"name");
				ss_paramKey($field,"uuid");
				if ($field['uuid'] !== 'Name' and $field['uuid'] !== 'Email' and $field['uuid'] !== 'Password') {
					if (array_key_exists($field['name'],$user)) {
						print("<th align=\"left\">".ss_HTMLEditFormat($field['name'])."</th>");
					}
				}
			}*/
			/*print("</tr>");*/
			$firstTime = false;
		}
		$insertData = array();

		$categoryField = 'Type';

		ss_paramKey($car,'Make','');
		ss_paramKey($car,'Model','');
		ss_paramKey($car,'Sub-Model','');
		ss_paramKey($car,'Year','');
		ss_paramKey($car,'Price','');
		ss_paramKey($car,'Stock Code','');
		ss_paramKey($car,'Extras','');
		ss_paramKey($car,$categoryField,'');
		$car['Extras'] = str_replace(',',', ',$car['Extras']);
		$car['Extras'] = trim($car['Extras'],', ');

		// kludge for 'station wago'
		if ($car['Body'] == 'Station Wago') $car['Body'] = 'Station Wagon';

		if (!array_key_exists($car[$categoryField],$cats)) {
			// if the category doesn't already exist... we just make a new one..
    		$catid = newPrimaryKey('shopsystem_categories','ca_id');
    		$Q_InsertCat = query("
    			INSERT INTO shopsystem_categories
    				(ca_id, ca_name, ca_as_id, ca_parent_ca_id)
    			VALUES
    				($catid, '".escape($car[$categoryField])."', $shop, NULL)
    		");
    		$cats[$car[$categoryField]] = $catid;
        }
		//}


		$insertData['pr_name'] = $car['Make'].' '.$car['Model'].' '.$car['Sub-Model'].' '.$car['Year'];
		$insertData['pr_long'] = '';
		$insertData['pro_price'] = str_replace(',','',$car['Price']);
		$insertData['pro_stock_code'] = str_replace(',','',$car['Stock Code']);
		$insertData['pr_ca_id'] = $cats[$car[$categoryField]];


		foreach($headers as $header) {
			if ($header != 'Stock Code' and $header != 'Price') {
				if (array_key_exists($header,$car) and strlen($car[$header])) {
					if ($header == 'Engine Size') {
						if ($car[$header] < 50) {
							$car[$header] = $car[$header] * 1000;
						}
						$car[$header] .= ' cc';
						$insertData['pr_long'] .= '<span class="important_text">'.ss_HTMLEditFormat($header).':</span> '.ss_HTMLEditFormat($car[$header]).'<br>';
					} else {
						$insertData['pr_long'] .= '<span class="important_text">'.ss_HTMLEditFormat($header).':</span> '.ss_HTMLEditFormat($car[$header]).'<br>';
					}
				}
			}
		}

		/*
		do that later
		if (strlen($insertData['us_email']) and strlen($this->ATTRIBUTES['UserUpdate'])) {
			$aUser = getRow("SELECT us_id FROM users WHERE us_email LIKE '{$insertData['us_email']}'");
			if (strlen($aUser['us_id'])) {
				$Q_aUserGroups = query("SElELCT ");
			}
		} else {
			$insertData['user_groups'] = $this->ATTRIBUTES['user_groups'];
		}
		*/
		$insertData['DoAction'] = 'Yes';

/*		// Display the values
		print("<tr>");
		print("<td>".(strlen($insertData['pr_name'])?ss_HTMLEditFormat($insertData['pr_name']):'&nbsp')."</td>");
		print("<td>".(strlen($insertData['pr_long'])?ss_HTMLEditFormat($insertData['pr_long']):'&nbsp')."</td>");
		print("<td>".(strlen($insertData['pro_price'])?ss_HTMLEditFormat($insertData['pro_price']):'&nbsp')."</td>");
		print("<td>".(strlen($insertData['pro_stock_code'])?ss_HTMLEditFormat($insertData['pro_stock_code']):'&nbsp')."</td>");
		print("</tr>");*/


		$counter++;

		$id = newPrimaryKey('import_users','imu_id');
		$data = escape(serialize($insertData));
		$escapedCode = escape($code);
		query("
			INSERT INTO import_users (imu_id,imu_user_data,imu_user_code)
			VALUES ($id,'$data','$escapedCode')
		");

		//array_push($allImportData,$insertData);
	}


	$counter = 0;

	$Q_Delete = query("
		DELETE FROM shopsystem_product_extended_options
	");
	$Q_Delete = query("
		DELETE FROM shopsystem_products
	");

	// Get the data
	$Q_Import = query("
		SELECT * FROM import_users
		WHERE imu_user_code LIKE '".escape($code)."'
	");

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

    //briar is going to put this here because permissions on the productimages folder are getting bunged
    /*$folder = ss_secretStoreForAsset($shop,'ProductImages/');
    chmod ($folder, 0755);*/

	//print("<table>");
	while ($row = $Q_Import->fetchRow()) {
		$importData = unserialize($row['imu_user_data']);

		//$id = newPrimaryKey('shopsystem_products','pr_id');
		$id = $importData['pro_stock_code'];

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
				($id, '".escape($importData['pr_name'])."', '".escape($importData['pr_long'])."', $shop, {$importData['pr_ca_id']}, {$images[1]}, {$images[2]}, {$images[3]}, {$images[4]});
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
	//print("</table>");

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
		WHERE imu_user_code LIKE '".escape($code)."'
	");

	$this->display->title = 'Import Complete';

?>
