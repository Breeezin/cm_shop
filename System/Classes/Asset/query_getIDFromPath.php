<?php
	if (array_key_exists('AssetPath',$this->ATTRIBUTES)) {

		// Empty assetPath equals null asset
		if ($this->ATTRIBUTES['AssetPath'] == NULL) return NULL;
	
		if (strstr($this->ATTRIBUTES['AssetPath'], 'System') !== false) {
			return null;
		}
		$fromSQL = '';	$whereSQL = '';
		$comma = '';	$counter = 1;
		$counterPlusOne = 2;
		
		$this->ATTRIBUTES['AssetPath'] = ss_withoutPreceedingSlash($this->ATTRIBUTES['AssetPath']);
		
		$assetPathExploded = array_reverse(explode('/',$this->ATTRIBUTES['AssetPath']));
		$temp = array();
		foreach($assetPathExploded as $assetName) {
			if (($assetName != NULL) && (strlen($assetName) > 0)) array_push($temp,$assetName);
		}

		// add index.php if we need to
		if ($assetName != basename($_SERVER['SCRIPT_NAME'])) {
			array_push($temp,basename($_SERVER['SCRIPT_NAME']));
		}
		
		$assetPathExploded = $temp;
		$assetDepth = count($assetPathExploded);
		
		// Construct the query SQL as if by magic
		foreach($assetPathExploded as $assetName) {

			$assetName = escape($assetName);	
			
			if ($assetName == "Custom" and array_key_exists('HTTP_USER_AGENT',$_SERVER)) {
				if (!array_key_exists('HTTP_USER_AGENT',$_SERVER) or stristr($_SERVER['HTTP_USER_AGENT'],'WebReaper') !== false ) {
					die('Invalid path.');
				}
			}
			
			
			$fromSQL .= "{$comma} assets AS assets{$counter}";
			$whereSQL .= "assets{$counter}.as_name LIKE '{$assetName}'  AND assets{$counter}.as_deleted != 1 AND  ";
			if ($counter != $assetDepth) {
				$whereSQL .= "assets{$counter}.as_parent_as_id = assets{$counterPlusOne}.as_id AND ";
			} else {
				$whereSQL .= "assets{$counter}.as_parent_as_id IS NULL";
			}
			
			$comma = ',';
			$counter++;
			$counterPlusOne++;
			if ($counter > 20) die('Invalid path.');
		}
		
		// Do the query
		$result = query("
			SELECT assets1.as_id as ID FROM $fromSQL
			WHERE $whereSQL
		");
		$row = $result->fetchRow();
		
		return $row['ID'];
	} else {
		return NULL;			
	}
?>
