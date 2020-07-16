<?php 
		
	// Credit card number is changed from now.
	// last 4 digit remains as they were but other digits are replaced with 'x'
	/*
	$lastFour = substr($webpay->cereal['TrCreditCardNumber'],-4);				
	$xes = ereg_replace('[[:alnum:]]', 'x', substr($webpay->cereal['TrCreditCardNumber'], 0,strlen($webpay->cereal['TrCreditCardNumber'])-4));
		
	$cardNum = $xes.$lastFour;			
	
	// Credit card expiry year is replaced with 'x'
	$separator = '/';
	list($month,$year) = explode($separator,$webpay->cereal['TrCreditCardExpiry']);
	$xes = ereg_replace('[[:alnum:]]', 'x', $year);			
	$expiry = $month.$separator.$xes;
	
	$newValues = array("TrCreditCardNumber"=>$cardNum, "TrCreditCardExpiry"=>$expiry);
	
	$this->fieldSet->loadFieldValuesFromSpecificArray($newValues);
	*/
?>