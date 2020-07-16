<?php
	requireOnceClass('FieldSet');
	

	// This sends an email to the customer notifying them of the price and
	// providing the link to the payment form.

	$this->param('bo_id');
	$this->param('as_id');

	$Booking = getRow("
		SELECT * FROM booking_form_bookings, transactions, countries
		WHERE bo_id = ".safe($this->ATTRIBUTES['bo_id'])."
			AND bo_as_id = ".safe($this->ATTRIBUTES['as_id'])."
			AND bo_tr_id = tr_id
			AND tr_currency_link = cn_id
	");
	$Transaction = $Booking;

	$res = new Request('Asset.PathFromID',array('as_id'	=>	$Booking['bo_as_id'],));
	$assetPath = $res->value;
	
	$data = array(
		'Link'	=>	ss_withTrailingSlash($GLOBALS['cfg']['secure_server'])."index.php?act=WebPay.ByCreditCard&tr_id={$Transaction['tr_id']}&tr_token={$Transaction['tr_token']}&BackURL=".ss_URLEncodedFormat(ss_withTrailingSlash($GLOBALS['cfg']['plaintext_server']).ss_withoutPreceedingSlash($assetPath)."?Service=ThankYou&bo_id={$Booking['bo_id']}&tr_id={$Transaction['tr_id']}&tr_token={$Transaction['tr_token']}"),
		'Amount'	=>	ss_decimalFormat($Transaction['tr_total']).' '.$Transaction['cn_currency_code'],
		'Reference'	=>	$Transaction['tr_id'],
		'Details'	=>	$Booking['bo_details'],
		'website_name'	=>	$GLOBALS['cfg']['website_name'],
	);
	
	$defaultEmail = $this->processTemplate('NotificationEmail',$data);
	$this->param('Email',$defaultEmail);

	/*print($this->ATTRIBUTES['Email']);*/
	
	
	/*$mailer = new htmlMimeMail();		
	$mailer->setFrom($asset->cereal[$this->fieldPrefix.'ADMINEMAIL']);
	$mailer->setSubject("Order Received - {$GLOBALS['cfg']['website_name']}");		
	$textMessage = $this->processTemplate('Email_OrderReceived', $emaildata);
	$mailer->setHTML($textMessage);				
	$mailer->send(array($asset->cereal[$this->fieldPrefix.'ADMINEMAIL']));*/
		
	$this->fieldSet = new FieldSet(array(
		'tablePrimaryKey'	=>	'tr_id',
		'tableName'	=>	'transactions',
		'primaryKey'	=>	-1,
	));	

	$this->fieldSet->addField(new HtmlMemoField2(array(
		'name'			=>	'Email',
		'displayName'	=>	'Email',
		'required'		=>	true,
		'verify'		=>	false,
		'unique'		=>	false,
		'default'		=>	$defaultEmail,
		'size'	=>	'50',	'maxLength'	=>	'255',
		'rows'	=>	'6',	'cols'		=>	'40',
		'width'	=>	'document.body.clientWidth-35',
	)));

	$this->fieldSet->loadFieldValues($this->ATTRIBUTES,$this->fields);
		
?>