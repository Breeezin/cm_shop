<?php

	if (array_key_exists('as_id',$this->ATTRIBUTES)) {
		
		$nextPart = $this->ATTRIBUTES['as_id'];
		$ancestors = array();
		$depth = 0;
		
		while (strlen($nextPart) > 0) {
			$depth++;
			
			// Do the query
			$row = getRow("
				SELECT as_name, as_parent_as_id FROM assets WHERE as_id = $nextPart AND as_deleted != 1
			");
			
			//$assetPath = "/{$row['as_name']}{$assetPath}";
			// If this is level 1 asset, skip out the index.php as that is assumed 
			$nextPart = $row['as_parent_as_id'];

			if (strlen($nextPart) == 0 and count($ancestors)) {
				// we dont want to grab index.php if there is already an assetpath :)
				// e.g.  "Home" is better than "index.php/Home"
			} else {
				//$assetPath = "/{$row['as_name']}{$assetPath}";
				$ancestors[$nextPart] = 1;
			}
		
			if ($depth > 50) return null;			
		}
		
		return $ancestors;
	} else {
		
		return NULL;			
	}
?>