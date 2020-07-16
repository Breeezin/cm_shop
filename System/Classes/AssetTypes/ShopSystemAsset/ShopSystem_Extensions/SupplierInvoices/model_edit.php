<?php
	$errors = array();
	if (array_key_exists('Submit',$this->ATTRIBUTES)) {
		// Nothing to validate

		// Update notes and invoice number
		$Q_UpdateOrder = query("
			UPDATE shopsystem_order_sheets
			SET ors_invoice_number = '".escape($this->ATTRIBUTES['ors_invoice_number'])."',
				ors_notes = '".escape($this->ATTRIBUTES['ors_notes'])."'
			WHERE ors_id = {$this->ATTRIBUTES['ors_id']}
		");	

		// Update the back stamp codes and quantities
		while ($row = $Q_OrderSheetItems->fetchRow()) {
			$shipping = $this->ATTRIBUTES['Shipping'.$row['orsi_id']];
			if (is_numeric($shipping) and $shipping > 0) {
				// good
			} else {
				// bad.. use shipping value of 1
				$shipping = 0;	
			}

			// see if the back stamp code changed or not
			$updateDateSQL = '';
			if (strlen($this->ATTRIBUTES['BackStampCode'.$row['orsi_id']])) {
				if ($this->ATTRIBUTES['BackStampCode'.$row['orsi_id']] != $row['orsi_bs_code']) {
					// .. if so, update the date changed
					$updateDateSQL = ', orsi_date_changed = NOW()';	
				}
			}

			$backStampCode = "'".escape($this->ATTRIBUTES['BackStampCode'.$row['orsi_id']])."'";
			if (strlen($backStampCode) == 2) {
				$backStampCode = 'NULL';
			}

			$Q_UpdateItem = query("
				UPDATE shopsystem_order_sheets_items
				SET orsi_bs_code = $backStampCode,
					orsi_shipping = $shipping				
					$updateDateSQL
				WHERE orsi_id = {$row['orsi_id']}
			");	
		}

		// fix the total
		$this->fixTotal($this->ATTRIBUTES['ors_id']);		

/*
		$this->param('ors_invoice_date');
		$this->param('ors_import');

		$separator = '/';
		$date = $this->atts['ors_invoice_date'];
		if (date_error($this->ATTRIBUTES['ors_invoice_date'],$separator) !== null) {
			array_push($errors,array(date_error($this->ATTRIBUTES['ors_invoice_date'],$separator)));
		} else {
			$day = ListGetAt($date,1,$separator);
			$month = ListGetAt($date,2,$separator);
			$year = ss_AdjustTwoDigitYear(ListGetAt($date,3,$separator));
			$dateOutput = ss_TimeStampToSQL(mktime(0,0,0,$month,$day,$year));
			//die($dateOutput);
		
			$Q_UpdateOrder = query("
				UPDATE shopsystem_order_sheets
				SET ors_invoice_date = $dateOutput
				WHERE ors_id = {$this->ATTRIBUTES['ors_id']}
			");	
		}
		
		
		if (!is_numeric($this->ATTRIBUTES['ors_import'])) {
			array_push($errors,array("Importe is invalid."));
		} else {
			$Q_UpdateOrder = query("
				UPDATE shopsystem_order_sheets
				SET ors_import = ".$this->ATTRIBUTES['ors_import']."
				WHERE ors_id = {$this->ATTRIBUTES['ors_id']}
			");	
		}
		*/
		
		if (count($errors) == 0) {
			locationRelative('index.php?act=shopsystem_order_sheets.View&BackURL='.ss_URLEncodedFormat($this->ATTRIBUTES['BackURL']).'&ors_id='.$this->ATTRIBUTES['ors_id']);
		}
	}

?>
