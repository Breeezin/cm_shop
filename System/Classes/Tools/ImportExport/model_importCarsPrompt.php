<?php
	$this->ATTRIBUTES['DoAction'] = 1;
	if (array_key_exists('DoAction',$this->ATTRIBUTES)) {
		/*$upload_temp = $_FILES['Import']['tmp_name'];
		if (!strlen($upload_temp)) {
			locationRelative('index.php?act=Import.CarsPrompt');	
		}*/
		$upload_temp = ss_withTrailingSlash(dirname($_SERVER['SCRIPT_FILENAME'])).'Custom/ContentStore/ImportExport/TopGear/Import.txt';
		$Q_Cars = ss_ParseTabDelimitedFile($upload_temp,null,false,'Branch	Stock Code	Make	Model	Sub-Model	Year	Odometer	Transmission	Fuel Type	Colour	Body	Type	Engine Size	Number Plate	Price	Empty	Extras');
		//move_uploaded_file($upload_temp,$targetDir.$targetFile);
//		unlink($upload_temp);
		print("<p>The following data has been extracted from your file. Please check for errors and then press the import button at the bottom of the page to import your data.</p>");
		print("<table>");
		$firstTime = false;		
		$headers = array('Branch', 'Stock Code','Make','Model','Sub-Model','Year','Odometer','Transmission','Fuel Type','Colour','Body','Type','Engine Size','Number Plate','Extras','Price');
		print("<tr>");
/*		foreach ($headers as $header) {
			print("<th align=\"left\">".ss_HTMLEditFormat($header)."</th>");
		}*/
		print('<th align="left">Name</th><th align="left">Long</th><th align="left">Price</th><th>Stock Code</th>');
		$firstTime = true;
		$counter = 0;
		$allImportData = array();

		$code = md5(rand());
		
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
				print("</tr>");
				$firstTime = false;	
			}
			$insertData = array();
			
			ss_paramKey($car,'Make','');
			ss_paramKey($car,'Model','');
			ss_paramKey($car,'Sub-Model','');
			ss_paramKey($car,'Year','');
			ss_paramKey($car,'Price','');
			ss_paramKey($car,'Stock Code','');
			ss_paramKey($car,'Extras','');
			$car['Extras'] = str_replace(',',', ',$car['Extras']);
			

			$insertData['pr_name'] = $car['Make'].' '.$car['Model'].' '.$car['Sub-Model'].' '.$car['Year'];
			$insertData['pr_long'] = '';
			$insertData['pro_price'] = str_replace(',','',$car['Price']);
			$insertData['pro_stock_code'] = str_replace(',','',$car['Stock Code']);
						
			
			foreach($headers as $header) {
				if ($header != 'Stock Code' and $header != 'Price') {
					if (array_key_exists($header,$car) and strlen($car[$header])) {
						$insertData['pr_long'] .= '<span class="important_text">'.ss_HTMLEditFormat($header).':</span> '.ss_HTMLEditFormat($car[$header]).'<br>';
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
			
			// Display the values
			print("<tr>");
			print("<td>".(strlen($insertData['pr_name'])?ss_HTMLEditFormat($insertData['pr_name']):'&nbsp')."</td>");
			print("<td>".(strlen($insertData['pr_long'])?ss_HTMLEditFormat($insertData['pr_long']):'&nbsp')."</td>");
			print("<td>".(strlen($insertData['pro_price'])?ss_HTMLEditFormat($insertData['pro_price']):'&nbsp')."</td>");
			print("<td>".(strlen($insertData['pro_stock_code'])?ss_HTMLEditFormat($insertData['pro_stock_code']):'&nbsp')."</td>");
			print("</tr>");

			
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

		print("</table>");	

		print("<p>$counter cars(s) found.</p>");
		print("<form method=\"post\" action=\"index.php?act=Import.Cars\">");
		print("<input type=\"hidden\" name=\"Code\" value=\"".ss_HTMLEditFormat($code)."\">");
		print("<div align=\"center\"><input type=\"submit\" name=\"submit\" value=\"Import\"></div>");
		print("</form>");
	}
?>
