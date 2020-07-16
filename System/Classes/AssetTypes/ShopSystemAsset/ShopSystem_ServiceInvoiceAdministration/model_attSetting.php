<?php 
	if (array_key_exists('DoAction', $this->ATTRIBUTES)) {
		
		foreach($allCategoriesResult as $aCat) {
			$this->param("Att_{$aCat['ca_id']}", ""); 
			$attValues = '';
			if (is_array($this->ATTRIBUTES["Att_{$aCat['ca_id']}"])) {
				$attValues = ArrayToList($this->ATTRIBUTES["Att_{$aCat['ca_id']}"]);
			}
			$field = "Ca{$this->ATTRIBUTES['Type']}Setting";
			$Q_AttUpdate = query("
				UPDATE 
					$this->tableName 
				SET 
					$field = '{$attValues}'
				WHERE ca_id = {$aCat['ca_id']}
			");
						
		}
		location("index.php?act={$this->ATTRIBUTES['act']}&as_id={$this->assetLink}&Type={$this->ATTRIBUTES['Type']}");
	}
?>