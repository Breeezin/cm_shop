<?php

	$this->fieldSet = new FieldSet(array(
		'formName'	=>	'AssetForm',
	));
	
	$Q_UserGroups = query("
		SELECT * FROM user_groups			
		ORDER By ug_name
	");
		
	$userGroups = array();
	while ($row = $Q_UserGroups->fetchRow()) {
		$userGroups[$row['ug_name']] = $row['ug_id'];
	}
		
	$this->fieldSet->addField(new MultiCheckFromArrayField(array(
		'name'			=>	$this->fieldPrefix.'UPLOAD_USERGROUPS',
		'displayName'	=>	'Upload User Groups',
		'options'		=>	$userGroups,
		'multi'			=>	true,
		'required'		=>	true,
		'columns'		=>	1,
	)));	


	$this->fieldSet->addField(new EmailField(array(
		'name'		=>	$this->fieldPrefix.'NOTIFICATION_EMAIL_ADDRESS',
		'displayName'	=>	'Notification Email Address',
		'required'	=>	true,
		'size'		=>	30,
	)));	
	
	$this->fieldSet->addField(new IntegerField(array(
		'name'		=>	$this->fieldPrefix.'RELEASES_PER_PAGE',
		'required'	=>	false,
		'size'		=>	5,
	)));
			
	
?>