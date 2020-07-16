<?php 
	$errors = array();
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
		
		

		if (count($errors) == 0) {
			//  and then write to the database		     
			$errors = $userAdmin->update();				
		}
		//ss_DumpVarDie($errors);
		$temp = new Request("Security.Sudo",array('Action'=>'stop'));
		if (count($errors) == 0) {			
			
			$usID = $userAdmin->primaryKey;
			foreach ($asset->cereal[$this->fieldPrefix.'JOIN_GROUPS'] as $aGroup) {
				$Q_Check = getRow("SELECT * FROM user_user_groups WHERE uug_us_id = $usID AND uug_ug_id = $aGroup");
				if (!strlen($Q_Check['uug_us_id'])) {
					$Q_AddUserGroups = query("INSERT INTO user_user_groups (uug_us_id , uug_ug_id) VALUES ($usID, $aGroup)");
				}
			}	
						
			$temperror = null;								
			ss_login($userAdmin->primaryKey, $temperror);
/*			if (ss_optionExists('Member Edit Notification Email')) {
				ss_paramKey($asset->cereal, $this->fieldPrefix.'ADMINEMAIL', '');
				if (strlen($asset->cereal[$this->fieldPrefix.'ADMINEMAIL'])) {
					$data = array();
					$data['FieldSet'] = $userAdmin;						
					global $cfg;
					$data['SiteAddress'] = $cfg['currentSite'];						
					$data['us_id'] = $this->ATTRIBUTES['us_id'];						
					$emailContent = $this->processTemplate('Email_UpdateMember', $data);
					require_once('System/Libraries/htmlMimeMail/htmlMimeMail.php');
					$mailer = new htmlMimeMail();		
					$mailer->setFrom($asset->cereal[$this->fieldPrefix.'ADMINEMAIL']);
					$mailer->setSubject("Member details has been updated- {$GLOBALS['cfg']['website_name']}");				
					$mailer->setHTML($emailContent);				
					$mailer->send(array($asset->cereal[$this->fieldPrefix.'ADMINEMAIL']));				
				}		
					
			}*/
								
 			locationRelative($assetPath);								
		} 
	}
?>
