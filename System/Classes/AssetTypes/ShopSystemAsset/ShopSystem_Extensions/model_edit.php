<?

	if (array_key_exists('Do',$this->ATTRIBUTES))
	{
		$details = array();
		$details['TrCreditCardNumber'] = escape( $this->ATTRIBUTES['TrCreditCardNumber'] );
		$details['TrCreditCardType'] = escape( $this->ATTRIBUTES['TrCreditCardType'] );
		$details['TrCreditCardHolder'] = escape( $this->ATTRIBUTES['TrCreditCardHolder'] );
		$details['TrCreditCardCompany'] = escape( $this->ATTRIBUTES['TrCreditCardCompany'] );
		$details['TrCreditCardCVV2'] = escape( $this->ATTRIBUTES['TrCreditCardCVV2'] );
		$details['TrCreditCardExpiry'] = escape( $this->ATTRIBUTES['TrCreditCardExpiry'] );

		$this->param('Total',null);
		
		$code = '';
		if (strlen($this->ATTRIBUTES['Total'])) {
			$curr = getRow( "select * from countries where cn_id = {$this->ATTRIBUTES['CurrencyLink']}" );
			$code = $curr['cn_currency_code'];
			$totalCharged = "'".$curr['cn_currency_symbol'].' '.ss_decimalFormat($this->ATTRIBUTES['Total']).' '.$curr['cn_currency_code']."'";
		} else {
			$totalCharged = "null";
			$this->ATTRIBUTES['Total'] = 0;	
		}

		$this->ATTRIBUTES['Total'] = str_replace(',','.',$this->ATTRIBUTES['Total']);
		
		$paymentDetailsSerialized = serialize($details);

		ss_audit( 'update', 'transactions', $this->ATTRIBUTES['tr_id'], 'Total now '.$totalCharged );
		$Q_Update = query("
			UPDATE transactions
			SET tr_total = {$this->ATTRIBUTES['Total']},
				tr_order_total = {$this->ATTRIBUTES['Total']},
				tr_currency_link = {$this->ATTRIBUTES['CurrencyLink']},
				tr_currency_code = '$code',
				tr_charge_total = {$totalCharged},
				tr_bank = {$this->ATTRIBUTES['Gateway']},
				tr_payment_details_szln = '".escape($paymentDetailsSerialized)."'
			WHERE tr_id = ".safe($this->ATTRIBUTES['tr_id'])."
		");	

		// find the associated order
		$order = getRow("
			SELECT or_id FROM shopsystem_orders
			WHERE or_tr_id = ".safe($this->ATTRIBUTES['tr_id'])."
		");
		
		$result = new Request('ShopSystem.AcmeCalculateOrderProfit',array('or_id'=>$order['or_id']));
		
	} else {
		$trans = getRow("
			SELECT * FROM transactions
			WHERE tr_id = ".safe($this->ATTRIBUTES['tr_id'])."
		");
		$this->ATTRIBUTES['Total'] = $trans['tr_total'];		
		$this->ATTRIBUTES['Currency'] = $trans['tr_currency_link'];		
		$this->ATTRIBUTES['Gateway'] = $trans['tr_bank'];		
		$CCdetails = unserialize($trans['tr_payment_details_szln']);

		$this->ATTRIBUTES['TrCreditCardNumber'] = $CCdetails['TrCreditCardNumber'];
		$this->ATTRIBUTES['TrCreditCardType'] = $CCdetails['TrCreditCardType'];
		$this->ATTRIBUTES['TrCreditCardHolder'] = $CCdetails['TrCreditCardHolder'];
		$this->ATTRIBUTES['TrCreditCardCompany'] = $CCdetails['TrCreditCardCompany'];
		$this->ATTRIBUTES['TrCreditCardCVV2'] = $CCdetails['TrCreditCardCVV2'];
		$this->ATTRIBUTES['TrCreditCardExpiry'] = $CCdetails['TrCreditCardExpiry'];
	}

?>
