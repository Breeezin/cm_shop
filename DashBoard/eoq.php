<?php
	$results = array();
	$Title = "Economic Order Quantity";
	$cost_per_order_event = 30;	// euro
	$holding_factor = 0.1;		// proportion
	$interval_time = 30;		// days
	$lead_time = 20;			// days
    require_once('session.php');
    echo "<style type=\"text/css\">@import url(\"/DashBoard/site.css\");</style>";

	$details = ($_GET['details'] == 1);

	echo "</head>";
	//echo "<a href='custom_reports.php'>Back</a>";

	$result = mysql_query("select * from shopsystem_products JOIN shopsystem_product_extended_options ON pr_id = pro_pr_id where pr_stock_warning is not null and pr_combo is null and pr_offline is null");
	if (!$result)
		die("no results available!");

	while($row = mysql_fetch_assoc($result)) 
		{
		$description = "";
		// grab sales data for the last month...
//		print( "<br/>Product :".$row['pr_id'] );

		$sales_last_month_result = mysql_query( "select sum(op_quantity) AS Quantity from shopsystem_orders JOIN ordered_products on op_or_id = or_id where ProductLink = ".$row['pr_id']." AND or_shipped > CURDATE()-INTERVAL $interval_time day" );
		$sales_last_month = mysql_fetch_assoc( $sales_last_month_result );

		$sales_last_year_result = mysql_query( "select sum(op_quantity) AS Quantity from shopsystem_orders JOIN ordered_products on op_or_id = or_id where ProductLink = ".$row['pr_id']." AND or_shipped > CURDATE()-INTERVAL 1 year AND or_shipped < CURDATE()-INTERVAL 1 year+INTERVAL $interval_time day" );
		$sales_last_year = mysql_fetch_assoc( $sales_last_year_result );

		// this could be better, one day...
		$sales = 0;
		$importance = 0;
		if( $sales_last_month['Quantity'] > 0 )
			$sales = $sales_last_month['Quantity'];
		if( $sales_last_year['Quantity'] > 0 )
			$sales = $sales_last_year['Quantity'];
		if( $sales_last_month['Quantity'] > 0 && $sales_last_year['Quantity'] > 0 )		// average them
			$sales = ($sales_last_month['Quantity']+$sales_last_year['Quantity'])/2;

		if( $sales > 0 )
			{
			// cost for this product is $row[pro_supplier_price] Q^* = \sqrt{\frac{2CR}{PF}} = \sqrt{\frac{2CR}{H}}.
			if( $row['pro_supplier_price'] > 0 )
				{
				$q = round(sqrt( 2*$cost_per_order_event*$sales
							/$row['pro_supplier_price']/$holding_factor ));

				$description = "Product:${row['pr_id']} Name:${row['pr_name']} Code:${row['pro_stock_code']} Stock on Hand: ${row['pro_stock_available']} ";
				//echo "<br> Product:${row['pr_id']} Name:${row['pr_name']} Code:${row['pro_stock_code']} Stock: ${row['pro_stock_available']} Sales:$sales EOQ:$q";
				// will we run out in $lead_time, i.e. do we order NOW ?
				if( $details )
					{
					$description .= "<br/>Sales last month<br/>";
					$description .=  print_r($sales_last_month, TRUE);
					$description .= "<br/>Sales last year<br/>";
					$description .= print_r($sales_last_year, TRUE);
					}

				if( $row['pro_stock_available'] > $sales*$row['pr_stock_lead_time']/$interval_time )
					{
					// No
//					echo " OK";
					$description .= " OK for now";
					}
				else
					{
					//echo " : <b>You need to order $q Units NOW</b>";
					$description .= " <b>You need to order $q Units NOW</b>";
					}
				// stock order importance calculation
				/*
				$days_left = $row['pro_stock_available']-$row['pr_stock_lead_time']/$interval_time;
				if( $days_left < 0 )
					$days_left = 0;

				$profit = $row['pro_price'] - $row['pro_supplier_price'];
				if( $row['pro_special_price'] !== null )
					$profit = $row['pro_special_price'] - $row['pro_supplier_price'];
				$importance = ($sales * $profit)/(1+$days_left);
				*/
				$days_shortfall = $sales*$row['pr_stock_lead_time']/$interval_time - $row['pro_stock_available'];
				$profit = $row['pro_price'] - $row['pro_supplier_price'];
				if( $row['pro_special_price'] !== null )
					$profit = $row['pro_special_price'] - $row['pro_supplier_price'];
				$importance = $days_shortfall*($sales * $profit);
				
				// insertion sort of array element $importance => $description
//				echo "<br>importance = ($sales * $profit)/(1+$days_left) = $importance";
//				if( !mysql_query("update shopsystem_products set pr_stock_order_urgency = $importance where pr_id = ".$row['pr_id']) )
//					echo "<br><b>Update Failed</b>";
				}
			else
				{
//				echo "<br>No cost for Product:".$row['pr_id'];
//				if( !mysql_query("update shopsystem_products set pr_stock_order_urgency = 0 where pr_id = ".$row['pr_id']) )
//					echo "<br><b>Update Failed</b>";

				}
			}
		else
			{
//			echo "<br/>Sales is ".$sales;
//			if( !mysql_query("update shopsystem_products set pr_stock_order_urgency = 0 where pr_id = ".$row['pr_id']) )
//				echo "<br><b>Update Failed</b>";

			}

		// insertion sort of array element $importance => $description

		$results[] = "".$importance."|".$description;
		}

	mysql_free_result($result);

	rsort( $results, SORT_NUMERIC );

	print( "<br/>" );
	foreach( $results as $foo )
		{
		print( substr($foo, strpos( $foo, "|" )+1 )."<br/>" );
//		print( $foo."<br/>" );
		}

	exit;
?>
