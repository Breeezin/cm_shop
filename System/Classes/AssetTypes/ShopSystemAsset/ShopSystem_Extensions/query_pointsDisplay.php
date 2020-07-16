<?php

	$this->param('or_id');
	
	$orderUser = getRow("
		SELECT or_us_id FROM shopsystem_orders
		WHERE or_id = ".safe($this->ATTRIBUTES['or_id'])."
	");
	if ($orderUser === null) die('Order does not exist');
	$this->ATTRIBUTES['us_id'] = $orderUser['or_us_id'];
	
	$Q_Points = query("
		SELECT up_id, Earned.or_tr_id AS EarnedOrder, Spent.or_tr_id AS SpentOrder, up_points, up_expires FROM 
			(shopsystem_user_points LEFT JOIN shopsystem_orders AS Earned ON shopsystem_user_points.up_or_id = Earned.or_id) LEFT JOIN shopsystem_orders AS Spent ON shopsystem_user_points.up_used = Spent.or_id
		WHERE up_us_id = ".safe($this->ATTRIBUTES['us_id'])."
		AND up_expires > CURDATE()
		ORDER BY up_id ASC
	");
	
	$CheckPoints = getRow("
		SELECT SUM(up_points) AS TotalPoints FROM shopsystem_user_points
		WHERE up_us_id = ".safe($this->ATTRIBUTES['us_id'])." AND up_used IS NULL
		AND up_expires > CURDATE()
	");
				
	$User = getRow("
		SELECT us_first_name, us_last_name, us_email FROM users
		WHERE us_id = ".safe($this->ATTRIBUTES['us_id'])."
	");
	
?>
