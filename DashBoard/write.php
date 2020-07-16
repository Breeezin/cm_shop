<?php
	$Title = "Save Business statistics";
    require_once('session.php');
    echo "<style type=\"text/css\">@import url(\"/DashBoard/site.css\");</style>";


	$target_date = addslashes($_GET['target_date']);
//	echo "<br>select from_days(".$target_date."), DAYOFWEEK(from_days(".$target_date.")), WEEK(from_days(".$target_date.")), DAYOFMONTH(from_days(".$target_date.")), MONTH(from_days(".$target_date.")), YEAR(from_days(".$target_date."))<br>";
	$dateDisplayRes = mysql_fetch_array( mysql_query( "select from_days(".$target_date."), DAYOFWEEK(from_days(".$target_date.")), WEEK(from_days(".$target_date.")), DAYOFMONTH(from_days(".$target_date.")), MONTH(from_days(".$target_date.")), YEAR(from_days(".$target_date."))" ) );
	$dateDisplay = $dateDisplayRes[0];
	$ss_dow = $dateDisplayRes[1];
	$ss_woy = $dateDisplayRes[2];
	$ss_dom = $dateDisplayRes[3];
	$ss_month = $dateDisplayRes[4];
	$ss_year = $dateDisplayRes[5];

	$ss_boxes_sold = addslashes($_GET['ss_boxes_sold']);
	$ss_orders = addslashes($_GET['ss_orders']);
	$ss_sales = addslashes($_GET['ss_sales']);
	$ss_profit = addslashes($_GET['ss_profit']);
	$ss_stock_cost = addslashes($_GET['ss_stock_cost']);
	$ss_overheads  = addslashes($_GET['ss_overheads ']);
	$ss_clients = addslashes($_GET['ss_clients']);
	$ss_repeat_clients = addslashes($_GET['ss_repeat_clients']);
	$ss_wishlist_clients = addslashes($_GET['ss_wishlist_clients']);
	$ss_blacklist_clients = addslashes($_GET['ss_blacklist_clients']);
	$ss_warehouse_stock  = addslashes($_GET['ss_warehouse_stock ']);
	$ss_newsletter_subscribed = addslashes($_GET['ss_newsletter_subscribed']);
	$ss_supplier_debt_owed = addslashes($_GET['ss_supplier_debt_owed']);
	$ss_supplier_unpaid_orders = addslashes($_GET['ss_supplier_unpaid_orders']);
	$ss_supplier_oldest_unpaid = addslashes($_GET['ss_supplier_oldest_unpaid']);
	$ss_shipping_debt_owed = addslashes($_GET['ss_shipping_debt_owed']);
	$ss_shipping_unpaid_orders = addslashes($_GET['ss_shipping_unpaid_orders']);
	$ss_shipping_oldest_unpaid = addslashes($_GET['ss_shipping_oldest_unpaid']);
	$ss_reshipment_boxes = addslashes($_GET['ss_reshipment_boxes']);
	$ss_reshipment_value = addslashes($_GET['ss_reshipment_value']);
	$ss_refunds = addslashes($_GET['ss_refunds']);
	$ss_projected_bank_balance  = addslashes($_GET['ss_projected_bank_balance ']);
	$ss_actual_bank_balance  = addslashes($_GET['ss_actual_bank_balance ']);
	$ss_avg_shipping_days = addslashes($_GET['ss_avg_shipping_days']);

	if( strlen($ss_boxes_sold) == 0 ) $ss_boxes_sold = 0;
	if( strlen($ss_orders) == 0 ) $ss_orders = 0;
	if( strlen($ss_sales) == 0 ) $ss_sales = 0;
	if( strlen($ss_profit) == 0 ) $ss_profit = 0;
	if( strlen($ss_stock_cost) == 0 ) $ss_stock_cost = 0;
	if( strlen($ss_overheads ) == 0 ) $ss_overheads  = 0;
	if( strlen($ss_clients) == 0 ) $ss_clients = 0;
	if( strlen($ss_repeat_clients) == 0 ) $ss_repeat_clients = 0;
	if( strlen($ss_wishlist_clients) == 0 ) $ss_wishlist_clients = 0;
	if( strlen($ss_blacklist_clients) == 0 ) $ss_blacklist_clients = 0;
	if( strlen($ss_warehouse_stock ) == 0 ) $ss_warehouse_stock  = 0;
	if( strlen($ss_newsletter_subscribed) == 0 ) $ss_newsletter_subscribed = 0;
	if( strlen($ss_supplier_debt_owed) == 0 ) $ss_supplier_debt_owed = 0;
	if( strlen($ss_supplier_unpaid_orders) == 0 ) $ss_supplier_unpaid_orders = 0;
	if( strlen($ss_supplier_oldest_unpaid) == 0 ) $ss_supplier_oldest_unpaid = 0;
	if( strlen($ss_shipping_debt_owed) == 0 ) $ss_shipping_debt_owed = 0;
	if( strlen($ss_shipping_unpaid_orders) == 0 ) $ss_shipping_unpaid_orders = 0;
	if( strlen($ss_shipping_oldest_unpaid) == 0 ) $ss_shipping_oldest_unpaid = 0;
	if( strlen($ss_reshipment_boxes) == 0 ) $ss_reshipment_boxes = 0;
	if( strlen($ss_reshipment_value) == 0 ) $ss_reshipment_value = 0;
	if( strlen($ss_refunds) == 0 ) $ss_refunds = 0;
	if( strlen($ss_projected_bank_balance ) == 0 ) $ss_projected_bank_balance  = 0;
	if( strlen($ss_actual_bank_balance ) == 0 ) $ss_actual_bank_balance  = 0;
	if( strlen($ss_avg_shipping_days) == 0 ) $ss_avg_shipping_days = 0;

	$sql = "insert into sales_summary (
							ss_date,
							ss_dow,
							ss_woy,
							ss_dom,
							ss_month,
							ss_year,
							ss_boxes_sold,
							ss_orders,
							ss_sales,
							ss_profit,
							ss_stock_cost,
							ss_overheads,
							ss_clients,
							ss_repeat_clients,
							ss_wishlist_clients,
							ss_blacklist_clients,
							ss_warehouse_stock,
							ss_newsletter_subscribed,
							ss_supplier_debt_owed,
							ss_supplier_unpaid_orders,
							ss_supplier_oldest_unpaid,
							ss_shipping_debt_owed,
							ss_shipping_unpaid_orders,
							ss_shipping_oldest_unpaid,
							ss_reshipment_boxes,
							ss_reshipment_value,
							ss_refunds,
							ss_projected_bank_balance,
							ss_actual_bank_balance,
							ss_avg_shipping_days,
							ss_unique_visitors )
					values (
							'$dateDisplay',
							$ss_dow,
							$ss_woy,
							$ss_dom,
							$ss_month,
							$ss_year,
							$ss_boxes_sold,
							$ss_orders,
							$ss_sales,
							$ss_profit,
							$ss_stock_cost,
							$ss_overheads,
							$ss_clients,
							$ss_repeat_clients,
							$ss_wishlist_clients,
							$ss_blacklist_clients,
							$ss_warehouse_stock,
							$ss_newsletter_subscribed,
							$ss_supplier_debt_owed,
							$ss_supplier_unpaid_orders,
							$ss_supplier_oldest_unpaid,
							$ss_shipping_debt_owed,
							$ss_shipping_unpaid_orders,
							$ss_shipping_oldest_unpaid,
							$ss_reshipment_boxes,
							$ss_reshipment_value,
							$ss_refunds,
							$ss_projected_bank_balance,
							$ss_actual_bank_balance,
							$ss_avg_shipping_days,
							-1
					)";

	echo "<br>".$sql."<br>";

	if( mysql_query( $sql ) == false )
		{
		if( mysql_errno() == 1062 )     // dup key on ins
			{
			$sql = "update sales_summary set
							ss_boxes_sold = $ss_boxes_sold,
							ss_orders = $ss_orders,
							ss_sales = $ss_sales,
							ss_profit = $ss_profit,
							ss_stock_cost = $ss_stock_cost,
							ss_overheads = $ss_overheads,
							ss_clients = $ss_clients,
							ss_repeat_clients = $ss_repeat_clients,
							ss_wishlist_clients = $ss_wishlist_clients,
							ss_blacklist_clients = $ss_blacklist_clients,
							ss_warehouse_stock = $ss_warehouse_stock,
							ss_newsletter_subscribed = $ss_newsletter_subscribed,
							ss_supplier_debt_owed = $ss_supplier_debt_owed,
							ss_supplier_unpaid_orders = $ss_supplier_unpaid_orders,
							ss_supplier_oldest_unpaid = $ss_supplier_oldest_unpaid,
							ss_shipping_debt_owed = $ss_shipping_debt_owed,
							ss_shipping_unpaid_orders = $ss_shipping_unpaid_orders,
							ss_shipping_oldest_unpaid = $ss_shipping_oldest_unpaid,
							ss_reshipment_boxes = $ss_reshipment_boxes,
							ss_reshipment_value = $ss_reshipment_value,
							ss_refunds = $ss_refunds,
							ss_projected_bank_balance = $ss_projected_bank_balance,
							ss_actual_bank_balance = $ss_actual_bank_balance,
							ss_avg_shipping_days = $ss_avg_shipping_days,
							ss_unique_visitors = -1
					WHERE
							ss_date = '$dateDisplay'";

			if( mysql_query( $sql ) == false )
				{
				echo "Error updateing ss result-- " . mysql_error()." - #". mysql_errno();
				exit;
				}
			else
				echo "<br><b>Updated successfully</b><br>";
			}
		else
			{
			echo "Error inserting ss result-- " . mysql_error()." - #". mysql_errno();
			}
		}
	else
		{
		echo "<br><b>Saved successfully</b><br>";
		}

	echo "<a href=\"index.php\">Back</a>";
    // TODO, get to grips with warehouse stock, why is the table mostly empty?
    // figure out some sort of running bank balance system?
    // ask about overheads?  Fixed, enterable, hinted?
    // need code for working out top referrers, keywords, unique visitors etc

    //  then submit all this information back to sales_summary
    //   then write the reporting system, will be based on report-engine.

?>
