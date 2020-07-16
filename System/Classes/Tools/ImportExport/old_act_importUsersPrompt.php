<?php
	if (array_key_exists('DoAction',$this->ATTRIBUTES)) {
		$this->param('user_groups',array());
		$this->param('ImportUpdate','');
		$upload_temp = $_FILES['Import']['tmp_name'];
		if (!strlen($upload_temp)) {
			locationRelative('index.php?act=Import.UsersPrompt');	
		}
		$Q_Users = ss_ParseTabDelimitedFile($upload_temp);
		$targetDir = ss_withTrailingSlash(dirname($_SERVER['SCRIPT_FILENAME'])).'Custom/Cache/Incoming/';
		$targetFile = 'UserImport'.md5(rand());
		move_uploaded_file($upload_temp,$targetDir.$targetFile);
//		unlink($upload_temp);
		print("<p>The following data has been extracted from your file. Please check for errors and then press the import button at the bottom of the page to import your data.</p>");
		print("<table>");
		$firstTime = false;		
		$headers = array('First Name','Last Name','Email','Password');
		print("<tr>");
		foreach ($headers as $header) {
			print("<th align=\"left\">".ss_HTMLEditFormat($header)."</th>");
		}
		$firstTime = true;
		$counter = 0;
		$allImportData = array();

		$code = md5(rand());
		
		// clear out any old user data
		/*query("
			DELETE FROM import_users
		");*/
		while ($user = $Q_Users->fetchRow()) {
			if ($firstTime) {
				// Load all the custom fields also
				foreach($fields as $field) {
					ss_paramKey($field,"name");
					ss_paramKey($field,"uuid");
					if ($field['uuid'] !== 'Name' and $field['uuid'] !== 'Email' and $field['uuid'] !== 'Password') {
						if (array_key_exists($field['name'],$user)) {
							print("<th align=\"left\">".ss_HTMLEditFormat($field['name'])."</th>");
						}
					}
				}
				print("</tr>");
				$firstTime = false;	
			}
			$insertData = array(
				'us_html_email'	=>	1,
				'us_name'	=>	array(
					'first_name'	=>	'',
					'last_name'	=>	'',
				),
				'us_email'	=>	'',
				'us_password'	=>	'',
			);
					
			// Pull in first name, last name, email and password
			if (array_key_exists("First Name",$user)) {
				$insertData['us_name']['first_name'] = $user['First Name'];			
			}
			if (array_key_exists("Last Name",$user)) {
				$insertData['us_name']['last_name'] = $user['Last Name'];			
			}
			if (array_key_exists("Email",$user)) {
				$insertData['us_email'] = $user['Email'];			
			}
			if (array_key_exists("Password",$user)) {
				$insertData['us_password'] = $user['Password'];			
			} else if (array_key_exists("Email",$user)) {
				$insertData['us_password'] = $user['Email'];			
			}
	
			// Add the verify password value
			$insertData['UsPassword_V'] = $insertData['us_password'];
			
			// Kludge if they supplied the first and last name in the same field..
			if (array_key_exists('Full Name',$user)) {
				if (ListLen($user['Full Name'],' ') > 1) {
					$insertData['us_name']['last_name'] = ListLast($user['Full Name'],' ');			
					$insertData['us_name']['first_name'] = substr($user['Full Name'],0,0-(strlen($insertData['us_name']['last_name'])+1));			
				} else {
					$insertData['us_name']['first_name'] = $user['Full Name'];			
				}
			}
	
			// Load all the custom fields also
			$extras = '';
			foreach($fields as $field) {
				ss_paramKey($field,"name");
				ss_paramKey($field,"uuid");
				if ($field['uuid'] !== 'Name' and $field['uuid'] !== 'Email' and $field['uuid'] !== 'Password') {
					if (array_key_exists($field['name'],$user)) {
						// Do something here to find the uuid of the value?
						$insertData['Us'.$field['uuid']] = $user[$field['name']];
						$extras .= '<td>';
						if (strlen($user[$field['name']])) {
							$extras .= ss_HTMLEditFormat($user[$field['name']]);
						} else {
							$extras .= '&nbsp;';	
						}
						$extras .= '</td>';
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
			$insertData['user_groups'] = $this->ATTRIBUTES['user_groups'];
			$insertData['DoAction'] = 'Yes';
			
			// Display the values
			print("<tr>");
			print("<td>".(strlen($insertData['us_name']['first_name'])?ss_HTMLEditFormat($insertData['us_name']['first_name']):'&nbsp')."</td>");
			print("<td>".(strlen($insertData['us_name']['last_name'])?ss_HTMLEditFormat($insertData['us_name']['last_name']):'&nbsp')."</td>");
			print("<td>".(strlen($insertData['us_email'])?ss_HTMLEditFormat($insertData['us_email']):'&nbsp')."</td>");
			print("<td>".(strlen($insertData['us_password'])?ss_HTMLEditFormat($insertData['us_password']):'&nbsp')."</td>");
			print($extras);
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

		print("<p>$counter user(s) found.</p>");
		print("<form method=\"post\" action=\"index.php?act=Import.users\">");
		print("<input type=\"hidden\" name=\"UserUpdate\" value=\"".$this->ATTRIBUTES['ImportUpdate']."\">");
		print("<input type=\"hidden\" name=\"user_groups\" value=\"".ss_HTMLEditFormat(serialize($this->ATTRIBUTES['user_groups']))."\">");
		print("<input type=\"hidden\" name=\"Code\" value=\"".ss_HTMLEditFormat($code)."\">");
		print("<div align=\"center\"><input type=\"submit\" name=\"submit\" value=\"Import\"></div>");
		print("</form>");
	}
?>