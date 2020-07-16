<?php 
	
	if (array_key_exists('Do_Service',$this->ATTRIBUTES)) {	
		
		
		$temp = new Request("Security.Sudo",array('Action'=>'start'));
		
		$userAdmin->loadFieldValuesFromForm($this->ATTRIBUTES);			
		// Validate and then write to the database
		$Q_User = getRow("SELECT * FROM users WHERE us_email LIKE '{$this->ATTRIBUTES['us_email']}'");
		if (!strlen($Q_User['us_id'])) {
			$errors = $userAdmin->insert();		
		} else {
			if (!strlen($Q_User['us_password']) or $Q_User['us_password'] == $this->ATTRIBUTES['us_password']) {
				$userAdmin->primaryKey = $Q_User['us_id'];
				$errors = $userAdmin->update();	
			} else {
				$errors = $userAdmin->insert();		
			} 
		}		
		
		$temp = new Request("Security.Sudo",array('Action'=>'stop'));
		
		if (count($errors) == 0) {								
				
			if ($memberType == 'TI') {
				ss_paramKey($asset->cereal,$this->fieldPrefix.'TI_REGISTRATION_THANK_YOU_CONTENT','');
			
				$editableContent = ss_parseText($asset->cereal[$this->fieldPrefix.'TI_REGISTRATION_THANK_YOU_CONTENT']);
				$editableContent = stri_replace('[first_name]',ss_HTMLEditFormat(ss_getFirstName()),$editableContent);
				$editableContent = stri_replace('[last_name]',ss_HTMLEditFormat(ss_getLastName()),$editableContent);
				$data = array(
					'EditableContent'	=>	$editableContent,		
				);
			
				//ss_DumpVarDie($data,'', true);
				$this->useTemplate('ThankYouService',$data);
			} else {
			}
			
			
				
			
			
		} 
	}
?>