<?php 
	startTransaction();
	
	$newOrderID = newPrimaryKey('shopsystem_supplier_order_sheets','sos_id');
	
	// Make a new order sheet
	$Q_Insert = query("
		INSERT INTO shopsystem_supplier_order_sheets
			(sos_id, sos_date)
		VALUES
			($newOrderID, NOW())
	");
	
	// Grab all the products that havene't been ordered yet
	$Q_StockOrders = query("
		SELECT * FROM shopsystem_stock_orders
		WHERE sto_sos_id IS NULL
	");

	$generalDiscount = null;
	
	$productsOrdered = array();
	
	// Loop thru all the products and add em to the order sheet
	$grandTotal = 0;
	while ($row = $Q_StockOrders->fetchRow()) {
		// Grab the product that the order is for
		$prod = getRow("
			SELECT * FROM shopsystem_product_extended_options, shopsystem_products
			WHERE pro_stock_code = '".$row['sto_stock_code']."'
		 		AND pro_pr_id = pr_id
		");	
		
		// If the product doesn't exist any more we can't really order it...
		if ($prod !== null) {

			$Order = getRow("
				SELECT or_basket FROM shopsystem_orders
				WHERE or_id = {$row['sto_or_id']}
			");

			if ($Order !== null) {
							
				
				if (array_key_exists($row['sto_stock_code'],$productsOrdered)) {
					// Simply update the quantities for the product in the order sheet item
					// this way the same product is kept together in the order sheet instead
					// of being listed twice
					
					$productsOrdered[$row['sto_stock_code']]['qty'] += $row['sto_qty'];
					
					$grandTotal += ($row['sto_qty']*$productsOrdered[$row['sto_stock_code']]['price']);
					
					$Q_Update = query("
						UPDATE shopsystem_supplier_order_sheets_items
						SET
							soit_qty = {$productsOrdered[$row['sto_stock_code']]['qty']},
							soit_total = ".($productsOrdered[$row['sto_stock_code']]['qty']*$productsOrdered[$row['sto_stock_code']]['price'])."
						WHERE soit_sos_id = $newOrderID
							AND soit_stock_code = '".escape($row['sto_stock_code'])."'
					");
					
					
				} else {
					// Figure out prices etc and insert order sheet item
					
					if ($generalDiscount === null) {
						// Find out the discount that this shop uses
						$shop = getRow("
							SELECT as_serialized FROM assets
							WHERE as_id = {$prod['pr_as_id']}
						");
						$settings = unserialize($shop['as_serialized']);
						$generalDiscount = $settings['AST_SHOPSYSTEM_SUPPLIER_DISCOUNT'];
						if (!strlen($generalDiscount)) {
							$generalDiscount = 0;	
						}
					}			
					
					$discount = 0;
					$price = 0;
					if (strlen($prod['pro_supplier_price'])) {
						$price = $prod['pro_supplier_price'];
					}
					if (strlen($prod['pro_supplier_disount'])) {
						$discount = ss_decimalFormat($prod['pro_supplier_disount']*$price/100);
					} else {
						$discount = ss_decimalFormat($generalDiscount*$price/100);
					}
					$total = ($price-$discount)*$row['sto_qty'];
					
					$productsOrdered[$row['sto_stock_code']] = array(
						'qty'	=>	$row['sto_qty'],
						'price'	=>	$price-$discount,
					);
					
					$grandTotal += $total;
					$Q_Insert = query("
						INSERT INTO shopsystem_supplier_order_sheets_items
							(soit_sos_id, soit_stock_code, soit_pr_name,
							soit_qty, soit_price, soit_discount, soit_total)
						VALUES
							($newOrderID, '".escape($row['sto_stock_code'])."', '".escape($prod['pr_name'])."',
							{$row['sto_qty']}, $price, $discount, $total)
					");
				}
				
				// Now update the order so that we know that the product has been ordered
				
				
				/*$Order = getRow("
					SELECT or_basket FROM shopsystem_orders
					WHERE or_id = {$row['sto_or_id']}
				");*/
				$OrderDetails = unserialize($Order['or_basket']);
				
				$qtyToBuy = $row['sto_qty'];
				// Loop thru all the products in the order.. ugh -_-'
				foreach ($OrderDetails['Basket']['Products'] as $id => $entry) {
					// Gotta have an availablility
					if (array_key_exists('Availabilities',$OrderDetails['Basket']['Products'][$id])) {
						// If its the right product
						if ($entry['Product']['pro_stock_code'] == $prod['pro_stock_code']) {
							// This is the product we're lookin for
							for ($qty=0; $qty < $entry['Qty']; $qty++) {
								// Now loop thru all the quantities and update to them being ordered
								if ($qtyToBuy > 0 and $OrderDetails['Basket']['Products'][$id]['Availabilities'][$qty] == 'buy') {
									$OrderDetails['Basket']['Products'][$id]['Availabilities'][$qty] = 'ordered';
									$qtyToBuy--;
								}
							}
						}
					}
				}
				
				// Serialize back into the order
				$OrderDetailsSerialized = serialize($OrderDetails);
				
				// Update the order
				$Q_Update = query("
					UPDATE shopsystem_orders
					SET or_basket = '".escape($OrderDetailsSerialized)."'
					WHERE or_id = {$row['sto_or_id']}
				");
	
				
				// Update the stock order with the supplier order sheet id
				$Q_UpdateStockOrders = query("
					UPDATE shopsystem_stock_orders
					SET sto_sos_id = $newOrderID
					WHERE sto_or_id = {$row['sto_or_id']}
						AND sto_stock_code LIKE '".escape($row['sto_stock_code'])."'
				");
			}
			
		}
	}
	
	// Add total to new order sheet
	$Q_Insert = query("
		UPDATE shopsystem_supplier_order_sheets
		SET sos_total = $grandTotal
		WHERE sos_id = $newOrderID
	");
	
	commit();

	locationRelative("index.php?act=shopsystem_supplier_order_sheets.View&sos_id=$newOrderID&BackURL=".ss_URLEncodedFormat('index.php?act=shopsystem_supplier_order_sheets.List'));
?>