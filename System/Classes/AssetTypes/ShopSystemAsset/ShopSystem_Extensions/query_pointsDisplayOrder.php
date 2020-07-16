<?php

	$this->param('or_id');
	
	$order = getRow("
		SELECT or_tr_id FROM shopsystem_orders
		WHERE or_id = ".safe($this->ATTRIBUTES['or_id'])."
	");
	
	$Q_Points = query("
		SELECT us_first_name, us_last_name, up_id, Earned.or_tr_id AS EarnedOrder, Spent.or_tr_id AS SpentOrder, up_points, up_expires FROM 
			(shopsystem_user_points LEFT JOIN shopsystem_orders AS Earned ON shopsystem_user_points.up_or_id = Earned.or_id) LEFT JOIN shopsystem_orders AS Spent ON shopsystem_user_points.up_used = Spent.or_id, users
		WHERE up_or_id = ".safe($this->ATTRIBUTES['or_id'])."
			AND us_id = up_us_id
		ORDER BY up_id ASC
	");
	
	$CheckPoints = getRow("
		SELECT SUM(up_points) AS TotalPoints FROM shopsystem_user_points
		WHERE up_or_id = ".safe($this->ATTRIBUTES['or_id'])."
	");
				
?>