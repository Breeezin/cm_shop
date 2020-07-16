<?php 
	if (array_key_exists("Do_Service", $this->ATTRIBUTES)) {
		if (!strlen($this->ATTRIBUTES['SubscriptionType'])) {
			$errorMessages = array(array("Please choose a subscription type."));
		} else {
			//insert order now before transaction form is completed						
			$user = getRow("SELECT * FROM users WHERE us_id = {$this->ATTRIBUTES['us_id']}");
		
			$prepareTransaction = new Request("WebPay.PreparePayment", 
				array(	'tr_currency_link' => $clientCountry, 
						'tr_client_name' => $user['us_first_name'].' '.$user['us_last_name'])
			);
		
			$trID = $prepareTransaction->value['tr_id'];
			$token = $prepareTransaction->value['tr_token'];
			
	
			$Q_InsertOrder = query("
					INSERT INTO members_orders 
					(mo_us_id, mo_tr_id, mo_as_id, mo_sub_id, mo_token)
					VALUES
					({$this->ATTRIBUTES['us_id']},$trID,{$assetID},{$this->ATTRIBUTES['SubscriptionType']}, '$token')
			");
			
			$Q_SubDetails = getRow("SELECT * FROM 
					members_subscriptions 
					WHERE ms_as_id = $assetID
					AND ms_id = {$this->ATTRIBUTES['SubscriptionType']}
			");
			
			$price = $Q_SubDetails['ms_default_price'];
		
			
			$Q_Price = getRow("SELECT * FROM 
					members_subscription_prices 
					WHERE msp_cn_id = {$clientCountry}
			");
			if (strlen($Q_Price['msp_id'])) {
				$price = $Q_Price['msp_price'];
			} else {
				$clientCountry = $Q_SubDetails['ms_default_cn_id'];
			}
			
			$updateTransaction  = new Request("WebPay.PreparePayment", array('tr_id' => $trID, 'tr_total' => $price, 'tr_currency_link' =>$clientCountry));			
			
			global $cfg;
			
			$normalSite = $GLOBALS['cfg']['plaintext_server'];
			$normalSite = ss_withTrailingSlash($normalSite);
			$backURL = ss_URLEncodedFormat("{$normalSite}$assetPath/Service/Completed/Token/$token/tr_id/$trID");
			$this->param("PaymentOption");
			$secureSite = $GLOBALS['cfg']['secure_server'];
			$secureSite = ss_withTrailingSlash($secureSite);
			location($secureSite."index.php?act=WebPay.{$this->ATTRIBUTES['PaymentOption']}&tr_id=$trID&tr_token=$token&BackURL={$backURL}");
		}
	}
?>