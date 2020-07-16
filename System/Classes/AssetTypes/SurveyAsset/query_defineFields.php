<?php

	$this->fieldSet = new FieldSet(array(
		'formName'	=>	'AssetForm',
	));

    // merging the new types in the correct position
	$typeMappings = ss_getFieldSetTypes();
    $newTypeMapping = array();
    foreach ( $typeMappings as $key => $value) {
        $newTypeMapping[$key] = $value;
        if($key == 'SelectFromArrayField'){
            $newTypeMapping['RadioWithOtherFromArrayField']='Select One (With Text)';
        }
    }
//    ss_DumpVarDie($newTypeMapping);

	$this->fieldSet->addField(new FieldSetBuilderField (array(
		'name'			=>	$this->fieldPrefix.'FIELDS',
		'fieldSetName'	=>	"Survey Definition",
		'extraOption'	=>	array(
								array( 
								'Name' 			=> 'AppearInList', 
								'Description' 	=> 'Field', 
								'Options' 		=> array(
													'Appears In List'=>'yes',
													'Does Not Appear In List'=>'no')
								),	
							),
        'typeMappings' => $newTypeMapping,
	)));
	


    // --------------------  Survey Fields  -------------------------
     $this->fieldSet->addField(new CheckBoxField (array(
    	'name'			=>	$this->fieldPrefix."NOTIFICATION_EMAIL",
    	'displayName'	=>  'Do you want notifications?',
    	'required'		=>	false,
    	'size'			=>	20,
    )));
    $this->fieldSet->addField(new TextField (array(
    	'name'			=>	$this->fieldPrefix.'NOTIFICATION_EMAIL_ADDRESS',
    	'displayName'	=>  'Fill this field out if you want a notification email when users submit a survey result.',
    	'required'		=>	false,
    	'size'			=>	10,
    )));


    $this->fieldSet->addField(new CheckBoxField (array(
    	'name'			=>	$this->fieldPrefix."USE_CUSTOM_SURVEY_TEMPLATE",
    	'displayName'	=>  'Use Custom Add Template?',
    	'required'		=>	false,
    	'size'			=>	20,
    )));
    $this->fieldSet->addField(new TextField (array(
    	'name'			=>	$this->fieldPrefix.'SUBMIT_BUTTON',
    	'displayName'	=>  'Submit Button Text',
    	'required'		=>	false,
    	'size'			=>	10,
    )));



	$this->fieldSet->addField(new HtmlMemoField2 (array(
		'name'			=>	$this->fieldPrefix.'THANKYOU_CONTENT',
		'displayName'	=>	'Thank You Page Content',
		'required'		=>	false,
		'height'	=>	'200',
	)));

	$this->fieldSet->addField(new HtmlMemoField2 (array(
		'name'			=>	$this->fieldPrefix.'SURVEYPAGE_CONTENT',
		'displayName'	=>	'Survey Page Template',
		'required'		=>	false,
		'height'	=>	'200',
	)));




	if (ss_optionExists('Survey Content Layout Picker')) {
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
?>
