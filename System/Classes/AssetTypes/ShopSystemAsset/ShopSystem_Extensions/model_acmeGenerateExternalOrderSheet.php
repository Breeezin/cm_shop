<?php 
	ss_paramKey( $this->ATTRIBUTES, "vendor", 2 );
	ss_paramKey( $this->ATTRIBUTES, "Ignore", 0 );

	// check for pending stuff.


	if( !ss_adminCapability( ADMIN_DELETE_ISSUE ) )
	{
		ss_log_message( "Generate packing, checking for outstanding packing items" );
		$SheetID = getRow( "select ors_id, or_tr_id, DATEDIFF(NOW(),ors_date) as age_in_days from shopsystem_order_sheets_items join shopsystem_order_sheets on orsi_ors_id = ors_id join shopsystem_orders on or_id = orsi_or_id where or_cancelled IS NULL and orsi_date_shipped IS NULL AND orsi_no_stock IS NULL and ors_date > NOW() - interval 7 week and ors_ve_id = ".$this->ATTRIBUTES['vendor']." order by ors_date limit 1" );

		if( is_array( $SheetID )
		 && array_key_exists( 'ors_id', $SheetID )
		 && ( $SheetID['ors_id'] > 0 ) )
		{
			if( $SheetID['age_in_days'] > 9 )
			{
				ss_log_message( "Yes, outstanding items on sheet ID {$SheetID['ors_id']} that are {$SheetID['age_in_days']} old, ignore = {$this->ATTRIBUTES['vendor']}" );
				if( $this->ATTRIBUTES['Ignore'] == 0 )
				{
					echo "<html>";
					echo "<h1>There are very old outstanding items on a previous packing list that you need to deal with now.</h1><br />";
					echo "<a href={$_SERVER['REQUEST_URI']}&Ignore=1>Click here to go to outstanding items</a><br /><br />";
					echo "</html>";
					die;
				}
				if( $this->ATTRIBUTES['Ignore'] == 1 )
				{
					ss_log_message( "redirecting to sheet {$SheetID['ors_id']}" );
					location( "index.php?act=shopsystem_order_sheets.ViewPacking&ors_id={$SheetID['ors_id']}&BackURL=".ss_URLEncodedFormat('index.php?act=shopsystem_order_sheets.List')."#{$SheetID['or_tr_id']}" );
				}			
			}
			else
			{
				ss_log_message( "Yes, outstanding items on sheet ID {$SheetID['ors_id']}, ignore = {$this->ATTRIBUTES['vendor']}" );
				if( $this->ATTRIBUTES['Ignore'] == 0 )
				{
					echo "<html>";
					echo "<h1>There are outstanding items on a previous packing list.</h1><br />";
					echo "<a href={$_SERVER['REQUEST_URI']}&Ignore=1>Click here to go to outstanding items</a><br /><br />";
					echo "<a href={$_SERVER['REQUEST_URI']}&Ignore=2>Click here to ignore them for now</a>";
					echo "</html>";
					die;
				}
				if( $this->ATTRIBUTES['Ignore'] == 1 )
				{
					ss_log_message( "redirecting to sheet {$SheetID['ors_id']}" );
					location( "index.php?act=shopsystem_order_sheets.ViewPacking&ors_id={$SheetID['ors_id']}&BackURL=".ss_URLEncodedFormat('index.php?act=shopsystem_order_sheets.List')."#{$SheetID['or_tr_id']}" );
				}
			}
		}
	}
	else
	{
		ss_log_message( "Generate packing, NOT checking for outstanding packing items" );
		ss_log_message( "Admin {$_SESSION['User']['us_id']} level {$_SESSION['User']['us_admin_level']}" );
	}

	// email off stuff to lyonnelconsulting@gmail.com  acme@admin.com  rolfbjork@gmail.com

	$full_email_body = "Stock on hand for vendor {$this->ATTRIBUTES['vendor']}<br />";

	$SOH1 = query( "select pr_name as Name, pro_stock_code as SKU, sum( pro_stock_available) as AvailableToBuy from shopsystem_products join shopsystem_product_extended_options ON pro_pr_id = pr_id where pro_stock_available > 0 and pr_combo IS NULL and pr_ve_id = {$this->ATTRIBUTES['vendor']} and pr_deleted IS NULL group by pro_stock_code order by pro_stock_code" );
	$SOH2 = query( " SELECT oi_name, oi_stock_code as SKU, count(oi_box_number) as SoldButNotSent FROM shopsystem_order_items, shopsystem_orders WHERE oi_or_id = or_id AND oi_eos_id IS NULL AND oi_ve_id = {$this->ATTRIBUTES['vendor']} AND or_cancelled IS NULL AND or_deleted = 0 AND or_shipped IS NULL group by oi_stock_code ORDER BY oi_stock_code " );

	$full_email_body .= "<table><tr><th>Name</th><th>SKU</th><th>Available To Buy</th></tr>";
	while( $row = $SOH1->fetchRow())
		$full_email_body .= "<tr><td>{$row['Name']}</td><td>{$row['SKU']}</td><td>{$row['AvailableToBuy']}</td></tr>";
	$full_email_body .= "</table>";

	$full_email_body .= "<table><tr><th>Name</th><th>SKU</th><th>Sold Not Sent</th></tr>";
	while( $row = $SOH2->fetchRow())
		$full_email_body .= "<tr><td>{$row['oi_name']}</td><td>{$row['SKU']}</td><td>{$row['SoldButNotSent']}</td></tr>";
	$full_email_body .= "</table>";

	$full_recipients = array( "acme@admin.com", "lyonnelconsulting@gmail.com", "rolfbjork@gmail.com" );
	foreach( $full_recipients as $recipient )
		$result = new Request('Email.Send',array(
						'to'	=>	$recipient, 
						'from'	=>	'webserver@acmerockets.com',
						'subject'	=>	"Stock before generating packing list",
						'html'	=>	$full_email_body,
					));

	ss_log_message( "sending email ".$full_email_body );

	startTransaction();
	
	$newOrderID = newPrimaryKey('shopsystem_order_sheets','ors_id');

	// Make a new order sheet
	$Q_Insert = query("
		INSERT INTO shopsystem_order_sheets
			(ors_id, ors_date, ors_ve_id)
		VALUES
			($newOrderID, NOW(), ".$this->ATTRIBUTES['vendor'].")
	");

	ss_log_message( "GENERATE ORDER SHEET, new sheet $newOrderID, for vendor ".$this->ATTRIBUTES['vendor'] );

	// Grab all the products that havene't been ordered yet, order well formed
	// has no message or had message and is older than 12 hours
	// and is older than 2.5 hours
	//	  AND or_recorded < NOW() - INTERVAL 150 minute
	// now removed in favour of explicit checking, or_not_new = 2


/*
	//  addresses are currently NOT needed to checked off.
	$sql = "SELECT * FROM shopsystem_order_items
				join shopsystem_orders on oi_or_id = or_id
				left join countries on or_country = cn_id
			WHERE oi_eos_id IS NULL
			  AND or_card_denied IS NULL AND or_cancelled IS NULL
			  AND or_standby IS NULL AND or_deleted = 0
			  AND oi_ve_id = ".$this->ATTRIBUTES['vendor'];

	if( 4 == $this->ATTRIBUTES['vendor'] )		// rolf wanted this... ugh. Marbella wants 2 packing lists per day, split up available orders.
	{
		// when was the last one ?
		$last_ts = getField( "select UNIX_TIMESTAMP( MAX( orsi_date_shipped ) ) from shopsystem_order_sheets_items where orsi_ors_id = (select max( ors_id ) from shopsystem_order_sheets where ors_ve_id = 4 and ors_id < $newOrderID )" );
		$this_ts = getField( "select UNIX_TIMESTAMP( NOW() )" );
		$average_ts = ($last_ts/2 + $this_ts/2);

		$sql = "SELECT * FROM shopsystem_order_items
					join shopsystem_orders on oi_or_id = or_id
					left join countries on or_country = cn_id
				WHERE oi_eos_id IS NULL
				  AND or_not_new = 2
				  AND or_card_denied IS NULL AND or_cancelled IS NULL
				  AND or_standby IS NULL AND or_deleted = 0
				  AND UNIX_TIMESTAMP( or_recorded ) < $average_ts
				  AND oi_ve_id = 4";

		$sql = "SELECT * FROM shopsystem_order_items
					join shopsystem_orders on oi_or_id = or_id
					left join countries on or_country = cn_id
				WHERE oi_eos_id IS NULL
				  AND or_card_denied IS NULL AND or_cancelled IS NULL
				  AND or_standby IS NULL AND or_deleted = 0
				  AND oi_ve_id = ".$this->ATTRIBUTES['vendor'];
	}
	else
*/
	if( true )		// OCD
	{		// not broken boxes
/*
		$sql = "(SELECT * FROM shopsystem_order_items
					join shopsystem_orders on oi_or_id = or_id
					left join countries on or_country = cn_id
				WHERE oi_eos_id IS NULL
				  AND or_not_new = 2
				  AND or_us_id != 1605
				  AND or_card_denied IS NULL AND or_cancelled IS NULL
				  AND or_standby IS NULL AND or_deleted = 0
				  AND oi_ve_id = ".$this->ATTRIBUTES['vendor'].")
				 UNION
				(SELECT * FROM shopsystem_order_items
					join shopsystem_orders on oi_or_id = or_id
					left join countries on or_country = cn_id
				WHERE oi_eos_id IS NULL
				  AND or_not_new = 2
				  AND or_us_id = 1605
				  AND or_card_denied IS NULL AND or_cancelled IS NULL
				  AND or_standby IS NULL AND or_deleted = 0
				  AND oi_ve_id = ".$this->ATTRIBUTES['vendor']." LIMIT 2)
				  ";
*/
		$sql = "SELECT * FROM shopsystem_order_items
					join shopsystem_orders on oi_or_id = or_id
					left join countries on or_country = cn_id
				WHERE oi_eos_id IS NULL
				  AND or_not_new = 2
				  AND or_card_denied IS NULL AND or_cancelled IS NULL
				  AND or_standby IS NULL AND or_deleted = 0
				  AND oi_ve_id = ".$this->ATTRIBUTES['vendor'];

	}

	ss_log_message( $sql );

	$Q_StockOrders = query($sql);

	$generalDiscount = null;
	
	//$productsOrdered = array();
	
	// Loop thru all the products and add em to the order sheet
	$grandTotal = 0;
	while ($row = $Q_StockOrders->fetchRow())
	{
		// Grab the product that the order is for
		echo "inserting {$row['oi_stock_code']} for order {$row['or_tr_id']}<br />";
		ss_log_message( "Inserting {$row['oi_stock_code']} for order {$row['or_tr_id']}" );

		// need destination
		$prod = getRow("
			SELECT *,
				IF(pro_special_price IS NOT NULL, pro_special_price, pro_price) as sale_price,
				IF(if_cost IS NULL, 0, if_cost) as shipping
			FROM shopsystem_product_extended_options
			  join shopsystem_products on pro_pr_id = pr_id
			  join vendor on ve_id = pr_ve_id
			  left join included_freight on if_shipping_method = ve_shipping_method and if_destination_zone = '{$row['cn_post_zone']}'
			WHERE pro_stock_code = '{$row['oi_stock_code']}'
			    AND pr_deleted IS NULL
				AND pr_ve_id = ".$this->ATTRIBUTES['vendor']."
		");
		
		// If the product doesn't exist any more we can't really order it...
		if ($prod !== null)
		{
			$OrderDetails = unserialize($row['or_basket']);

			// Figure out prices etc and insert order sheet item
			
			if ($generalDiscount === null) {
				// Find out the discount that this shop uses
				$shop = getRow("
					SELECT as_serialized FROM assets
					WHERE as_id = {$prod['pr_as_id']}
				");
				$settings = unserialize($shop['as_serialized']);
//						$generalDiscount = $settings['AST_SHOPSYSTEM_SUPPLIER_DISCOUNT'];
//						if (!strlen($generalDiscount)) {
					$generalDiscount = 0;	
//						}
			}

			$discount = 0;
			$price = 0;
			$shipping = 0;
			$costprice = 0;

			if (strlen($prod['pro_supplier_price'])) {
				$costprice = $prod['pro_supplier_price'];
			}

			if (strlen($prod['sale_price'])) {
				$price = $prod['sale_price'];
			}

			if (strlen($prod['shipping'])) {
				$shipping = $prod['shipping'];
			}

			if (strlen($prod['pro_supplier_disount'])) {
				$discount = ss_decimalFormat($prod['pro_supplier_disount']*$costprice/100);
			} else {
				$discount = ss_decimalFormat($generalDiscount*$costprice/100);
			}

			$costprice -= $discount;

			$total = $price + $shipping;

			ss_log_message( "Total is $total" );
			$grandTotal += $total;
			$USDRate = ss_getExchangeRate( 'USD', $prod['pro_source_currency'] );
			if( !strlen( $USDRate ) )
				$USDRate = 'NULL';
			$sql = "INSERT INTO shopsystem_order_sheets_items
					(orsi_ors_id, orsi_pr_id, orsi_stock_code, orsi_pr_name,
					orsi_box_number, orsi_cost_price, orsi_price, orsi_shipping, orsi_discount, orsi_total, orsi_or_id, orsi_usd_rate )
				VALUES
					($newOrderID, {$prod['pr_id']}, '".escape($row['oi_stock_code'])."', '".escape($prod['pr_name'])."',
					{$row['oi_box_number']}, $costprice, $price, $shipping, $discount, $total, {$row['or_id']}, $USDRate)
			";
			print( $sql );
			$Q_Insert = query($sql);
			print( "<br />");
			ss_log_message( "insert" );

			// Now update the order so that we know that the product has been ordered
			
			// Loop thru all the products in the order.. ugh -_-'
			foreach ($OrderDetails['Basket']['Products'] as $id => $entry)
			{
				// Gotta have an availablility
				if (array_key_exists('Availabilities',$OrderDetails['Basket']['Products'][$id])) 
				{
					// If its the right product
					if ($entry['Product']['pro_stock_code'] == $prod['pro_stock_code'])
					{
						// This is the product we're lookin for

						$box_num = $row['oi_box_number'];
						if( array_key_exists( $box_num, $OrderDetails['Basket']['Products'][$id]['Availabilities'] ) )
							if( $OrderDetails['Basket']['Products'][$id]['Availabilities'][$box_num] == 'buy')
								$OrderDetails['Basket']['Products'][$id]['Availabilities'][$box_num] = 'ordered';
					}
				}
			}
			
			// Serialize back into the order
			$OrderDetailsSerialized = serialize($OrderDetails);
			
			// Update the order
			$Q_Update = query("
				UPDATE shopsystem_orders
				SET or_basket = '".escape($OrderDetailsSerialized)."'
				WHERE or_id = {$row['oi_or_id']}
			");

			ss_log_message( "updated order id  {$row['oi_or_id']}" );

			// Update the stock order with the supplier order sheet id
			$Q_UpdateStockOrders = query("
				UPDATE shopsystem_order_items
				SET oi_eos_id = $newOrderID
				WHERE oi_or_id = {$row['oi_or_id']}
					AND oi_stock_code LIKE '".escape($row['oi_stock_code'])."'
			");

			ss_log_message( "updated shopsystem_order_items with order id {$row['oi_or_id']} for '".escape($row['oi_stock_code'])."'" );
		}
		else
		{
			echo "Product no longer exists<br />";
			ss_log_message( "Product no longer exists" );

			// delete it
			$Q_UpdateStockOrders = query("
				DELETE from shopsystem_order_items
				WHERE oi_eos_id IS NULL
					AND oi_or_id = {$row['oi_or_id']}
					AND oi_stock_code LIKE '".escape($row['oi_stock_code'])."'
			");
		}
	}

	// Add total to new order sheet
	$Q_Insert = query("
		UPDATE shopsystem_order_sheets
		SET ors_total = $grandTotal
		WHERE ors_id = $newOrderID
	");

	commit();

	echo "<a href='index.php?act=shopsystem_order_sheets.ViewPacking&ors_id=$newOrderID&BackURL=".ss_URLEncodedFormat('index.php?act=shopsystem_order_sheets.List')."'>Continue</a>";
//	locationRelative("index.php?act=shopsystem_order_sheets.View&ors_id=$newOrderID&BackURL=".ss_URLEncodedFormat('index.php?act=shopsystem_order_sheets.List'));
?>
