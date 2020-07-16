<?php 
	$this->param('MinTotal', 200);
	$this->param('pr_ca_id', '');
	$this->param('Key', '');
	$this->param('Winner', '');
	$twoweeksago = mktime(0,0,0,month(time()),day(time())-7,year(time()));
	$this->param('DateFrom',date('Y-m-d',$twoweeksago));
	$this->param('DateTo', date('Y-m-d',time()));
	
	$errors = array();
	
	
	
	$this->display->title = "Draw Lottery Winner";
	$Q_Products = query("
		SELECT * FROM shopsystem_products INNER JOIN shopsystem_product_extended_options ON shopsystem_products.pr_id = shopsystem_product_extended_options.pro_pr_id
		WHERE ((pr_deleted IS NULL) OR (pr_deleted = 1))
			AND (pro_stock_available IS NULL OR pro_stock_available > 0)
		ORDER BY pr_name
	");

	$result = new Request("Security.Sudo",array('Action'=>'Start'));
	$allCategoriesResult = new Request("shopsystem_categories.QueryAll",array('as_id'	=>	514));
	$Q_Categories = $allCategoriesResult->value;
	
	
	requireClass('Field');
	$minTotalField = new IntegerField(array(
		'name'			=>	'MinTotal',
		'displayName'	=>	'Minium Order Total',
		'note'			=>	null,			
		'required'		=>	true,			
		'verify'		=>	FALSE,
		'unique'		=>	FALSE,
		'value'			=> 	$this->ATTRIBUTES['MinTotal'],
		'showCalendar'	=> 	TRUE,
		'size'	=>	'4',	'maxLength'	=>	'10',
		'rows'	=>	'6',	'cols'		=>	'40',	
	));
	
	$dateFromField = new DateField(array(
		'name'			=>	'DateFrom',
		'displayName'	=>	'Date From',
		'note'			=>	null,			
		'required'		=>	true,			
		'verify'		=>	FALSE,
		'unique'		=>	FALSE,
		'value'			=> 	$this->ATTRIBUTES['DateFrom'],
		'showCalendar'	=> 	TRUE,
		'size'	=>	'8',	'maxLength'	=>	'10',
		'rows'	=>	'6',	'cols'		=>	'40',	
	));
	
	$dateToField = new DateField(array(
		'name'			=>	'DateTo',
		'displayName'	=>	'Date To',
		'note'			=>	null,			
		'required'		=>	true,		
		'verify'		=>	FALSE,
		'unique'		=>	FALSE,
		'value'			=> 	$this->ATTRIBUTES['DateTo'],		
		'showCalendar'	=> 	TRUE,
		'size'	=>	'8',	'maxLength'	=>	'10',
		'rows'	=>	'6',	'cols'		=>	'40',	
	));
	
	$winner = null;
	if (strlen($this->ATTRIBUTES['Winner'])) {
		$winner = getRow("SELECT * FROM users, shopsystem_orders, transactions WHERE tr_id = or_tr_id AND us_id = or_us_id AND or_id = {$this->ATTRIBUTES['Winner']}");
	}
	
?>