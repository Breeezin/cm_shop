<?php
requireOnceClass('Administration');
class SurveyAdministration extends Administration {
	
	var $searchableFields = array();
	
	function exposeServices() {
		$prefix = "ShopSystem";
		return array_merge(Administration::exposeServicesUsing('Survey'),array(
			'Survey.Export'	=>	array('method'	=>	'export'),
            'SurveyAdministration.DeleteAllRecords' => array('method'	=>	'deleteAll'),
		));
	}

	function entries() {
		if (array_key_exists($this->tablePrimaryKey,$this->ATTRIBUTES))
            $this->primaryKey = $this->ATTRIBUTES[$this->tablePrimaryKey];
		require('EntriesQuery.php');
		require('EntriesDisplay.php');
        }

   	function export() {
		$this->display->layout = 'None';
		require('ExportSurvey.php');
	}

   	function deleteAll() {
		$this->display->layout = 'None';

    	$this->param('as_id','');
    	$this->param('BackURL','');

        if( strlen($this->ATTRIBUTES['as_id']) == 0 )
            die('Invalid Request');

    	$Q_Users = query("
    		TRUNCATE TABLE Survey_{$this->ATTRIBUTES['as_id']};
    	");

		location($this->ATTRIBUTES['BackURL']);
	}

	function insert() {
		$error = array();
		$error = parent::insert();

		return $error;
	}
	function update() {
		$error = array();
		$error = parent::update();		

		return $error;
	}

    // Overwritten to prevent Admin editing
	function edit() {
		if (array_key_exists($this->tablePrimaryKey,$this->ATTRIBUTES)) $this->primaryKey = $this->ATTRIBUTES[$this->tablePrimaryKey];
		if (array_key_exists('as_id',$this->ATTRIBUTES)) $this->assetLink = $this->ATTRIBUTES['as_id'];

		require('EditAction.php');
		require('EditDisplay.php');
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

		
		$tableSearchFields = array();
		$orderBys = array('efs_timestamp' => 'Date');
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

					array_push($tableDisplayFields, "Su".$fieldDef['uuid']);
					if (array_key_exists($fieldDef['type'], $specialFieldTypes)) {
						if ($fieldDef['type'] == "CountryField") {
							$tableSearchFieldsFromOptions["Su".$fieldDef['uuid']] = array("table"=> "countries", "joinField" =>"cn_three_code", 'displayField' =>  "cn_name", 'groupField' =>  "");
						} else {
							$tableSearchFieldsFromOptions["Su".$fieldDef['uuid']] = array("table"=> "select_field_options", "joinField" =>"sfo_uuid", 'displayField' =>  "sfo_value",'groupField' =>  "sfo_parent_uuid");
						}
						$specialOrderBys["Su".$fieldDef['uuid']] = $fieldDef['name'];
					} else {
						//$tableSearchFields["Su".$fieldDef['uuid']] = $fieldDef['name'];
						array_push($tableSearchFields,"Su".$fieldDef['uuid']);
						$orderBys["Su".$fieldDef['uuid']] = $fieldDef['name'];
						
					}
						
					array_push($tableDisplayFieldTitles, $fieldDef['name']);
				}
				
			}
			//ss_DumpVarHide($fieldDef, array_search($fieldDef['type'], $notsearchableFielTypes));				
			if (array_search($fieldDef['type'], $notsearchableFielTypes) === false ) {				
				array_push($this->searchableFields,"Su".$fieldDef['uuid']);
			}
		
		}

		parent::__construct( array(
			'prefix'					=>	'Survey',
			'singular'					=>	'Record',
			'plural'					=>	'Records',
			'tableName'					=>	'Survey_'.$assetID,
			'tablePrimaryKey'			=>	'efs_id',
			'tableOrderBy'				=>	$orderBys,			
			'tablePrefix'				=>	'Su',
			'tableSpecialOrderBy'		=>	$specialOrderBys,
			'tableDisplayFields'		=>	$tableDisplayFields,
			'tableSearchFields'			=>	$tableSearchFields,
			'tableSearchFieldsFromOption'=>	$tableSearchFieldsFromOptions,
			'tableDisplayFieldTitles'	=>	$tableDisplayFieldTitles,						
			'assetLink'					=>	$assetID,
			'tableSortOrderField'		=>	'efs_timestamp',
		) );


		if (ss_optionExists("Survey Record Window Title")) {
			$this->addField(new TextField (array(
				'name'			=>	'SuWindowTitle',
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
		$this->addCustomizedFields($fieldsArray, "Su");


	}

}
?>
