<?
	$errors = array();
	$this->param('Amount','');
	$this->param('Ref','');
	
	if (array_key_exists("Submit",$this->atts)) {
		
		if (!strlen($this->atts['Ref'])) {
			$errors['Ref'] = array('Please enter a reference number');				
		}

		if (!strlen($this->atts['Amount'])) {
			$errors['Amount'] = array('Please enter a total cost');				
		} else if (!is_numeric($this->atts['Amount'])) {
			$errors['Amount'] = array('Total cost must be a number');				
		}
		
		if (count($errors) == 0) {
			startTransaction();
			$newID = newPrimaryKey('shopsystem_shipping_charges','ssc_id');
			
			$Q_Insert = query("
				INSERT INTO shopsystem_shipping_charges
					(ssc_id, ssc_amount, ssc_date, ssc_reference, ssc_paid)
				VALUES 
					($newID, ".safe($this->atts['Amount']).", NOW(), '".escape($this->atts['Ref'])."', NULL)
			");
			
			$Q_Allocate = query("
				UPDATE shopsystem_shipped_products
				SET shp_ssc_id = $newID
				WHERE shp_ssc_id IS NULL
			");
			
			
			commit();
			
			location('index.php?act=shopsystem_shipping_charges.List');
		}
		
	}


?>