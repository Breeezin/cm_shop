<?
	$Q_Code = query("	
		SELECT * FROM discounts
		WHERE di_code = '".safe($discountCode)."'
	");		
	if ($Q_Code->numRows() and ss_optionExists('Shop Discount Codes')) {
		// Save to the shop
		$_SESSION['Shop']['DiscountCode'] = $Q_Code->fetchRow();
	} else {
		// Give up
		$_SESSION['Shop']['DiscountCode'] = null;
	}
	
	$result = new Request('Asset.Display',array(
		'NoHusk'	=>	true,
		'as_id'	=>	$this->asset->getID(),
		'Service'	=>	'UpdateBasket',
		'Mode'		=>	'FixTax',
		'AsService'	=>	true,
	));	
?>
