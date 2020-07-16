<?php 
	if (array_key_exists('DoAction', $this->ATTRIBUTES)) { 
		//ss_DumpVarDie($this);
		$this->param('TableSort', array());	
		
		foreach ($this->ATTRIBUTES['TableSort'] as $sortNum => $id) {
			$Q_Update = query("
				UPDATE 
					{$this->tableName} 
				SET 
					{$this->tableSortOrderField} = $sortNum
				WHERE 
					$this->tablePrimaryKey = $id					
			");
		}
		
		location($this->ATTRIBUTES['BackURL']);
	}
?>