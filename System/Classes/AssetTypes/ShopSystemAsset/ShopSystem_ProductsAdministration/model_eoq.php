<?php
	$results = array();
	$Title = "Economic Order Quantity";
	$cost_per_order_event = 100;	// euro
	$holding_factor = 0.1;		// proportion
	$interval_time = 30;		// days
	$lead_time = 20;			// days
	$last_bit = "";
	//echo "<a href='custom_reports.php'>Back</a>";

	$result = query("select * from shopsystem_products JOIN shopsystem_product_extended_options ON pr_id = pro_pr_id JOIN shopsystem_categories on ca_id = pr_ca_id where pr_ve_id = 2 and pr_combo is null and pr_offline IS NULL Order by pr_ca_id");
	if (!$result)
		die("no results available!");


//	while($row = mysql_fetch_assoc($result)) 
	while( $row = $result->fetchRow() )
		{
		// grab sales data for the last month...
//		print( "<br/>Product :".$row['pr_id'] );

		$sales_last_month = getRow( "select sum(op_quantity) AS Quantity from shopsystem_orders JOIN ordered_products on op_or_id = or_id where op_pr_id = ".$row['pr_id']." AND or_shipped > CURDATE()-INTERVAL $interval_time day" );

		$sales_last_year = getRow( "select sum(op_quantity) AS Quantity from shopsystem_orders JOIN ordered_products on op_or_id = or_id where op_pr_id = ".$row['pr_id']." AND or_shipped > CURDATE()-INTERVAL 1 year AND or_shipped < CURDATE()-INTERVAL 1 year+INTERVAL $interval_time day" );

//		print( "<br/>Sales last year<br/>" );
//		print_r( $sales_last_year );
		// this could be better, one day...
		$sales = 0;
		$importance = 0;
		if( $sales_last_month['Quantity'] > 0 )
			$sales = $sales_last_month['Quantity'];
		else
			if( $sales_last_year['Quantity'] > 0 )
				$sales = $sales_last_year['Quantity']/11;
		if( $sales_last_month['Quantity'] > 0 && $sales_last_year['Quantity'] > 0 )		// average them
			$sales = ($sales_last_month['Quantity']*12+$sales_last_year['Quantity'])/12;

		$name = str_replace( ",", "", $row['pr_name'] );

		$description = "'{$row['ca_name']}', $name, {$row['pro_stock_code']}, {$row['pro_stock_available']},". number_format($sales,0,'.','').",";
		if( $sales > 0 )
			{
			$q = 0;

			// cost for this product is $row[pro_supplier_price] Q^* = \sqrt{\frac{2CR}{PF}} = \sqrt{\frac{2CR}{H}}.
			if( $row['pro_supplier_price'] > 0 )
				{
				$q = round(sqrt( 2*$cost_per_order_event*$sales
							/$row['pro_supplier_price']/$holding_factor ));
				$q2 = $q * $row['pr0_883_f'];


				//echo "<br> Product:${row['pr_id']} Name:${row['pr_name']} Code:${row['pro_stock_code']} Stock: ${row['pro_stock_available']} Sales:$sales EOQ:$q";
				// will we run out in $lead_time, i.e. do we order NOW ?

				// stock order importance calculation
				$days_shortfall = $sales*$row['pr_stock_lead_time']/$interval_time - $row['pro_stock_available'];
				$profit = $row['pro_price'] - $row['pro_supplier_price'];
				if( $row['pro_special_price'] !== null )
					$profit = $row['pro_special_price'] - $row['pro_supplier_price'];
				$importance = $days_shortfall*($sales * $profit);
				$description .= number_format($days_shortfall, 0,'.','')."";
				/*
				if( $row['pro_stock_available'] > $sales*$row['pr_stock_lead_time']/$interval_time )
					$description .= "0";
				else
					$description .= "$q";
					*/

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

		if( ($importance > 0) && ($q > 0))
			{
			if( $description != "" )
				$results[] = "".number_format($importance,0,'.','').",".$description;
			$last_bit .= ",".$description."\n";
			}
		}

	rsort( $results, SORT_NUMERIC );
	$output = "";

	foreach( $results as $foo )
		$output .= $foo."\n";

	$output .= "\n\n\n\n\n\n\n\n\n".$last_bit;

	header("Pragma: public");
	header("Expires: 0");
	header("Cache-Control: must-revalidate, post-check=0, pre-check=0"); 
	header("Content-Type: application/force-download");
	header("Content-Type: application/octet-stream");
	header("Content-Type: application/download");
	header("Content-Disposition: attachment; filename=Ordering.csv;");
	header("Content-Transfer-Encoding: binary");
	header("Content-Length: ".strlen($output));		
	print( '"Importance", "Category", "Name", "Stock Code", "Stock", "Avg Monthly Sales", "Shortfall"'."\n" );
	print($output);

	exit;
?>
