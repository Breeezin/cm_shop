<?php 
	$Q_Subscriptions = query("SELECT * FROM members_subscriptions WHERE ms_as_id = $assetID");	
	$paymentOptions = new Request("WebPay.Options", array('FormName'=>'Subscription'));
	$data = array(
		'Q_Subscriptions' => $Q_Subscriptions, 
		'AssetPath'=>ss_withoutPreceedingSlash($asset->getPath()), 
		'tr_id'=>'',
		'us_id'=>$this->ATTRIBUTES['us_id'],
		'tr_token'=>'',
		'SubscriptionType'=>$this->ATTRIBUTES['SubscriptionType'],
		'Errors'=>$errorMessages,
		'PaymentOptions'=>$paymentOptions->value,
	);
	
	$this->useTemplate('SubscriptionService',$data);
	
?>
