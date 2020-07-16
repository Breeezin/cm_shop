<?php
		
	requireOnceClass('FieldSet');

	$this->param('as_id',0);
	$this->param('SoHeight',0);
	
	$id = $this->ATTRIBUTES['as_id'];
	
	$this->param('AfterUpdate','');
	
	
	// Get the asset specified by attributes or server path_info
	// "true" indicates we are loading for an "edit" operation
	$this->loadAsset(true);
	
	// Check if they are allowed to administer this asset
	$result = new Request('Security.Authenticate',array(
		'Permission'	=>	'CanAdministerAsset',
		'as_id'		=>	$this->getID(),
	));
	
	$this->fieldSet = new FieldSet(array(
		'tablePrimaryKey'	=>	'as_id',
		'tableName'	=>	'assets',
		'primaryKey'	=>	$this->getID(),
		'formName'	=>	'AssetForm',
	));
	
	$isSuperUser = new Request('Security.Authenticate',array(
		'Permission'	=>	'IsSuperUser',
		'LoginOnFail'	=>	false,
	));
	$isSuperUser = $isSuperUser->value;

	$isTheDeployer = new Request('Security.Authenticate',array(
		'Permission'	=>	'IsDeployer',
		'LoginOnFail'	=>	false,
	));
	$isTheDeployer = $isTheDeployer->value;
	
	if (($this->fields['as_system'] != 1 and $this->fields['as_owner_au_id'] != 0) or $isSuperUser) {
		$this->fieldSet->addField(new AssetNameField (array(
			'name'			=>	'as_name',
			'displayName'	=>	'Name',
			'required'		=>	TRUE,
			'as_id'		=> $this->getID(),
			'size'	=>	30,		'maxLength'	=>	255,
		)));
		$this->fieldSet->addField(new TextField (array(
			'name'			=>	'as_subtitle',
			'displayName'	=>	'Sub Title',
			'required'		=>	false,		
			'size'	=>	30,		'maxLength'	=>	255,
		)));
		$this->fieldSet->addField(new CheckBoxField (array(
			'name'			=>	'as_appear_in_menus',
			'displayName'	=>	'Appears In Menus',
		)));
	}
	
	if ($isTheDeployer) {
		$this->fieldSet->addField(new SelectField (array(
				'name'			=>	"as_type",
				'displayName'	=>	'Type',
				'note'			=>	NULL,
				'required'		=>	true,
				'verify'		=>	FALSE,
				'unique'		=>	FALSE,
				'class'			=>	'formborder',
				'value'			=>	$this->fields['as_type'],
				'size'	=>	'30',	'maxLength'	=>	'255',
				'rows'	=>	'6',	'cols'		=>	'40',
				'linkQueryAction'	=>	'AssetTypesAdministration.Query',
				'linkQueryValueField'	=>	'at_name',
				'linkQueryValueFieldIsText'	=>	true,
				'linkQueryDisplayField'	=>	'at_display',
		)));
		
		$this->fieldSet->addField(new CheckBoxField (array(
			'name'			=>	'as_dev_asset',
			'displayName'	=>	'Development Asset',
		)));

		$this->fieldSet->addField(new HiddenField (array(
			'name'			=>	'as_owner_au_id',						
		)));

	}
	
	if ($this->supportsReview) {
		$this->fieldSet->addField(new HiddenField (array(
			'name'			=>	'AssetAuthorComments',						
		)));
		$this->fieldSet->addField(new HiddenField (array(
			'name'			=>	'AssetReviewer',						
		)));
	}

	if (ss_optionExists("Schedule assets")) {
		$this->fieldSet->addField(new DateTimeField (array(
			'name'			=>	'AssetOnlineDate',
			'displayName'	=>	'Online Date/Time',
			'size'	=>	10,
			'showCalendar'	=>	true,
		)));
		$this->fieldSet->addField(new DateTimeField (array(
			'name'			=>	'AssetOfflineDate',
			'displayName'	=>	'Offline Date/Time',
			'size'	=>	10,
			'showCalendar'	=>	true,
		)));
		$this->fieldSet->addField(new RestrictedTextField (array(
			'name'			=>	'AssetOnline',
			'displayName'	=>	'Online Scheduling',
			'options'	=>	array('Never','','Date'),
		)));		
		$this->fieldSet->addField(new RestrictedTextField (array(
			'name'			=>	'AssetOffline',
			'displayName'	=>	'Offline Scheduling',
			'options'	=>	array('','Now','Date'),
		)));		
	}

	

	$this->fieldSet->addField(new TextField (array(
			'name'			=>	'as_menu_name',
			'displayName'	=>	'Menu Name',
			'size'	=>	50,		'maxLength'	=>	255,
	)));

	$this->fieldSet->addField(new TextField (array(
			'name'			=>	'as_header_name',
			'displayName'	=>	'Header Name',
			'size'	=>	50,		'maxLength'	=>	255,
	)));
	
	$this->fieldSet->addField(new HiddenField (array(
			'name'			=>	'as_search_keywords',						
	)));
	
	$this->fieldSet->addField(new HiddenField (array(
			'name'			=>	'as_search_description',						
	)));

	
	
	$this->defineLayoutFields();

	// Get an object for the correct asset type and define the fields
	$className = $this->fields['as_type'].'Asset';
	requireOnceClass($className);
	$assetType = new $className;

	$assetType->defineFields($this);
	
		
	// Load the fields with values from the DB or from a previous form submission
	$this->fieldSet->loadFieldValues($this->ATTRIBUTES,$this->fields);
	$assetType->fieldSet->ATTRIBUTES = $this->ATTRIBUTES;
	$assetType->fieldSet->loadFieldValues($this->ATTRIBUTES,$this->cereal,$this->fieldSet->isEdit($this->ATTRIBUTES));
	$this->layoutFieldSet->loadFieldValues($this->ATTRIBUTES,$this->layout,$this->fieldSet->isEdit($this->ATTRIBUTES));
	

?>
