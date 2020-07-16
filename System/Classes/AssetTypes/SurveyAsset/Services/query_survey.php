<?php

	requireOnceClass('FieldSet');

	ss_paramKey($asset->cereal,$this->fieldPrefix.'FIELDS','');
	// Load the field set
	if (strlen($asset->cereal[$this->fieldPrefix.'FIELDS'])) {
		$fieldsArray2 = unserialize($asset->cereal[$this->fieldPrefix.'FIELDS']);
	} else {
		$fieldsArray2 = array();
	}


	$fieldSet = new FieldSet();
	$fieldsArray = array();
	$preCounter = 0;
    foreach ($fieldsArray2 as $customField) {
		$fieldsArray["$preCounter"] = $customField;
		$preCounter++;
	}
    $fieldSet->assetLink = $assetID;
    $fieldSet->tablePrimaryMinValue = null;
    $fieldSet->tablePrimaryKey = 'efs_id';
    $fieldSet->tableName = 'Survey_'.$assetID;
	$fieldSet->addCustomizedFields($fieldsArray,'Su');

?>
