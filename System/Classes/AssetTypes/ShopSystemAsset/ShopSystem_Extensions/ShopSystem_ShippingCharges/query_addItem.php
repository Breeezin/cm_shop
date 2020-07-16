<?
	$this->param('sos_id');
	$this->param('StockCode');
	$this->param('BackURL');
	$this->param('Qty');

	$prod = getRow("
		SELECT * FROM shopsystem_products, shopsystem_product_extended_options
		WHERE pr_id = pro_pr_id
			AND pro_stock_code LIKE '".escape($this->ATTRIBUTES['StockCode'])."'
	");	
	

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

	$qty = $this->ATTRIBUTES['Qty'];
	
	// figure out the price and discount
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
	$total = ss_decimalFormat(($price-$discount)*$qty);

	// insert it into the db
	$Q_Insert = query("
		INSERT INTO shopsystem_supplier_order_sheets_items
			(soit_sos_id, soit_stock_code, soit_pr_name,
			soit_qty, soit_price, soit_discount, soit_total)
		VALUES
			({$this->ATTRIBUTES['sos_id']}, '".escape($prod['pro_stock_code'])."', '".escape($prod['pr_name'])."',
			$qty , $price, $discount, $total)
	");
	
	// fix the total
	$this->fixTotal($this->ATTRIBUTES['sos_id']);
	$newItem = 10000;
	
	locationRelative("index.php?act=shopsystem_supplier_order_sheets.Edit&sos_id={$this->ATTRIBUTES['sos_id']}&BackURL=".ss_URLEncodedFormat($this->ATTRIBUTES['BackURL']));
	
?>