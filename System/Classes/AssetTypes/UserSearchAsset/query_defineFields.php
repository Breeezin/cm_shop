<?php		
		$this->fieldSet = new FieldSet(array(
			'formName'	=>	'AssetForm',
		));
		
		$Q_UserGroups = query("
			SELECT * FROM user_groups			
			ORDER By ug_name
		");
		
		$userGroups = array();
		$newsGroups = array();
		while ($row = $Q_UserGroups->fetchRow()) {
			$userGroups[$row['ug_name']] = $row['ug_id'];
			if ($row['ug_mailing_list'] == 1) 
				$newsGroups[$row['ug_name']] = $row['ug_id'];		
		}
		
		$this->fieldSet->addField(new MultiCheckFromArrayField (array(
			'name'			=>	$this->fieldPrefix.'GROUPS',
			'displayName'	=>	'User Search Groups',
			'options'		=>	$userGroups,
			'multi'			=>	true,
			'required'		=>	false,
		)));
		
		$this->fieldSet->addField(new HtmlMemoField2 (array(
			'name'			=>	$this->fieldPrefix.'DETAIL_TEMPLATE',
			'displayName'	=>	'User Details Template',
			'required'		=>	false,			
			'height'	=>	'200',
		)));
		
?>