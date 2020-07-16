<?php
requireOnceClass('Administration');
class DataCollectionAdministration extends Administration {
	
	var $searchableFields = array();
	
	function exposeServices() {
		return Administration::exposeServicesUsing('DataCollection');
	}
	
	function insert() {
		$error = array();
		$error = parent::insert();
		
		if (!count($error))
			require('model_updateSearch.php');			
		return $error;
	}
	function update() {
		$error = array();
		$error = parent::update();		
		if (!count($error))
			require('model_updateSearch.php');			
		return $error;
	}
	
	function __construct($assetLink = null) {

		$assetID = null;
		if (!is_array($assetLink)) 
			$this->assetLink = $assetLink;
					
		if (!strlen($this->assetLink)) {
			if (array_key_exists("as_id", $_REQUEST)) {
				$assetID = $_REQUEST['as_id'];			
			}			
		} else {
			$assetID = $this->assetLink;
		}

		if( !$assetID )
			return;
		
		$fieldsArray = array();	
		//ss_DumpVar($this, $assetID);	
		if (strpos($assetID, "(") !== false) {
			$assetID = str_replace(')', '', str_replace('(', '', $assetID));			
		}
		
		$Q_Asset = getRow("SELECT * FROM assets WHERE as_id = $assetID");
		ss_paramKey($Q_Asset,'as_serialized',''); 
		if (strlen($Q_Asset['as_serialized'])) {
			$cereal = unserialize($Q_Asset['as_serialized']); 							
			ss_paramKey($cereal, "AST_SURVEYFIELDS", '');
			
			if (strlen($cereal['AST_SURVEYFIELDS'])) {
				$fieldsArray = unserialize($cereal['AST_SURVEYFIELDS']);
			} else {
				$fieldsArray = array();					
			}
		}
		$notsearchableFielTypes = ss_getFieldSetTypes(true, true);
		$tableDisplayFields = array();
		$tableDisplayFieldTitles = array();
		if (ss_optionExists('Survey Show ID')) {
			array_push($tableDisplayFields, "efs_id");
			array_push($tableDisplayFieldTitles, "ID");
		}
		
		$fieldsArray = array();	
		//ss_DumpVar($this, $assetID);	
		if (strpos($assetID, "(") !== false) {
			$assetID = str_replace(')', '', str_replace('(', '', $assetID));			
		}
		
		$Q_Asset = getRow("SELECT * FROM assets WHERE as_id = $assetID");
		ss_paramKey($Q_Asset,'as_serialized',''); 
		if (strlen($Q_Asset['as_serialized'])) {
			$cereal = unserialize($Q_Asset['as_serialized']); 							
			ss_paramKey($cereal, "AST_DATABASE_FIELDS", '');							
			
			if (strlen($cereal['AST_DATABASE_FIELDS'])) {
				$fieldsArray = unserialize($cereal['AST_DATABASE_FIELDS']);
			} else {
				$fieldsArray = array();
			}
		}

        $notsearchableFielTypes = ss_getFieldSetTypes(true, true);
		$tableDisplayFields = array();
		$tableDisplayFieldTitles = array();
		if (ss_optionExists('Data Collection Show ID')) {
			array_push($tableDisplayFields, "DaCoID");
			array_push($tableDisplayFieldTitles, "ID");
		}
		$tableSearchFields = array();
		$orderBys = array('DaCoSortOrder' => 'Default');
		$specialOrderBys = array();
		$tableSearchFieldsFromOptions = array();
		$specialFieldTypes = ss_getFieldSetTypes(false);
		foreach($fieldsArray as $fieldDef) {
			// Param all the settings we might have
			ss_paramKey($fieldDef,'uuid','');			
			ss_paramKey($fieldDef,'name','unknown');									
			ss_paramKey($fieldDef,'type','');									
			ss_paramKey($fieldDef,'AppearInList','no');									
			if ($fieldDef['AppearInList'] == 'yes') {
				if (!($fieldDef['type'] == 'MonthlyScheduleField' and is_array($assetLink)) ) {
									
					array_push($tableDisplayFields, "DaCo".$fieldDef['uuid']);
					if (array_key_exists($fieldDef['type'], $specialFieldTypes)) {
						if ($fieldDef['type'] == "CountryField") {
							$tableSearchFieldsFromOptions["DaCo".$fieldDef['uuid']] = array("table"=> "countries", "joinField" =>"cn_three_code", 'displayField' =>  "cn_name", 'groupField' =>  "");
						} else {
							$tableSearchFieldsFromOptions["DaCo".$fieldDef['uuid']] = array("table"=> "select_field_options", "joinField" =>"sfo_uuid", 'displayField' =>  "sfo_value",'groupField' =>  "sfo_parent_uuid");
						}
						$specialOrderBys["DaCo".$fieldDef['uuid']] = $fieldDef['name'];
					} else {
						//$tableSearchFields["DaCo".$fieldDef['uuid']] = $fieldDef['name'];
						array_push($tableSearchFields,"DaCo".$fieldDef['uuid']);
						$orderBys["DaCo".$fieldDef['uuid']] = $fieldDef['name'];
                    }
						
					array_push($tableDisplayFieldTitles, $fieldDef['name']);
				}
				
			}
			//ss_DumpVarHide($fieldDef, array_search($fieldDef['type'], $notsearchableFielTypes));				
			if (array_search($fieldDef['type'], $notsearchableFielTypes) === false ) {				
				array_push($this->searchableFields,"DaCo".$fieldDef['uuid']);						
			}

		}

        if (ss_optionExists('Advanced Data Collection')) {
			array_push($tableDisplayFields, "DaCoApproved");
			array_push($tableDisplayFieldTitles, "Approved");
            array_push($tableDisplayFields, "DaCoUserName");
			array_push($tableDisplayFieldTitles, "Uploaded by");
            array_push($tableDisplayFields, "DaCoDate");
			array_push($tableDisplayFieldTitles, "Date");
		}

		$setting = array(
			'prefix'					=>	'DataCollection',
			'singular'					=>	'Record',
			'plural'					=>	'Records',
			'tableName'					=>	'DataCollection_'.$assetID,
			'tablePrimaryKey'			=>	'DaCoID',
			'tableOrderBy'				=>	$orderBys,			
			'tablePrefix'				=>	'DaCo',
			'tableSpecialOrderBy'		=>	$specialOrderBys,
			'tableDisplayFields'		=>	$tableDisplayFields,
			'tableSearchFields'			=>	$tableSearchFields,
			'tableSearchFieldsFromOption'=>	$tableSearchFieldsFromOptions,
			'tableDisplayFieldTitles'	=>	$tableDisplayFieldTitles,
			'assetLink'					=>	$assetID,
			'tableSortOrderField'		=>	'DaCoSortOrder',
		);
		if (ss_optionExists('Data Collection For Natcoll Courses')) {
			$setting['listManageOptions']  = array(
				'Edit Auckland'		=>"index.php?act=CourseRegionsAdministration.Edit&Region=Auckland&DaCoID=[DaCoID]&BreadCrumbs=[BreadCrumbs]&BackURL=[BackURL]",
				'Edit Christchurch'	=>"index.php?act=CourseRegionsAdministration.Edit&Region=Christchurch&DaCoID=[DaCoID]&BreadCrumbs=[BreadCrumbs]&BackURL=[BackURL]",
				'Edit Wellington'	=>"index.php?act=CourseRegionsAdministration.Edit&Region=Wellington&DaCoID=[DaCoID]&BreadCrumbs=[BreadCrumbs]&BackURL=[BackURL]",
			);
		}

		parent::__construct($setting);
		

		if (ss_optionExists("Data Collection Record Window Title")) {
			$this->addField(new TextField (array(
				'name'			=>	'DaCoWindowTitle',
				'displayName'	=>	'Window Title',
				'note'			=>	null,
				'required'		=>	false,
				'verify'		=>	false,
				'unique'		=>	false,
				'size'	=>	'60',	'maxLength'	=>	'255',
				'rows'	=>	'6',	'cols'		=>	'40',
			)));
		}
		// add the customized user field	

		$this->addCustomizedFields($fieldsArray, "DaCo");

        //Advanced Data Collection, added 15.8.05 by Briar
        if (ss_OptionExists('Advanced Data Collection')){
            //briar - there must be a better way to check if Admin??
            $isAdmin = array_key_exists(1,$_SESSION['User']['user_groups']);
            if (!array_key_exists("Service", $_REQUEST) and $isAdmin) {

        		$this->addField(new CheckBoxField (array(
        			'name'			=>	'DaCoApproved',
        			'displayName'	=>	'Approved',
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
            //briar -need to do something like this that won't get overwritten when edited..

            $this->addField(new HiddenField (array(
    			'name'			=>	'DaCoUserName',
    			'displayName'	=>	'Uploaded By',
    			'note'			=>	NULL,
    			'required'		=>	FALSE,
    			'verify'		=>	FALSE,
                'defaultValue'  =>  $_SESSION['User']['us_first_name'] . ' ' . $_SESSION['User']['us_last_name'],
    			'unique'		=>	FALSE,
    			'size'	=>	'10',	'maxLength'	=>	'10',
    			'rows'	=>	'6',	'cols'		=>	'40',
    			'linkQueryAction'	=>	NULL,
    			'linkQueryValueField'	=>	NULL,
    			'linkQueryDisplayField'	=>	NULL,
    		)));

            $this->addField(new HiddenField (array(
    			'name'			=>	'DaCoDate',
    			'displayName'	=>	'Uploaded Date',
    			'note'			=>	NULL,
    			'required'		=>	FALSE,
    			'verify'		=>	FALSE,
                'defaultValue'  =>  date('Y-m-d'),
    			'unique'		=>	FALSE,
    			'size'	=>	'10',	'maxLength'	=>	'10',
    			'rows'	=>	'6',	'cols'		=>	'40',
    			'linkQueryAction'	=>	NULL,
    			'linkQueryValueField'	=>	NULL,
    			'linkQueryDisplayField'	=>	NULL,
    		)));

        }
	}
}
?>
