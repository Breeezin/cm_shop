<?php
				
		$this->fieldSet = new FieldSet(array(
			'formName'	=>	'AssetForm',
		));
				
		$Q_UserGroups = query("
			SELECT * FROM user_groups
			WHERE ug_mailing_list = 1
			ORDER By ug_name
		");
		
		$userGroups = array();
		while ($row = $Q_UserGroups->fetchRow()) {
			$userGroups[$row['ug_name']] = $row['ug_id'];
		}
		
		$this->fieldSet->addField(new SelectFromArrayField (array(
			'name'			=>	$this->fieldPrefix.'USERGROUPS',
			'displayName'	=>	'User Groups',
			'options'		=>	$userGroups,
			'multi'			=>	true,
		)));

		$this->fieldSet->addField(new PopupUniqueImageField (array(
			'name'			=>	$this->fieldPrefix.'BUTTONIMAGE',
			'displayName'	=>	'Image',
			'directory'		=>	ss_storeForAsset($asset->getID()),
			'preview'		=>	true,
		)));
		
		$this->fieldSet->addField(new PopupUniqueImageField (array(
			'name'			=>	$this->fieldPrefix.'BUTTONIMAGEOVER',
			'displayName'	=>	'Image',
			'directory'		=>	ss_storeForAsset($asset->getID()),
			'preview'		=>	true,
		)));
		
		$this->fieldSet->addField(new HtmlMemoField2(array(
					'name'			=>	$this->fieldPrefix.'SUBSCRIBE_CONTENT',
					'displayName'	=>	'Subscribe Content',
					'note'			=>	NULL,
					'required'		=>	FALSE,
					'verify'		=>	FALSE,
					'unique'		=>	FALSE,
					'size'	=>	'30',	'maxLength'	=>	'127',
					'rows'	=>	'6',	'cols'		=>	'40',
					'height'	=>	'200',
					'Directory' => "Custom/ContentStore/Layouts/Images/",
		)));

		$this->fieldSet->addField(new HtmlMemoField2(array(
					'name'			=>	$this->fieldPrefix.'UNSUBSCRIBE_CONTENT',
					'displayName'	=>	'Unsubscribe Content',
					'note'			=>	NULL,
					'required'		=>	FALSE,
					'verify'		=>	FALSE,
					'unique'		=>	FALSE,
					'size'	=>	'30',	'maxLength'	=>	'127',
					'rows'	=>	'6',	'cols'		=>	'40',
					'height'	=>	'200',
					'Directory' => "Custom/ContentStore/Layouts/Images/",
		)));
		
		if (ss_optionExists('Newsletter Advanced Subscribe Form')) {
			$userFields = array();
			$fieldsArray = array();				
			$Q_UserAsset = getRow("SELECT * FROM assets WHERE as_type LIKE 'users'");
			ss_paramKey($Q_UserAsset,'as_serialized',''); 
			
			if (strlen($Q_UserAsset['as_id']) AND strlen($Q_UserAsset['as_serialized'])) {
				$cereal = unserialize($Q_UserAsset['as_serialized']);			
				ss_paramKey($cereal,'AST_USER_FIELDS','');
				if (strlen($cereal['AST_USER_FIELDS'])) {
					$fieldsArray = unserialize($cereal['AST_USER_FIELDS']);
				} else {
					$fieldsArray = array();	
				}
			} else {
				$fieldsArray = array();	
			}
			//ss_DumpVar($fieldsArray, '', true);	
			foreach($fieldsArray as $fieldDef) {		
				// Param all the settings we might have
				ss_paramKey($fieldDef,'uuid','');			
				ss_paramKey($fieldDef,'name','unknown');									
				$userFields[$fieldDef['name']] = $fieldDef['uuid'];
			}
			
			$this->fieldSet->addField(new MultiSelectFromArrayField (array(
				'name'			=>	$this->fieldPrefix.'FORMFIELDS',
				'displayName'	=>	'Address Fields',
				'options'		=>	$userFields,
				'multi'			=>	true,
			)));
		}
		
?>