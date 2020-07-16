<?php

	$this->fieldSet = new FieldSet(array(
		'formName'	=>	'AssetForm',
	));
	
	$this->fieldSet->addField(new FieldSetBuilderField (array(
		'name'			=>	$this->fieldPrefix.'FIELDS',
		'fieldSetName'	=>	"Data Form Definition",		
		'extraOption'	=>	array(
								array( 
								'Name' 			=> 'AppearInList', 
								'Description' 	=> 'Field', 
								'Options' 		=> array(
													'Appears In List'=>'yes',
													'Does Not Appear In List'=>'no')
								),	
								array( 
								'Name' 			=> 'CategoryBy', 
								'Description' 	=> 'Category', 
								'Options' 		=> array(
													'Show'=>'yes',
													'Does not Show'=>'no')
								),
							)			
	)));	
	
	

	$this->fieldSet->addField(new HtmlMemoField2 (array(
		'name'			=>	$this->fieldPrefix.'MAINPAGE_CONTENT',
		'displayName'	=>	'Main Content',
		'required'		=>	false,
		'height'	=>	'200',
	)));

	$this->fieldSet->addField(new HtmlMemoField2 (array(
		'name'			=>	$this->fieldPrefix.'LISTPAGE_CONTENT',
		'displayName'	=>	'Data List Content',
		'required'		=>	false,
		'height'	=>	'200',
	)));
	
	$this->fieldSet->addField(new HtmlMemoField2 (array(
		'name'			=>	$this->fieldPrefix.'SUBPAGE_CONTENT',
		'displayName'	=>	'Subpage Content',
		'required'		=>	false,
		'height'	=>	'200',
	)));
	
	if (ss_optionExists('Data Collection Content Layout Picker')) {
		$ListLayouts = ListToArray(file_get_contents(expandPath('Custom/ContentStore/Layouts/Layouts.txt')),Chr(10));
		$layouts = array();
		foreach ($ListLayouts as $aLayout) {
			$index = ListLast($aLayout,":");
			$layouts["$index"] = ListFirst($aLayout,":");
		}
		foreach (array('MAIN','LIST','SUB',) as $name)	{
			$this->fieldSet->addField(new SelectFromArrayField (array(
				'name'			=>	$this->fieldPrefix.$name.'PAGE_LAYOUT',
				'displayName'	=>	$name.'page Layout',
				'required'		=>	false,
				'options'		=>	$layouts,
			)));
			$this->fieldSet->addField(new TextField (array(
				'name'			=>	$this->fieldPrefix.$name.'PAGE_CUSTOMTITLE',
				'displayName'	=>	$name.'page title',
				'required'		=>	false,
				'size'	=> 20, 	'maxlength'	=> 256
			)));
		}
	}

	if (ss_optionExists('Advanced Data Collection')) {
    	$this->fieldSet->addField(new HtmlMemoField2 (array(
    		'name'			=>	$this->fieldPrefix.'NEW_CONTENT',
    		'displayName'	=>	'New Content',
    		'required'		=>	true,
    		'height'	=>	'200',
    	)));
    	$this->fieldSet->addField(new HtmlMemoField2 (array(
    		'name'			=>	$this->fieldPrefix.'THANKYOU_CONTENT',
    		'displayName'	=>	'Thankyou Content',
    		'required'		=>	true,
    		'height'	=>	'200',
    	)));
    	$this->fieldSet->addField(new EmailField (array(
    		'name'			=>	$this->fieldPrefix.'EMAIL_RECIPIENT',
    		'displayName'	=>  'New Listing Email Recipient',
    		'required'		=>	false,
    		'size'			=>	40,
    	)));
    }

?>
