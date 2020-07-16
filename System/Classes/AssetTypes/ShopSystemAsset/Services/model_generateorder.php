<?

	$this->param('ShippingDetails',array());
	$this->param('PurchaserDetails',array());
	$this->param('ShippingValues',array());
	// us_name = array('first_name'=>'','last_name'=>'');
	// us_email
	$this->param('us_id',-1);
	
	
	$shippingDetailsSerialized = escape(serialize(array('ShippingDetails' => $this->ATTRIBUTES['ShippingDetails'], 'PurchaserDetails' => $this->ATTRIBUTES['PurchaserDetails'])));
	$shippingValuesSerialized = escape(serialize($this->ATTRIBUTES['ShippingValues']));
			
	$assetID = $this->asset->getID();
	$sessionBasket = escape(serialize($_SESSION['Shop']));
	//$sessionBasket = '';
	$orTotal = $this->formatPrice('display',$_SESSION['Shop']['Basket']['Total']);
	//$orTotal = "\${$_SESSION['Shop']['Basket']['Total']} NZD";
	$email = '';
	if(array_key_exists('us_email', $this->ATTRIBUTES)) {
		$email = escape($this->ATTRIBUTES['us_email']);
	}
	$firstName = '';
	$lastName = '';
	if(array_key_exists('us_name', $this->ATTRIBUTES)) {
		$firstName = escape($this->ATTRIBUTES['us_name']['first_name']);
		$lastName = escape($this->ATTRIBUTES['us_name']['last_name']);
	}
	$result = new Request('Asset.Display',array(
		'Service'	=>	'Basket',
		'as_id'	=>	$this->asset->getID(),
		'Style'		=>	'NoInputs',
		'NoHusk'	=>	true,
	));
			
	$giftmsg = '';
	$this->param('GiftMessage', '');
	if (ss_OptionExists('Gift Message')) {
		$giftmsg = $this->ATTRIBUTES['GiftMessage'];
	}
			
	$orderDetails = array('OrderProducts' =>$_SESSION['Shop']['Basket']['Products'], "BasketHTML" =>str_replace(chr(10),'',$result->display), 'GiftMessage' => $giftmsg);
	$basket = escape(serialize($orderDetails));
			
	// Redlane wanted this in a separate field. Might as well do it for all shops
	$insertDiscountCodeField = '';
	$insertDiscountCodeValue = '';
	if (ss_optionExists('Shop Discount Codes')) {
		if ($_SESSION['Shop']['DiscountCode'] !== null) {
			$insertDiscountCodeField = ', or_discount_code';
			$insertDiscountCodeValue = ", '".escape($_SESSION['Shop']['DiscountCode'])."'";
		}
	}

	$insertShippingValuesField = '';
	$insertShippingValuesValue = '';
	if (ss_optionExists('Shop Edit Orders')) {
		$insertShippingValuesField = ',or_shipping_values';
		$insertShippingValuesValue = ",'".$shippingValuesSerialized."'";
	}
			
	$displayCurrency = $this->getDisplayCurrency();
	$currency = getRow("SELECT * FROM countries WHERE cn_currency_code LIKE '".$displayCurrency['CurrencyCode']."'");
	$prepareTransaction = new Request("WebPay.PreparePayment",array(
		'tr_currency_link' => $currency['cn_id'], 
		'tr_client_name' => '',)
	);
				
	$this->ATTRIBUTES['tr_id'] = $prepareTransaction->value['tr_id'];
	$this->ATTRIBUTES['tr_token'] = $prepareTransaction->value['tr_token'];
			
	$Q_InsertOrder = query("
		INSERT INTO shopsystem_orders 
			(or_us_id,or_tr_id, or_as_id, or_shipping_details, 
				or_total, or_purchaser_email, or_recorded, 
				or_purchaser_firstname, or_purchaser_lastname, or_basket,
				or_details, or_site_folder $insertDiscountCodeField 
				$insertShippingValuesField
			)
				VALUES
			(".safe($this->ATTRIBUTES['us_id']).",{$this->ATTRIBUTES['tr_id']},{$assetID}, '$shippingDetailsSerialized', 
				'$orTotal', '$email', Now(),
				'$firstName', '$lastName', '$sessionBasket',
				'$basket', '{$GLOBALS['cfg']['folder_name']}' $insertDiscountCodeValue
				$insertShippingValuesValue
			)
	");

	$enterCurrency = $this->getEnterCurrency();
	$displayCurrency = $this->getDisplayCurrency();
	$totalPrice = $_SESSION['Shop']['Basket']['Total'];
			
	if ($enterCurrency != $displayCurrency) {
		$totalPrice = $totalPrice * ss_getExchangeRate($enterCurrency['CurrencyCode'],$displayCurrency['CurrencyCode']);
		$totalPrice = sprintf("%01.2f",$totalPrice);
	}
	$currency = getRow("SELECT * FROM countries WHERE cn_currency_code LIKE '".$displayCurrency['CurrencyCode']."'");
			
	$updateTransaction  = new Request("WebPay.PreparePayment", array(
		'tr_id' => $this->ATTRIBUTES['tr_id'], 
		'tr_total' => $totalPrice, 
		'tr_currency_link' =>$currency['cn_id'], 
		'tr_client_name' =>$firstName.' '.$lastName
	));	

	print($this->ATTRIBUTES['tr_id']);
			
?>