<?
	$errors = array();
	$done = false;
	$this->param('Amount','');
	if (array_key_exists('Submit',$this->atts)) {
		
		if (!strlen($this->atts['Amount'])) {
			$errors['Amount'] = array('Please enter a total cost');
		} else if (!is_numeric($this->atts['Amount'])) {
			$errors['Amount'] = array('Total cost must be a number');
		}
		
		if (count($errors) == 0) {
			startTransaction();
			
			$Q_Insert = query("
				INSERT INTO ShopSystem_BankBalances
					(BaBaDate, BaBaAmount)
				VALUES 
					(NOW(), ".safe($this->atts['Amount']).")
			");
			
			commit();

			$done = true;
			
		}
		
	}

?>