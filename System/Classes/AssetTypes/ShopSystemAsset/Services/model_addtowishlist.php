<?php

	if (array_key_exists('DoAction',$this->ATTRIBUTES)) {
		// add the product into the wish list
		
		$this->param('Email','');
		$this->param('Key','');
		
		if (strlen($this->ATTRIBUTES['Key']) and strlen($this->ATTRIBUTES['Email'])) {
			
			$Q_Lookup = query("
				SELECT * FROM users
				WHERE us_email LIKE '".escape($this->ATTRIBUTES['Email'])."'
			");
			if ($Q_Lookup->numRows() > 0) {
			
				$user = $Q_Lookup->fetchRow();
				
				$optionID = ListLast($this->ATTRIBUTES['Key'],'_');
				
				$option = getRow("
					SELECT * FROM shopsystem_product_extended_options	
					WHERE pro_id = ".safe($optionID)."
				");
				
				$stockCode = $option['pro_stock_code'];
				
				// Delete it just in case its already there
				$Q_Clean = query("
					DELETE FROM shopsystem_stock_notifications
					WHERE stn_stock_code LIKE '".escape($stockCode)."'
						AND stn_email LIKE '".escape($this->ATTRIBUTES['Email'])."'
						AND stn_site_folder LIKE '".escape($GLOBALS['cfg']['folder_name'])."'
				");
				
				// Now insert a fresh record
				$Q_Insert = query("
					INSERT INTO shopsystem_stock_notifications
						(stn_stock_code, stn_email, stn_us_id, stn_site_folder)
					VALUES
						('".escape($stockCode)."','".escape($this->ATTRIBUTES['Email'])."', {$user['us_id']}, '".escape($GLOBALS['cfg']['folder_name'])."')
				");
				
			} else {
				$error = '<p style="color:red;">You must be an existing customer to add a product to your wish list.</p>';
			}
					
		} else {
			$error = '<p style="color:red;">Please ensure you select a product and enter your email address.</p>';
		}
		
	}

?>