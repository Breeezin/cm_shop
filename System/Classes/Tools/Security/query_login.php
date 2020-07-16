<?php
	// Default some values	
	$this->param('Email','');
	$this->param('Password','');	
	$this->param('KeepMeLoggedIn',0);
	$this->param('ShowKeepMeLoggedIn',1);
	$this->param('LoginType','');
	$this->param('UserNameDesc','Email Address');

	$errors = NULL;
	$errorData = array('ErrorType'=> 'Normal');			
	if (array_key_exists('DoAction',$this->ATTRIBUTES)) {		
		
		if ($this->ATTRIBUTES['Password'] == '') {
			$passwordSQL = " IS NULL ";
		} else {
			$passwordSQL = "= '".escape($this->ATTRIBUTES['Password'])."'";
		}
		// custom login
		// one of example for the custom login is duty free frequent buyer login 
		// check custom login file in the Custom/functioin folder
		$cumstomLoginProcessed = false;
		$customFilePath = expandPath('Custom/Core/login.php');
		if (file_exists($customFilePath)) {		
			require($customFilePath);	
		}

		if ($cumstomLoginProcessed == false) {
            if (ss_optionExists('Security Login With UserName')
				&& !strchr($this->ATTRIBUTES['Email'], '@'))
            {
				$result = query("
					SELECT * FROM users
					WHERE us_user_name LIKE '".escape($this->ATTRIBUTES['Email'])."'
						AND us_password $passwordSQL
				");

            }
            else if (ss_optionExists('Security Login With Name')
				&& !strchr($this->ATTRIBUTES['Email'], '@'))
            {
				$firstName = ListFirst($this->ATTRIBUTES['Email'],'.');
				$lastName = ListRest($this->ATTRIBUTES['Email'],'.');
				$result = query("
					SELECT * FROM users
					WHERE us_first_name LIKE '".escape($firstName)."'
						AND us_last_name LIKE '".escape($lastName)."'
						AND us_password $passwordSQL
				");
//				echo " SELECT * FROM users
//					WHERE us_first_name LIKE '".escape($firstName)."'
//						AND us_last_name LIKE '".escape($lastName)."'
//						AND us_password $passwordSQL
//				";			
				
			} else {
				if (ss_optionExists('Shop Checkout Hide Password') and array_key_exists('SHOP_SUBMIT',$this->ATTRIBUTES)) {
					$result = query("
						SELECT * FROM users
						WHERE us_email = '".escape($this->ATTRIBUTES['Email'])."'
					");
					if ($result->numRows() > 0) {
						$result->preFetch();
						// If the email address was found, then check if it is an 
						// admin account or not	
						$Q_User = $result->fetchRow();
						$result->reset();
						
						// If it's an admin account, do not allow it.
						$Q_UserGroups = query("
							SELECT * FROM user_user_groups
							WHERE uug_us_id = {$Q_User['us_id']}
						");
						while ($ug = $Q_UserGroups->fetchRow()) {
							if ($ug['uug_ug_id'] == 1) {
								$result = query("
									SELECT * FROM users
									WHERE us_id = -1234
								");
								break;
							}	
						}						
					}
				} else {
					ss_log_message( "
						SELECT * FROM users
						WHERE us_email = '".escape($this->ATTRIBUTES['Email'])."'
							AND us_password $passwordSQL
					");
					$result = query("
						SELECT * FROM users
						WHERE us_email = '".escape($this->ATTRIBUTES['Email'])."'
							AND us_password $passwordSQL
					");
				}
			}
		
			ss_log_message( "login returned ".$result->numRows()." rows" );
			// See if the user was validated
			if ($result->numRows() > 0) {
				$row = $result->fetchRow();			
				// Login as the user
				if (ss_login($row['us_id'],$errors)) {
					//record into login_stats
					if ($row['us_id'] > 2) {
						$Q_Log = query("INSERT INTO login_statistics VALUES ({$row['us_id']}, NOW())");
					}
					// If the account wasn't expired then either save cookie or redirect
					// to the requested page
					if ($this->ATTRIBUTES['KeepMeLoggedIn']) {
						location('index.php?act=Security.CreateCookie&BackURL='.ss_URLEncodedFormat($this->ATTRIBUTES['BackURL']));
					} else {
						location($this->ATTRIBUTES['BackURL']);
					}
				}
				
			} else {
				$errors = $this->processTemplate('Errors', $errorData); //'<STRONG>A Problem :</STRONG> Please correct the following problem and try again. <UL><LI>Those credentials are not correct.  Please try again.</LI></UL>';
			}
		}
	}

	$secureSite = $GLOBALS['cfg']['secure_server'];
	$secureSite = ss_withTrailingSlash($secureSite);

	$data = array();
	$data['UserNameDesc'] = $this->ATTRIBUTES['UserNameDesc'];
	$data['BackURL'] = $this->ATTRIBUTES['BackURL'];
	$data['KeepMeLoggedIn'] = $this->ATTRIBUTES['KeepMeLoggedIn'];
	$data['FormAction'] = $secureSite.'index.php?act=Security.Login&BackURL='.ss_URLEncodedFormat($this->ATTRIBUTES['BackURL']);
	$data['Errors'] = $errors;
	$data['ShowKeepMeLoggedIn'] = $this->ATTRIBUTES['ShowKeepMeLoggedIn'];

    //for dutyfreestores
    if (isset ($errorData['Multiple'])){
        $data['Multiple'] = $errorData['Multiple'];
    }

?>
