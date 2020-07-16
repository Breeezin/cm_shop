<?php

	$this->param('Deleted', false);
	
	if (array_key_exists('as_id',$this->ATTRIBUTES)) {
			
		$nextPart = $this->ATTRIBUTES['as_id'];
		$assetPath = '';
		$depth = 0;
		$whereSQL = 'AND as_deleted != 1';
		
		if ($this->ATTRIBUTES['Deleted']) {
			$whereSQL = '';
		}
		
		while (strlen($nextPart) > 0) {
			$depth++;
			
			// Do the query
			$result = query("
				SELECT as_name, as_parent_as_id FROM assets WHERE as_id = $nextPart $whereSQL
			");
			$row = $result->fetchRow();
			
			//$assetPath = "/{$row['as_name']}{$assetPath}";
			// If this is level 1 asset, skip out the index.php as that is assumed 
			$nextPart = $row['as_parent_as_id'];

			if (strlen($nextPart) == 0 and strlen($assetPath)) {
				// we dont want to grab index.php if there is already an assetpath :)
				// e.g.  "Home" is better than "index.php/Home"
			} else {
				$assetPath = "/{$row['as_name']}{$assetPath}";
			}
		
			if ($depth > 50) return null;
			
		}
		
		return $assetPath;
	} else {
		return NULL;			
	}
?>
