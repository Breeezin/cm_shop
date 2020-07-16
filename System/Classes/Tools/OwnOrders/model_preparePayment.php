<?php 
	$this->param('tr_id','');
	$this->param('tr_currency_link');
	
	$this->param('tr_total','');
	$this->param('tr_client_email',null);	
	$result = null;
	
	// if tr_id is null, then insert new transcation
	if (!strlen($this->ATTRIBUTES['tr_id'])) {
		$this->param('tr_client_name');
		$this->param('tr_reference',null);
		
		
		//$trID = newPrimaryKey($this->tableName,$this->tablePrimaryKey,200);
		
		$trToken = md5(rand());
		if ($this->ATTRIBUTES['tr_reference'] === null) {
			$trReference = date('Ymd').'-'.date('His').'-'.rand(0,100);
		} else {
			$trReference = $this->ATTRIBUTES['tr_reference'];
		}
			
		$Q_NewTransaction = query("INSERT INTO 
				$this->tableName (tr_currency_link, tr_token, tr_reference, tr_client_name) 
				VALUES 
				({$this->ATTRIBUTES['tr_currency_link']}, '$trToken', '$trReference', '".escape($this->ATTRIBUTES['tr_client_name'])."')"
		);		
		$transation = getRow("SELECT tr_id FROM transactions WHERE tr_token LIKE '$trToken' ");
		$trID = $transation['tr_id'];
		$result = array('tr_id'=>$trID, 'tr_token'=>$trToken);
		
		return $result;
	} else {
		// if tr_id is not null and tr_total has a value then upate the transaction
		$clientName = '';
		if (array_key_exists('tr_client_name', $this->ATTRIBUTES))  {
			$clientName = ", tr_client_name = '".escape($this->ATTRIBUTES['tr_client_name'])."'";
		}
		if (!strlen($this->ATTRIBUTES['tr_total'])) $this->ATTRIBUTES['tr_total'] = '0';
		$Q_UpdateTrascation = query("
				UPDATE transactions 
				SET 
					tr_total = {$this->ATTRIBUTES['tr_total']}, 
					tr_client_email = '".escape($this->ATTRIBUTES['tr_client_email'])."',
					tr_currency_link = {$this->ATTRIBUTES['tr_currency_link']} 
					$clientName
					
				WHERE tr_id = {$this->ATTRIBUTES['tr_id']}
		");
		return $result;
	}
	
	return array();
?>