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
		'name'			=>	$this->fieldPrefix.'ADMIN_USERGROUPS',
		'displayName'	=>	'Administator User Groups',
		'options'		=>	$userGroups,
		'multi'			=>	true,
		'required'		=>	true,
		'columns'		=>	1,
	)));	

	$this->fieldSet->addField(new CheckBoxField(array(
		'name'			=>	$this->fieldPrefix.'ALLOW_GUEST_POSTS',
		'displayName'	=>	'Allow guests to post?',
		'required'		=>	false,
	)));	

?>