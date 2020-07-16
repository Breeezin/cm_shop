<?php

	$GLOBALS['cfg']['debugMode'] = false;

	//if (array_key_exists('DoAction',$this->ATTRIBUTES)) {
		$Q_Products = ss_ParseTabDelimitedFile('Custom/ContentStore/ImportExport/Products.txt');

		print("<p>The following data has been extracted from your file. Please check for errors and then press the import button at the bottom of the page to import your data.</p>");
		print("<table>");
		$firstTime = false;		
		print("<tr>");
		$firstTime = true;
		$counter = 0;
		$allImportData = array();

		$code = md5(rand());
		
		// clear out any old user data
		/*query("
			DELETE FROM import_users
		");*/
		$knownFields = array('Category','Product Name','Stock Code','Price','Short Description','Long Description');
		
		$lastID = null;
		while ($product = $Q_Products->fetchRow()) {
			$product['Options'] = array();
			if ($firstTime) {
				// Load all the custom fields also
				foreach($product as $key => $value) {
					if (array_search($key,$knownFields) !== false) {
						print("<th align=\"left\" style=\"color:red;\">".ss_HTMLEditFormat($key)."</th>");
					} else {
						print("<th align=\"left\">".ss_HTMLEditFormat($key)."</th>");
					}
				}
				print("</tr>");
				$firstTime = false;	
			}

			$insertData = $product;
			
			// Display the values
			print("<tr>");
			foreach($product as $key => $value) {
				if (!is_array($value)) {
					print("<td>".ss_HTMLEditFormat($value)."</td>");
				} else {
					print("<td>");
					ss_DumpVar($value);
					Array("</td>");
				}
			}
			print("</tr>");

			
			// If we dont have a category
			if (!strlen(trim($product['Category'])) and $lastID !== null) {
				$lastProduct = getRow(" 
					SELECT * FROM import_users
					WHERE imu_id = $lastID
				");		
				$data = unserialize($lastProduct['imu_user_data']);
				array_push($data['Options'],$product);
				$serializedData = escape(serialize($data));
				
				$result = query("
					UPDATE import_users 
					SET imu_user_data = '$serializedData'
					WHERE imu_id = $lastID
				");
				unset($data);
				unset($serializedData);
				//$result->free();
				
			} else {
				$id = newPrimaryKey('import_users','imu_id');
				$lastID = $id;
				$data = escape(serialize($insertData));
				$escapedCode = escape($code);
				$result = query("
					INSERT INTO import_users (imu_id,imu_user_data,imu_user_code)
					VALUES ($id,'$data','$escapedCode');
				");
				unset($escapedCode);
				unset($data);
				unset($insertData);

				$counter++;
				
				//$result->free();
			}
			
			//array_push($allImportData,$insertData);
		}

		print("</table>");	

		print("<p>$counter products(s) found.</p>");
		print("<form method=\"post\" action=\"index.php?act=Import.Products\">");
		print("<input type=\"hidden\" name=\"Code\" value=\"".ss_HTMLEditFormat($code)."\">");
		print("<div align=\"center\"><input type=\"submit\" name=\"submit\" value=\"Import\"></div>");
		print("</form>");
	//}
?>