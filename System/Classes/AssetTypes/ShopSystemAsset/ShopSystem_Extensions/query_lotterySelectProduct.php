<?
	
	$result = new Request("Security.Sudo",array('Action'=>'Start'));
	$allCategoriesResult = new Request("shopsystem_categories.QueryAll",array('as_id'	=>	514));
	$Q_Categories = $allCategoriesResult->value;

	$nextWeek = getRow("
		SELECT * FROM lottery_winners
		WHERE lotw_draw_date IS NULL
			AND lotw_upcoming IS NULL
	");
	
	if ($nextWeek === null) {
		$key = '';
		$category = '';
	} else {
		$optionLink = getRow("
			SELECT pro_id FROM shopsystem_product_extended_options
			WHERE pro_pr_id = {$nextWeek['lotw_pr_id']}
		");
		$key = $nextWeek['lotw_pr_id'].'_'.$optionLink['pro_id'];
		$productCategory = getRow("
			SELECT pr_ca_id FROM shopsystem_products
			WHERE pr_id = {$nextWeek['lotw_pr_id']}
		");
		$category = $productCategory['pr_ca_id'];
	}
	$this->param('Key',$key);
	$this->param('pr_ca_id',$category);
	
?>