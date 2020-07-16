<?php
		$this->fieldSet = new FieldSet(array(
			'formName'	=>	'AssetForm',
		));

        $options =  array(array('Name' 			=> 'AppearInList',
    							'Description' 	=> 'Field',
    							'Options' 		=> array(
    												'Appears In List'=>'yes',
    												'Does Not Appear In List'=>'no'
        )),);

        if ( ss_optionExists('User Fieldset Show Prefixed Option') ) {
            $addedOptions = array(array(
						'Name' 			=> 'prefixed',
						'Description' 	=> 'Lock',
						'Options' 		=> array(
											'Field is Fixed'=>'1',
											'Field is Unfixed'=> '0'),
                        'default'       => 0,
						));
            $options = array_merge($options,$addedOptions);
        }

        if ( ss_optionExists('User Fieldset Show Unique Option') ) {
            $addedOptions = array(array(
						'Name' 			=> 'unique',
						'Description' 	=> 'Unique',
						'Options' 		=> array(
											'Yes'=>'1',
											'No'=>null),
                        'default'       => null,
						));
            $options = array_merge($options,$addedOptions);
        }


          // this doesn't seem to work, check out the wiki for "User Fieldset Show Prefixed Option "
    		$this->fieldSet->addField(new FieldSetBuilderField (array(
    		'name'			=>	$this->fieldPrefix.'FIELDS',
    		'fieldSetName'	=>	"User Form Definition",
    		'prefixedFields' => 	array('Name'=>'NameField',
                                          'Email' => 'EmailField',
                                          'Password' =>'PasswordField',),
    		'extraOption'	=>	$options,
    		)));

?>
