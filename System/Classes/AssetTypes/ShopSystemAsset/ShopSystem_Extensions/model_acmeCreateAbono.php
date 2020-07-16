<?
	/* here we load up the order... then look for our invoice.. and then insert the abono code for 
	   products using that invoice number */
	
	

	$this->param('or_id');
	$this->param('inv_id');
	$this->param('BackURL');

	startTransaction();
	
	
	$reshipOrder = getRow("SELECT * FROM shopsystem_orders WHERE or_id = ".safe($this->atts['or_id']));
	$reshipOrderDetails = unserialize($reshipOrder['or_basket']);			

	$counterInvoices = array();
	$newInvoice = $this->atts['inv_id'];
	
	foreach ($reshipOrderDetails['Basket']['Products'] as $id => $entry) {
		ss_paramKey($reshipOrderDetails['Basket']['Products'][$id],'InvoiceNumbers',array());
		ss_paramKey($reshipOrderDetails['Basket']['Products'][$id],'Abono',array());
				
		for ($qty=0; $qty < $entry['Qty']; $qty++) {

			if (array_key_exists($qty,$reshipOrderDetails['Basket']['Products'][$id]['InvoiceNumbers'])) {
				if ($newInvoice == $reshipOrderDetails['Basket']['Products'][$id]['InvoiceNumbers'][$qty]) {
					// This invoice matches the invoice number supplied.. so we make an abono for it..
					if (!array_key_exists($newInvoice,$counterInvoices)) {
						// make a new "Abono"
						$invoiceInfo = getRow("
							SELECT in_total_value, in_date FROM shopsystem_invoices
							WHERE inv_id = $newInvoice
						");
						$invoiceTotal = $invoiceInfo['in_total_value'];
						$invoiceDate = $invoiceInfo['in_date'];
						$newCounterInvoiceID = newPrimaryKey('ShopSystem_CounterInvoices','CoInID');
						$Q_CreateCounterInvoice = query("
							INSERT INTO ShopSystem_CounterInvoices
								(CoInID, CoInDate, CoInOriginalInvoiceLink, CoInTotal)
							VALUES
								($newCounterInvoiceID, '$invoiceDate', $newInvoice , $invoiceTotal)
						");
						$counterInvoices[$newInvoice] = $newCounterInvoiceID;
					}
					// apply the counter invoice for the invoice in use
					$reshipOrderDetails['Basket']['Products'][$id]['Abono'][$qty] = $counterInvoices[$newInvoice];
				}				
			}
			
		}
	}
	// Serialize back into the order
	$reshipOrderDetailsSerialized = serialize($reshipOrderDetails);
	
	// Update the order
	$Q_Update = query("
		UPDATE shopsystem_orders
		SET or_basket = '".escape($reshipOrderDetailsSerialized)."'
		WHERE or_id = ".safe($this->atts['or_id'])."
	");
			
	commit();
	
	location($this->atts['BackURL']);
	
?>