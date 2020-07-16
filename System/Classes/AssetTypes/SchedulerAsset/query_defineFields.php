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

        if ( ss_isItUs() ) {
            $addedOptions = array(array(
						'Name' 			=> 'prefixed',
						'Description' 	=> 'Lock',
						'Options' 		=> array(
											'Field is Fixed'=>'1',
											'Field is Unfixed'=> '0'),
                        'default'       => 0,
						));
            $options = array_merge($options,$addedOptions);
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

   		$this->fieldSet->addField(new FieldSetBuilderField (array(
		    'name'			=>	$this->fieldPrefix.'FIELDS',
		    'fieldSetName'	=>	"Events Form Definition",
    		'extraOption'	=>	$options,
   		)));


        $parameters = array (
            'NoHusk' => TRUE,
            'linkQueryValueField' => 'ug_id',
            'linkQueryDisplayField' => 'ug_name',            
        );
		$temp = new Request("Security.Sudo",array('Action'=>'start'));
		$result = new Request('UserGroupsAdministration.Query',$parameters);
		$temp = new Request("Security.Sudo",array('Action'=>'stop'));
		$result = $result->value;
        $allowedGroups = array();
        while ($row = $result->fetchRow()) {
            $allowedGroups[$row['ug_name']] = $row['ug_id'];
        }

		$this->fieldSet->addField(new MultiSelectFromArrayField (array(
		    'name'			=>	$this->fieldPrefix.'GROUPS',
			'displayName'	=>	'Allowed users',
			'note'			=>	'Please select one or more groups that have access to this asset',
			'options'		=>	$allowedGroups,
			'multi'			=>	true,
		)));			
        
        
        $groupsArray = array('1');	
        $schedulerAdminGroups = ss_optionExists('Scheduler Administrative Groups');
        if($schedulerAdminGroups){
            $groupsArray = array_merge($groupsArray,ListToArray($schedulerAdminGroups));            
        }
        
        $parameters = array (
            'groups'               =>  $groupsArray,           
        );
		$temp = new Request("Security.Sudo",array('Action'=>'start'));
		$result = new Request('SchedulerUsersAdministration.Query',$parameters);
		$temp = new Request("Security.Sudo",array('Action'=>'stop'));
		$result = $result->value;
        $allowedAdmins = array();
        while ($row = $result->fetchRow()) {
            $allowedAdmins[$row['us_first_name'].' '.$row['us_first_name']."({$row['us_id']})"] = $row['us_id'];
        }
		$this->fieldSet->addField(new MultiSelectFromArrayField (array(
		    'name'			=>	$this->fieldPrefix.'ADMIN_GROUPS',
			'displayName'	=>	'Administrative users',
			'note'			=>	'These users have administrative rights over this scheduler',
			'required'		=>	FALSE,
			'verify'		=>	FALSE,
			'unique'		=>	FALSE,
			'options'		=>	$allowedAdmins,
			'multi'			=>	true,
		)));			
			


?>