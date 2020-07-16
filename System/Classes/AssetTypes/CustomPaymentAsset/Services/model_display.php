<?php
	$success = false;
	$errors = array();
	
	if (array_key_exists("DoAction",$asset->ATTRIBUTES)) {
		
		$fieldSet->loadFieldValuesFromForm($asset->ATTRIBUTES);
	
		$errors = $fieldSet->validate();
		$fromName = '';
		$totalPrice = 0;
		$quan_no_zero = false;
		foreach($productDetails as $id => $detail) {					
			if ($this->atts['Quantity_'.$id] != 0) {
				$quan_no_zero = true;
				$totalPrice += ($this->atts['Quantity_'.$id] * $this->atts['Product_'.$id]);
			}						
		}
		if ($totalPrice == 0) {
			$errors['total'] = array('Please enter a valid quantity.');
		}
		
		if (count($errors) == 0) {				
			

			$prepareTransaction = new Request("WebPay.PreparePayment", 			
				array(	'tr_currency_link' => 554, 
						'tr_client_name' => $fromName,)
			);
			$this->ATTRIBUTES['tr_id'] = $prepareTransaction->value['tr_id'];
			$this->ATTRIBUTES['tr_token'] = $prepareTransaction->value['tr_token'];
				
			$CuPaCustomEmail = '';
			$htmlEmail = '<table width="100%" border="0" cellpadding="5" cellspacing="0" bgcolor="#FFFFFF" class="Border">';
			$htmlEmail .= "<tr><td valign=\"top\"><strong>Order Reference</strong></td><td>".ss_getTrasacationRef($this->ATTRIBUTES['tr_id'])."</td></tr>";
			foreach($fieldsArray as $fieldDef) {
		
				// Param all the settings we might need
				ss_paramKey($fieldDef,'name','Unknown');
				ss_paramKey($fieldDef,'type','Unknown');
				ss_paramKey($fieldDef,'uuid','');		
				ss_paramKey($fieldDef,'prefixed',0);		
		
				// Construct some field desciption html
				$fieldDescriptionHTML = "<td valign=\"top\"><strong>".ss_HTMLEditFormat($fieldDef['name'])."</strong></td>";
				
					
				// Figure out a from name
				if ($fromName == null and $fieldDef['type'] == 'NameField') {
					$fromName = $fieldSet->getFieldDisplayValue('F'.$fieldDef['uuid']);
				}
				if ($fieldDef['type'] == 'EmailField') {
					$CuPaCustomEmail = strip_tags($fieldSet->getFieldDisplayValue('F'.$fieldDef['uuid']));
				}
				// Get the value to display
				$fieldCellHTML = '';
				if ($fieldDef['type'] != 'Comment') {
					$fieldCellHTML = "<td>".$fieldSet->getFieldDisplayValue('F'.$fieldDef['uuid'])."</td>";
				}

				// join it all up
				$htmlEmail .= "<tr>$fieldDescriptionHTML $fieldCellHTML</tr>";
			}				
						
			$htmlEmail .= "</table><BR>";
			$data = array();
			$data['Atts'] = $this->ATTRIBUTES;
			$data['Products'] = $productDetails;
			$htmlEmail .= $this->processTemplate('OrderTable',$data );
            $thankyou = ss_parseText($asset->cereal['AST_CUSTOMPAYMENT_THANK_YOU_PAGE']);
            $htmlEmail .= "<BR><br>" . $thankyou;

			// record in the database		
			if (strlen($fromName) > 255) {
				$fromName = substr($fromName,0,255);	
			}
			
			
			$updateTransaction  = new Request("WebPay.PreparePayment", array(
				'tr_id' => $this->ATTRIBUTES['tr_id'], 
				'tr_total' => $totalPrice, 
				'tr_currency_link' =>554, 
				'tr_client_name' => $fromName,
			));			

			$id = newPrimaryKey('CustomPayments','CuPaID');
            $this->ATTRIBUTES['Currency'] = isset($this->ATTRIBUTES['Currency']) ? $this->ATTRIBUTES['Currency'] : 554;


			$Q_Insert = query("
				INSERT INTO CustomPayments
					(CuPaID, CuPaTransactionLink, CuPaRecorded, CuPaAssetLink, CuPaCustomEmail,CuPaTotal, CuPaEmailContent, CuPaCurrencyCode)
				VALUES
					($id, {$this->ATTRIBUTES['tr_id']}, NOW(), ".$assetID.", '".escape($CuPaCustomEmail)."',$totalPrice, '".escape($htmlEmail)."', '{$this->ATTRIBUTES['Currency']}')
			");	

			$normalSite = $GLOBALS['cfg']['plaintext_server'];
			$normalSite = ss_withTrailingSlash($normalSite);
			$backURL = ss_URLEncodedFormat("{$normalSite}$assetPath/Service/Completed/tr_id/{$this->ATTRIBUTES['tr_id']}/tr_token/{$this->ATTRIBUTES['tr_token']}/CuPaID/{$id}");
			$this->param("PaymentOption");
			$secureSite = $GLOBALS['cfg']['secure_server'];
			$secureSite = ss_withTrailingSlash($secureSite);
			
			$accessCode = '';
			if (array_key_exists('AccessCode', $_REQUEST))
				$accessCode = $_REQUEST['AccessCode'];
			else if (array_key_exists('AccessCode', $_SESSION)) 
				$accessCode = $_SESSION['AccessCode'];
									
            location($secureSite."index.php?act=WebPay.{$this->ATTRIBUTES['PaymentOption']}&AccessCode=$accessCode&tr_id={$this->ATTRIBUTES['tr_id']}&tr_token={$this->ATTRIBUTES['tr_token']}&BackURL={$backURL}&as_id={$assetID}");
			$success = true;
		}
	} else {
		$fieldSet->loadFieldValuesFromForm($asset->ATTRIBUTES,true);
	}
	
	
?>