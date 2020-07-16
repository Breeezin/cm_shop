<?php

	$result = new Request("Security.Sudo",array('Action'=>'Start'));

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

    $result = new Request("Security.Sudo",array('Action'=>'Finish'));

    $isAdmin = false;
    if (array_key_exists('1',$_SESSION['User']['user_groups'])) {
        $isAdmin = true;
    }
	$error = '';
	
?>
