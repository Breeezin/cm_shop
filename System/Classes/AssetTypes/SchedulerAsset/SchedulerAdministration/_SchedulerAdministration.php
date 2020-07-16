<?php
requireOnceClass('Administration');
class SchedulerAdministration extends Administration {

	function exposeServices() {
		return Administration::exposeServicesUsing('Scheduler');
	}

    /*
	function query($params = array()) {
		$result = new Request('Security.Authenticate',array(
			'Permission'	=>	'IsDeployer',
			'LoginOnFail'	=>	'No',
		));
		// This sets the minimum primary key to allow in the query to be 2
		// so it will hide guest, super and innovative media user accounts		
		if ($result->value == TRUE) {
			$query = parent::query($params);
		} else {
			$query = parent::query(array_merge($params,array(
				'Min'	=>	2,
			)));
		}

		if (is_object($query)) {
			$query->addColumn('user_groups');
			$counter = 0;
			while ($row = $query->fetchRow()) {
				$Q_UserGroups = query("
					SELECT ug_name FROM user_user_groups, user_groups
					WHERE uug_us_id = {$row['us_id']}
						AND uug_ug_id = ug_id
				");
				$userGroups = '';
				while ($userGroup = $Q_UserGroups->fetchRow()) {
					if (strlen($userGroups)) $userGroups .= ', ';
					$userGroups .= $userGroup['ug_name'];
				}
				$query->setCell('user_groups',$userGroups,$counter++);
			}
		}
				
		return $query;
	}
    

	function delete() {
		
		require('DeleteAction.php');	
	}

	*/
    
