<?php

	$result = new Request("Security.Sudo",array('Action'=>'Start'));

    $usID = isset($this->ATTRIBUTES['us_id']) ? $this->ATTRIBUTES['us_id'] : $_SESSION['User']['us_id'];

	$allCategoriesResult = new Request("shopsystem_categories.QueryAll",array('as_id'	=>	$asset->getID()));
	$Q_Categories = $allCategoriesResult->value;

	$Q_Users = query("
		SELECT * FROM users, user_user_groups, user_groups
        WHERE us_id > 2
        AND ug_id = uug_ug_id
		AND uug_us_id = us_id
        AND uug_ug_id = 5
        ORDER BY us_first_name, us_last_name
	");

    $Q_Products = query("
        SELECT * FROM shopsystem_products, shopsystem_product_extended_options, shopsystem_userproducts
        WHERE up_stock_code = pro_stock_code
        AND pro_pr_id = pr_id
        AND up_uug_us_id = {$usID}
        ORDER BY pr_ca_id
    ");

    $result = new Request("Security.Sudo",array('Action'=>'Finish'));

    $isAdmin = false;
    if (array_key_exists('1',$_SESSION['User']['user_groups'])) {
        $isAdmin = true;
    }
	$error = '';
	
?>
