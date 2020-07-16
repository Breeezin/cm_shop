<?php
	if (array_key_exists('DoAction',$this->ATTRIBUTES)) {

		// Validate the data for each field
		// Set up the error array
		//$ok = true;
		
		
		if(!array_key_exists("Paid", $this->ATTRIBUTES)) {				
					
			if (array_key_exists($this->tablePrimaryKey,$this->ATTRIBUTES)) {
				$this->primaryKey = $this->ATTRIBUTES[$this->tablePrimaryKey];
			}
			
			// Validate each field and record any errors reported
			$errors = array_merge($errors,$this->validate());
			$errors = array_merge($errors,$processorType->fieldSet->validate());
			//ss_DumpVar($processorType->fieldSet->fields,'att',true);			
			//ss_DumpVar($this->ATTRIBUTES,'att',true);			
		}
		
		
		// Update if no errors validating data
		if (count($errors) == 0) {
			$display = false;
			$transactionDone = $processorType->checkTransactionDone($this);
			// transcation result need to store all not only time
			$previousResult = $this->payment['tr_result'];
			$previousResult .= strlen($previousResult)?'<BR>':'';
			$previousInfo = $this->payment['tr_sent_information'];
			$previousInfo .= strlen($previousInfo)?'<BR>':'';
			
			$transactionResults = escape($previousResult.$processorType->storeTransactionResult($this));
			$transactionInfo = escape($previousInfo.$processorType->getTransactionSentInfo($this));
			$paymentDetailsSerialized = '';
								
			if(!array_key_exists("Paid", $this->ATTRIBUTES)) {		
				if ($creditConfig['Processor'] == 'WebPay_CreditCard_Manual' or ($transactionDone != 2 and ss_optionExists('Transaction Fail Continue'))) {
					$paymentDetailsSerialized = serialize($processorType->fieldSet->getFieldValuesArray());			
				} 
				
			}
			
			// Update the fields
			$result = query("
				UPDATE {$this->tableName}
				SET 
					tr_payment_details_szln = '".escape($paymentDetailsSerialized)."', 
					tr_completed = 1, 
					tr_status_link = $transactionDone,
					tr_timestamp = Now(), 
					tr_charge_total = '{$chargedIn}', 
					tr_nzd_total_charged = $nzdChargedIn,
					tr_result = '$transactionResults',
					tr_sent_information = '$transactionInfo',
					tr_payment_method = '{$creditConfig['Processor']}'		
				WHERE {$this->tablePrimaryKey} = {$this->primaryKey}
			");
		
			if( array_key_exists( 'Blacklist', $_SESSION ) && $_SESSION['Blacklist'] )
			{
				ss_log_message( "Blacklisted idiot, dying here" );
				die;
			}

			// shifted confirm email from model_complete to here.

			$fieldsArray = array();	 // user fields			
			$fieldNamesArray = array();	 			
			$Q_UserAsset = getRow("SELECT * FROM assets WHERE as_type LIKE 'users'");
			ss_paramKey($Q_UserAsset,'as_serialized',''); 
			
			if (strlen($Q_UserAsset['as_id']) AND strlen($Q_UserAsset['as_serialized'])) {
				$cereal = unserialize($Q_UserAsset['as_serialized']);			
				ss_paramKey($cereal,'AST_USER_FIELDS','');
				if (strlen($cereal['AST_USER_FIELDS'])) {
					$fieldsArray = unserialize($cereal['AST_USER_FIELDS']);
				} else {
					$fieldsArray = array();	
				}
			} else {
				$fieldsArray = array();	
			}		
			
			foreach($fieldsArray as $fieldDef) {	
				ss_paramKey($fieldDef, 'uuid','');
				ss_paramKey($fieldDef, 'name','');
				$fieldNamesArray[$fieldDef['uuid']] = $fieldDef['name'];			
			}

			$shop_asset = getRow('select * from assets where as_id = 514');
			$shop_assetCereal = unserialize( $shop_asset['as_serialized'] );
			$emailText = $shop_assetCereal['AST_SHOPSYSTEM_CLIENT_CREDITCARDEMAIL'];

			$order_details = getRow( "select * from transactions join shopsystem_orders on or_tr_id = tr_id where {$this->tablePrimaryKey} = {$this->primaryKey}" );

			$allTags = array();	
			// get details from purchaser and shipping
			// put into the tag table with value
			$shippingDetails = unserialize($order_details['or_shipping_details']);
			if (!array_key_exists('first_name',$shippingDetails['ShippingDetails']))
			{
				if (array_key_exists('Name',$shippingDetails['ShippingDetails']))
				{
					$aValue = $shippingDetails['ShippingDetails']['Name'];
					$shippingDetails['ShippingDetails']['first_name'] = ListFirst($aValue,' ');
					$shippingDetails['ShippingDetails']['last_name'] = ListLast($aValue,' ');
				}
			}

			foreach($shippingDetails['ShippingDetails'] as $key => $aValue)
			{
				if (array_key_exists($key, $fieldNamesArray)) 
					$allTags["S.".$fieldNamesArray[$key]] = $aValue; 				
				else 
					$allTags["S.".$key] = $aValue; 				
			}		

			if (!array_key_exists('first_name',$shippingDetails['PurchaserDetails'])) {
				$aValue = $shippingDetails['PurchaserDetails']['Name'];
				$shippingDetails['PurchaserDetails']['first_name'] = ListFirst($aValue,' ');
				$shippingDetails['PurchaserDetails']['last_name'] = ListLast($aValue,' ');
			}			

			foreach($shippingDetails['PurchaserDetails'] as $key => $aValue)
			{
				if (array_key_exists($key, $fieldNamesArray)) 
					$allTags["P.".$fieldNamesArray[$key]] = $aValue; 				
				else 
					$allTags["P.".$key] = $aValue; 					
			}

			$gateway = getRow( 'select * from payment_gateways where pg_id = '.(int) $order_details['tr_bank'] );

			$ordetails = unserialize($order_details['or_details']);
			$allTags['OrderDetails'] = $ordetails['BasketHTML'];
			$allTags['TotalCharge'] = $order_details['tr_charge_total'];
			$allTags['Total'] = $order_details['tr_total'];
			$allTags['ChargingName'] = $gateway['pg_charging_name'];
			$allTags['OrderNumber'] = $order_details['tr_id'];

		//	ss_log_message_r(  'Log:'.__FILE__.':'.__LINE__, $allTags );
			foreach($allTags as $tag => $value)
				$emailText = stri_replace("[{$tag}]",$value,$emailText);

		//	ss_log_message( $emailText );

			// html manipulation from Email.Send
			$styleSheet = 'System/Classes/AssetTypes/ShopSystemAsset/ShopSystem_OrdersAdministration/Templates/sty_invoice.css';
			$ExtraStyleSheets = '<link rel="stylesheet" href="'.$styleSheet.'" type="text/css">';
			$data = array(
				'ExtraStyleSheets'	=>	$ExtraStyleSheets,
				'Content'	=>	$emailText,
			);

//			ss_log_message_r(  'Log:'.__FILE__.':'.__LINE__, $data );

			//$htmlMessage = processTemplate("System/Classes/Tools/Email/Templates/Email.html",$data);
			$htmlMessage = processTemplate("Custom/ContentStore/Templates/acmerockets/Email/Email.html",$data);

//			ss_log_message( $htmlMessage );
			$subject = "Order Receipt";

			include_once( "System/Libraries/Rmail/Rmail.php" );
			$mailer = new Rmail();
			$mailer->setFrom('admin@acmerockets.com');		// irrelevant, overwritten
			$mailer->setSubject($subject);
			$mailer->setHTML($htmlMessage);				
			$mailer->setSMTPParams("localhost", 587);		// out via submit to gmail
		//	ss_log_message_r(  'Log:'.__FILE__.':'.__LINE__, $mailer );
			$result = $mailer->send(array($order_details['or_purchaser_email']), 'smtp');

			ss_log_message( 'manual email order receipt send result = '.$result );

			//location($this->ATTRIBUTES['BackURL'], true);	
			//ss_DumpVarDie($this);
		}
		
	}
?>	
