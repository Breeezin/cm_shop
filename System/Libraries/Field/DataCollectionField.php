<?php 
	class DataCollectionField extends SelectFromArrayField {
	
	var $assetID = null;
	var $options	= array();
	var $dataFields = array();		
	var $detailTemplate = null;		
	
	function displayFullDetails($value) {
		//ss_DumpVarDie($this);
		if ($this->multi) {
			$result = '';
			foreach (ListToArray($value) as $aValue) {
				$result .= "<BR>".ss_getDataCollectionRecordDetail($this->assetID, $aValue, $this->detailTemplate, $this->dataFields);			
			}
			return $result;
		} else {
			return ss_getDataCollectionRecordDetail($this->assetID, $value, $this->detailTemplate, $this->dataFields);
		}
	}
}
?>