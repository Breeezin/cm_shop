<?php 
	
	if (array_key_exists('Do_Service',$this->ATTRIBUTES)) {	
		
		$temp = new Request("Security.Sudo",array('Action'=>'start'));
		
		$userAdmin->loadFieldValuesFromForm($this->ATTRIBUTES);			
		
		// Validate
		$errors = $userAdmin->validate();		
		
		// validate for 
		if (array_key_exists('us_user_name',$this->ATTRIBUTES) and array_key_exists('us_referral_user_name',$this->ATTRIBUTES)) {
			if (strlen(trim($this->ATTRIBUTES['us_user_name'])) and strlen(trim($this->ATTRIBUTES['us_referral_user_name']))) {
				if (strtolower(trim($this->ATTRIBUTES['us_user_name'])) == strtolower(trim($this->ATTRIBUTES['us_referral_user_name']))) {
					$errors['us_user_name'] = array('User Name and Referral User Name must be different');
				}	
			}	
		}
		
		if (array_key_exists('us_referral_user_name',$this->ATTRIBUTES) and strlen(trim($this->ATTRIBUTES['us_referral_user_name']))) {
			$referral = getRow("
				SELECT us_id From users
				WHERE us_user_name LIKE '".escapeLike($this->ATTRIBUTES['us_referral_user_name'])."'
			");
			if ($referral === null) {
				$errors['us_referral_user_name'] = array('Referral User Name does not match with any existing customers.  Please leave this field blank if you were not referred by anyone.');	
			}
		}
				
		
		// Validate and then write to the database
		if (count($errors) == 0) {
			$Q_User = getRow("SELECT * FROM users WHERE us_email LIKE '{$this->ATTRIBUTES['us_email']}'");
			if (!strlen($Q_User['us_id'])) {
				$errors = $userAdmin->insert();		
			} else {
				if (!strlen($Q_User['us_password']) or strtolower(trim($Q_User['us_password'])) == strtolower(trim($this->ATTRIBUTES['us_password']))) {
					// remove those fields that aren't allowed
					$userAdmin = new UsersAdministration(false);
					$userAdmin->ATTRIBUTES = $this->ATTRIBUTES;
					$userAdmin->loadFieldValuesFromForm($this->ATTRIBUTES);	
					
					$userAdmin->primaryKey = $Q_User['us_id'];
					$errors = $userAdmin->update();	
				} else {
					$errors['us_email'] = array('You entered an email address from an existing customer, but the password entered is incorrect.  If you have forgotten your password, <a href="index.php?act=Security.ForgotPassword&BackURL='.ss_urlEncodedFormat(ss_EscapeAssetPath($asset->getPath()).'/Service/New').'">click here for an email reminder</a>.');
					//$errors = $userAdmin->insert();		
				} 
			}		
		}
		
		$temp = new Request("Security.Sudo",array('Action'=>'stop'));
		
		if (count($errors) == 0) {								
			//$type = $asset->cereal['AST_MEMBERS_TYPE'];											
			$usID = $userAdmin->primaryKey;
			foreach ($asset->cereal[$this->fieldPrefix.'JOIN_GROUPS'] as $aGroup) {
				$Q_Check = getRow("SELECT * FROM user_user_groups WHERE uug_us_id = $usID AND uug_ug_id = $aGroup");
				if (!strlen($Q_Check['uug_us_id'])) {
					$Q_AddUserGroups = query("INSERT INTO user_user_groups (uug_us_id , uug_ug_id) VALUES ($usID, $aGroup)");
				}
			}	
			
				
			ss_login($usID,$tempErrors);
			locationRelative($assetPath);
			
 			
		} 
	}
?>
