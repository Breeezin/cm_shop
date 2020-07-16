<?php
	if (!array_key_exists('noReturn',$this->ATTRIBUTES)) {
		$this->param('BackURL');
	}
	
    // update assets table to admin ownership :: changed 30 Nov 2005
	$Q_OwnAssets = query("
        update assets set as_owner_au_id =1 
        where as_owner_au_id =".safe($this->ATTRIBUTES[$this->tablePrimaryKey])."
	");

	// check whether this use owns any asset
	$Q_OwnAssets = query("
		SELECT Count(as_id) AS HowMany, as_owner_au_id 
		FROM assets 
		WHERE as_owner_au_id IN (".safe($this->ATTRIBUTES[$this->tablePrimaryKey]).")
			AND (as_deleted = 0 or as_deleted IS NULL)
		GROUP BY as_owner_au_id 
	");
	if ($Q_OwnAssets->numRows()) {		
		$disableIDs = array(); /// ids that cannot be deleted
		$ids = ListToArray($this->ATTRIBUTES[$this->tablePrimaryKey]);
		while($temp = $Q_OwnAssets->fetchRow()) {
			if ($temp['HowMany'] > 0) {
				array_push($disableIDs, $temp['as_owner_au_id']);				
			}
		}
		$newIDs = array_diff($ids, $disableIDs);
		if (!count($newIDs)) {
			if (!array_key_exists('noReturn',$this->ATTRIBUTES)) {
				location($this->ATTRIBUTES['BackURL']);
			}		
		} else {
			$this->ATTRIBUTES[$this->tablePrimaryKey] = ArrayToList($newIDs);
			$this->parentDelete();
		}
	} else {
		$this->parentDelete();
	}
	
	
	// Return to the list of records
	

?>