	function __construct($isAdmin = true) {
			
		
		$fieldsArray = array();	
		$Q_UserAsset = getRow("SELECT * FROM assets WHERE as_type LIKE 'Scheduler'");

		if( !$Q_UserAsset )
			return;

		ss_paramKey($Q_UserAsset,'as_serialized',''); 
		
		if (strlen($Q_UserAsset['as_id']) AND strlen($Q_UserAsset['as_serialized'])) {
			$cereal = unserialize($Q_UserAsset['as_serialized']);			
			ss_paramKey($cereal,'AST_SCHEDULER_FIELDS','');
			if (strlen($cereal['AST_SCHEDULER_FIELDS'])) {
				$fieldsArray = unserialize($cereal['AST_SCHEDULER_FIELDS']);
			} else {
				$fieldsArray = array();	
			}
		} else {
			$fieldsArray = array();	
		}


		$tableDisplayFields = array();
		$tableDisplayFieldTitles = array();
		$tableSearchFields = array();
		$orderBys = array();
		array_push($tableDisplayFields, "EvID");
		array_push($tableDisplayFieldTitles,'ID');
		array_push($tableDisplayFields, "EvStart");
		array_push($tableDisplayFieldTitles,'Start');
		array_push($tableDisplayFields, "EvEnd");
		array_push($tableDisplayFieldTitles,'End');
		array_push($tableSearchFields, "EvID");
		array_push($tableSearchFields, "EvDescription");
		array_push($tableSearchFields, "EvLocation");
		$orderBys["EvID"] = "ID";
		$orderBys["EvStart"] = "Start";
		$orderBys["EvEnd"] = "End";
        
		 
        $specialOrderBys = array();
		$tableSearchFieldsFromOptions = array();
		$specialFieldTypes = ss_getFieldSetTypes(false);
		
		// Decide which fields are going to be displayed in the list page
		foreach($fieldsArray as $fieldDef) {		
			// Param all the settings we might have
			ss_paramKey($fieldDef,'uuid','');			
			ss_paramKey($fieldDef,'name','unknown');									
			ss_paramKey($fieldDef,'type','');									
			ss_paramKey($fieldDef,'AppearInList','no');								
			if ($fieldDef['AppearInList'] == 'yes') {
				if (!($fieldDef['type'] == 'MonthlyScheduleField' and is_array($assetLink)) ) {
									
					if (array_key_exists($fieldDef['type'], $specialFieldTypes)) {
						if ($fieldDef['type'] == "CountryField") {
							$tableSearchFieldsFromOptions["Ev".$fieldDef['uuid']] = array("table"=> "countries", "joinField" =>"cn_three_code", 'displayField' =>  "cn_name", 'groupField' =>  "");
						} else {
							$tableSearchFieldsFromOptions["Ev".$fieldDef['uuid']] = array("table"=> "select_field_options", "joinField" =>"sfo_uuid", 'displayField' =>  "sfo_value", 'groupField' =>  "sfo_parent_uuid");
						}
						$specialOrderBys["Ev".$fieldDef['uuid']] = $fieldDef['name'];
					} else {
						array_push($tableSearchFields, "Ev".$fieldDef['uuid']);
						$orderBys["Ev".$fieldDef['uuid']] = $fieldDef['name'];
					}
					array_push($tableDisplayFields, "Ev".$fieldDef['uuid']);
					array_push($tableDisplayFieldTitles, $fieldDef['name']);
				}
				
			}	
		}	
		
		//$this->Administration(array(
		parent::__construct(array(
			'prefix'					=>	'Scheduler',
			'singular'					=>	'Events',
			'plural'					=>	'Events',
			'assetLink'					=>	2,
			'tableName'					=>	'Events',
			'tablePrimaryKey'			=>	'EvID',
			'tableOrderBy'				=>	$orderBys,			
			'tablePrefix'				=>	'Ev',			
			'tableSpecialOrderBy'		=>	$specialOrderBys,			
			'tableDisplayFields'		=>	$tableDisplayFields,
			'tableSearchFields'			=>	$tableSearchFields,
			'tableSearchFieldsFromOption'=>	$tableSearchFieldsFromOptions,
			'tableDisplayFieldTitles'	=>	$tableDisplayFieldTitles,									
		));
		
		$this->addCustomizedFields($fieldsArray, "ev_");

		$groupsArray = array();	
		$Q_UserAsset = getRow("SELECT * FROM assets WHERE as_type LIKE 'Scheduler'");
		ss_paramKey($Q_UserAsset,'as_serialized',''); 
		if (strlen($Q_UserAsset['as_id']) AND strlen($Q_UserAsset['as_serialized'])) {
			$cereal = unserialize($Q_UserAsset['as_serialized']);			
			if (count($cereal['AST_SCHEDULER_GROUPS'])) {
				$groupsArray = ($cereal['AST_SCHEDULER_GROUPS']);
			}
		}
        
        $parameters = array (
            'groups'               =>  $groupsArray,           
        );
		$temp = new Request("Security.Sudo",array('Action'=>'start'));
		$result = new Request('SchedulerUsersAdministration.Query',$parameters);
		$temp = new Request("Security.Sudo",array('Action'=>'stop'));
		$result = $result->value;
        $allowedGroups = array();
        while ($row = $result->fetchRow()) {
            $allowedGroups[$row['us_first_name'].' '.$row['us_last_name']."({$row['us_id']})"] = $row['us_id'];
        }



		$this->addField(new MultiSelectFromArrayField (array(
			'name'			=>	'EvUsers',
			'displayName'	=>	'User',
            'note'          =>  'Leave this blank to specify all users',
			'required'		=>	FALSE,
			'verify'		=>	FALSE,
			'unique'		=>	FALSE,
			'options'		=>	$allowedGroups,
			'multi'			=>	true,
		)));			

		$this->addField(new SelectField (array(
			'name'			=>	'EvTypeLink',
			'displayName'	=>	'Event Type',
            'note'          =>  null,
			'required'		=>	true,
			'verify'		=>	FALSE,
			'unique'		=>	FALSE,
				'size'	=>	'30',	'maxLength'	=>	'255',
				'rows'	=>	'6',	'cols'		=>	'40',
		    'linkQueryAction'	=>	'SchedulerTaskTypesAdministration.Query',
		    'linkQueryDisplayField'	=>	'EvTyName',
		    'linkQueryValueField'	=>	'EvTyID',
		)));
        
        
		$this->addField(new DateTimeField (array(
			'name'			=>	'EvStart',
			'displayName'	=>	'Start',
			'note'			=>	NULL,
			'required'		=>	true,
			'showCalendar'		=>	true,                 
			'verify'		=>	FALSE,
			'unique'		=>	FALSE,
			'size'	=>	'10',	'maxLength'	=>	'10',
			'rows'	=>	'6',	'cols'		=>	'40',
			'linkQueryAction'	=>	NULL,
			'linkQueryValueField'	=>	NULL,
			'linkQueryDisplayField'	=>	NULL,
		)));
		
		$this->addField(new DateTimeField (array(
			'name'			=>	'EvEnd',
			'displayName'	=>	'End',
			'note'			=>	NULL,
			'showCalendar'		=>	true,                 
			'required'		=>	true,
			'verify'		=>	FALSE,
			'unique'		=>	FALSE,
			'size'	=>	'10',	'maxLength'	=>	'10',
			'rows'	=>	'6',	'cols'		=>	'40',
			'linkQueryAction'	=>	NULL,
			'linkQueryValueField'	=>	NULL,
			'linkQueryDisplayField'	=>	NULL,
		)));
						
		$this->addField(new MemoField (array(
			'name'			=>	'EvDescription',
			'displayName'	=>	'Description',
			'note'			=>	NULL,
			'required'		=>	FALSE,
			'verify'		=>	FALSE,
			'unique'		=>	FALSE,
			'size'	=>	'10',	'maxLength'	=>	'10',
			'rows'	=>	'6',	'cols'		=>	'40',
			'linkQueryAction'	=>	NULL,
			'linkQueryValueField'	=>	NULL,
			'linkQueryDisplayField'	=>	NULL,
		)));
		
		$this->addField(new MemoField (array(
			'name'			=>	'EvLocation',
			'displayName'	=>	'Location',
			'note'			=>	NULL,
			'required'		=>	FALSE,
			'verify'		=>	FALSE,
			'unique'		=>	FALSE,
			'size'	=>	'10',	'maxLength'	=>	'10',
			'rows'	=>	'6',	'cols'		=>	'40',
			'linkQueryAction'	=>	NULL,
			'linkQueryValueField'	=>	NULL,
			'linkQueryDisplayField'	=>	NULL,
		)));

			
	}

}
?>
