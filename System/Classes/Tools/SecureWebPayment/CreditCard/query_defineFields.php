<?php
		$this->fieldSet = new FieldSet(array(
			'formName'	=>	'CreditCardForm',
		));
		
		$CreditCardType ='';
		ss_paramKey($webpay->ATTRIBUTES, 'Edit', 0);
		
		if ($webpay->ATTRIBUTES['Edit']) {
			
			if (array_key_exists('TrCreditCardType', $webpay->ATTRIBUTES) AND strlen($webpay->ATTRIBUTES['TrCreditCardType'])) {
				$Q_CreditCardType = query("SELECT * FROM credit_card_types WHERE cct_id =".(int)$webpay->ATTRIBUTES['TrCreditCardType']);		// SQL injection
				$temp = $Q_CreditCardType->fetchRow();
				$CreditCardType = $temp['cct_name'];
				//ss_DumpVar($CreditCardType,'credit crd type',true); 
			}
		} else {
			if (array_key_exists('TrCreditCardType', $webpay->cereal) AND strlen($webpay->cereal['TrCreditCardType'])) {
				$Q_CreditCardType = query("SELECT * FROM credit_card_types WHERE cct_id =".(int)$webpay->cereal['TrCreditCardType']);		// SQL injection
				$temp = $Q_CreditCardType->fetchRow();
				$CreditCardType = $temp['cct_name'];
			} 
		}
	
		$this->fieldSet->addField(new CreditCardNumberField (array(
					'name'			=>	'TrCreditCardNumber',
					'displayName' 	=>	'Credit Card Number',					
					'note'			=>	NULL,
					'required'		=> 	TRUE,
					'verify'		=>	FALSE,
					'unique'		=>	FALSE,
					'cardType' 		=>	$CreditCardType,					
		)));
		
		$this->fieldSet->addField( new SelectField (array(
			'name'			=>	'TrCreditCardType',
			'displayName'	=>	'Credit Card Type',
			'note'			=>	NULL,
			'required'		=>	TRUE,
			'verify'		=>	FALSE,
			'multi'		=>	FALSE,
			'unique'		=>	FALSE,			
			'onChange'		=> 	'OnChange="changeCVV(value);"',			
			'linkQueryAction'	=>	'CreditCardType.WebPayConfig',
			'linkQueryValueField'	=>	'cct_id',
			'linkQueryDisplayField'	=>	'cct_name',
		)));
		
		$this->fieldSet->addField( new TextField (array(
						'name'			=>	'TrCreditCardHolder',
						'displayName' 	=>	'Holder Name',						
						'note'			=>	NULL,
						'required'		=>	TRUE,
						'verify'		=>	FALSE,
						'unique'		=>	FALSE,
		)));
		
		$this->fieldSet->addField( new TextField (array(
						'name'			=>	'TrCreditCardCompany',
						'displayName' 	=>	'Company Name',						
						'note'			=>	NULL,
						'required'		=>	false,
						'verify'		=>	FALSE,
						'unique'		=>	FALSE,
		)));
		if (ss_optionExists('Web Pay CVV2')) {
			$defaultCVV2Length = 4;
			$this->fieldSet->addField( new IntegerField (array(
							'name'			=>	'TrCreditCardCVV2',
							'displayName' 	=>	'CVV2',						
							'note'			=>	NULL,
							'required'		=>	true,
							'verify'		=>	FALSE,
							'unique'		=>	FALSE,
							'maxLength'		=> 	$defaultCVV2Length,
							'size'			=>	10,
			)));
		}
		$this->fieldSet->addField( new CreditCardExpiryDateField (array(
						'name'			=>	'TrCreditCardExpiry',
						'displayName' 	=>	'Expiry Date',
						'note'			=>	NULL,
						'required'		=>	TRUE,
						'verify'		=>	FALSE,
						'unique'		=>	FALSE,
		)));

?>
