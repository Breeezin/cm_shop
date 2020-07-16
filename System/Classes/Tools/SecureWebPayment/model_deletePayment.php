<?php
 
	$this->param("tr_id");
	if (strlen($this->ATTRIBUTES['tr_id'])) {
					
		$Q_deleteTransaction = query("
				DELETE FROM transactions
				WHERE tr_id =".$this->ATTRIBUTES['tr_id']
		);		
	}

?>