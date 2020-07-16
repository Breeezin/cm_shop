<?php 

	if( ss_adminCapability( ADMIN_ORDER_STATUS ) )
	{
		ss_audit( 'update', 'Orders', $this->ATTRIBUTES['or_id'], 'marking Paid' );

		require_once( "model_autoInvoice.php" );

		$this->param('or_id');	
		$this->param('BackURL');
		
		$Q_Order = getRow("SELECT * FROM shopsystem_orders WHERE or_id = {$this->ATTRIBUTES['or_id']}");
		
		$Q_Custom = getRow("SELECT * FROM user_groups WHERE ug_name LIKE 'Customers'");
					
		// check the customer has the 'Customers' user group 
		$Q_UserGroups = query("
				SELECT * FROM user_user_groups 
				WHERE uug_us_id = {$Q_Order['or_us_id']} AND uug_ug_id = {$Q_Custom['ug_id']}
		");
		//if the user doenst have the group, then add one
		if (!$Q_UserGroups->numRows()) {
			$Q_UpdateGroup = query("
				INSERT INTO user_user_groups 
					(uug_us_id, uug_ug_id) 
				VALUES 
					({$Q_Order['or_us_id']},  {$Q_Custom['ug_id']})
			");
		}
		
		// Check if marked paid already
		$alreadyPaid = false;
		if (array_key_exists('or_paid',$Q_Order) and $Q_Order['or_paid'] !== null) {
			$alreadyPaid = true;
		}
		if (array_key_exists('or_paid_not_shipped',$Q_Order) and $Q_Order['or_paid_not_shipped'] !== null) {
			$alreadyPaid = true;
		}
		
		$extraSQL = '';
		if (ss_optionExists('Shop Advanced Ordering')) {
			$extraSQL = ', or_paid_not_shipped = NULL, or_card_denied = NULL, or_cancelled = NULL, or_standby = NULL, or_charge_list = NULL';
			
		}
		
		// now update the order record as paid.
		$Q_UpdateOrder = query("
				UPDATE shopsystem_orders 
				SET 
					or_paid = Now()		
					$extraSQL	
					WHERE or_id = {$this->ATTRIBUTES['or_id']}
		");
		
		
		$Q_Transaction = getRow("SELECT * FROM transactions WHERE tr_id = {$Q_Order['or_tr_id']} AND tr_completed = 1");	
		
		
		if ($Q_Transaction['tr_payment_method'] == 'WebPay_CreditCard_Manual' or $Q_Transaction['tr_payment_method'] == 'Cheque' or $Q_Transaction['tr_payment_method'] == 'Direct') {				
			if (!$alreadyPaid) {
				if (is_array($Q_Order['or_details'])) {
					$basket = $Q_Order['or_details'];
				} else {			
					$basket = unserialize($Q_Order['or_details']);
				}
				//ss_DumpVarDie($basket['OrderProducts']['Products']);
				// add order products into the db.
				foreach($basket['OrderProducts'] as $aProduct) {
					$name = escape("{$aProduct['Product']['pr_name']} ({$aProduct['Product']['Options']})");
					$price = $aProduct['Qty'] * $aProduct['Product']['Price'];
					//$price = escape($this->formatPrice('display', $price));
					
					// Update the product's stock availability since this product
					// option has been sold.
					/*$ProductOption = getRow("
						SELECT * FROM shopsystem_product_extended_options
						WHERE pro_stock_code LIKE '{$aProduct['Product']['pro_stock_code']}'
					");
					if ($ProductOption['pro_stock_available'] !== null) {
						// If the product option is using the stock level management..
						$Q_UpdateProductOption = query("
							UPDATE shopsystem_product_extended_options
							SET pro_stock_available = ".($ProductOption['pro_stock_available']-$aProduct['Qty'])."
							WHERE pro_id = {$ProductOption['pro_id']}
						");
					}*/
					
					
					$Q_Insert = query("
							INSERT INTO shopsystem_order_products 
								(orpr_or_id, orpr_pr_id, orpr_pr_name, orpr_price, orpr_qty, orpr_timestamp, orpr_site_folder) 
							VALUES
								({$Q_Order['or_id']}, {$aProduct['Product']['pr_id']}, '$name', '$price', {$aProduct['Qty']}, Now(), '{$Q_Order['or_site_folder']}')		
					");
				}
			}		
		}
			
		$Q_UpdateOrder = query("
				UPDATE shopsystem_orders 
				SET or_paid = Now() 
				$extraSQL	
				WHERE or_id = {$this->ATTRIBUTES['or_id']}
		");
		
		if (ss_optionExists('Shop Acme Rockets')) {
			require("inc_addPoints.php");	
			$result = new Request('ShopSystem.AcmeCalculateOrderProfit',array('or_id'=>$this->ATTRIBUTES['or_id']));
		}
		
		if (ss_optionExists('Shop Keep Credit Card Details') === false) {
			$res = new Request("WebPay.MarkPaid",array(
					'tr_id'	=>	$this->ATTRIBUTES['tr_id'],
			));
		}

		if (ss_optionExists('Shop Auto Invoice'))
			autoInvoice( $this->ATTRIBUTES['or_id'] );
	}

	locationRelative($this->ATTRIBUTES['BackURL']);
		
?>
