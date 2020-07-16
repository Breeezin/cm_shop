<?php
	$Title = "Enter Business statistics";
    require_once('session.php');
    echo "<style type=\"text/css\">@import url(\"/DashBoard/site.css\");</style>";

	$show_se = false;
    if( $_GET['date'] == null )		// only if we are looking at today do we want to do this...
		{

		$foo = mysql_fetch_array( mysql_query( "SELECT COUNT(*) AS count FROM se_rank where sr_date = CURDATE()" ) );
		if( $foo['count'] > 0 )
			$show_se = true;
		else
			require( 'search_engine.php' );
		}
	else
		{
		$foo = mysql_fetch_array( mysql_query( "SELECT COUNT(*) AS count FROM se_rank where sr_date = ".addslashes( $_GET['date'] ) ) );
		if( $foo['count'] > 0 )
			$show_se = true;
		else
			echo "No search results recorded on this day<br>";
		}

	if( $show_se )
		{
		echo "Previously gathered results<br>";

		if( $_GET['date'] == null )
			$query = "select rk_date, rk_order, rk_keywords, rk_weight
					from rank_keywords 
					where rk_date <= CURDATE()
					order by rk_date desc, rk_order asc";
		else
			$query = "select rk_date, rk_order, rk_keywords, rk_weight
					from rank_keywords 
					where rk_date <= ".addslashes( $_GET['date'] )."
					order by rk_date desc, rk_order asc";

		if( ($result_rk = mysql_query( $query ) ) == false )
			{
			echo "Error selecting from se_rank -- " . mysql_error();
			exit;
			}

		$tu = Array();
		$tu_n = 0;

		if( ($result_tu = mysql_query( "select * from target_url" )) == false)
			{
			echo "Error selecting from target_url -- " . mysql_error();
			exit;
			}

		while( $row_tu = mysql_fetch_array($result_tu))
			{
			$tu[] = $row_tu;
			$tu_n++;
			}

		mysql_free_result($result_tu);

		$se = Array();
		$se_n = 0;

		if( ($result_se = mysql_query( "select * from search_engine order by se_search_engine" )) == false)
			{
			echo "Error selecting from search_engine -- " . mysql_error();
			exit;
			}


		while( $row_se = mysql_fetch_array($result_se))
			{
			$se[] = $row_se;
			$se_n++;
			}

		mysql_free_result($result_se);

		echo "<br>Search Engine Rankings<br>";
		echo "<table BORDER WIDTH=\"100%\" BGCOLOR=\"#FFFFFF\" NOSAVE >";
		echo "<tr>";
		echo "<td></td>";
		echo "<td></td>";
		for($i = 0; $i < $se_n; $i++ )
			echo "<td colspan=".$tu_n."><b>".$se[$i]['se_label']."</b></td>";
		echo "</tr>";
		echo "<tr>";
		echo "<td></td>";
		echo "<td><b>Keywords</b></td>";
		for($i = 0; $i < $se_n; $i++ )
			for( $j = 0; $j < $tu_n; $j++ )
				echo "<td><b>".$tu[$j]['tu_label']."</b></td>";
		echo "</tr>";


		$fdate = "";

		while ($row_rk = mysql_fetch_array($result_rk))
			{
			if( $fdate == "" )
				$fdate = $row_rk['rk_date'];
			else
				if( $fdate != $row_rk['rk_date'] )
					break;

			echo "<tr>";
			echo "<td>".$row_rk['rk_order']."</td>";
			echo "<td>".$row_rk['rk_keywords']."</td>";
			$row_rk['rank'] = array();

			for($i = 0; $i < $se_n; $i++ )
				{
				$args = str_replace( ' ', $se[$i]['se_space_char'], $row_rk['rk_keywords'] );
				$url = $se[$i]['se_submit_url'].$args;

				// get the result

				for( $j = 0; $j < $tu_n; $j++ )
					{
	//				$rank = rand(1, 100);
					$query = "select sr_rank from se_rank where";

					if( $_GET['date'] == null )
						$query .=	" sr_date = CURDATE()";
					else
						$query .=	" sr_date = ".addslashes( $_GET['date'] );

					$query .= " and sr_keywords = '".$row_rk['rk_keywords']."'
						and sr_search_engine = ".$se[$i]['se_search_engine']."
						and sr_target_url = ".$tu[$j]['tu'];

//						echo $query;
						
					if( ( $res = mysql_query($query)) == false )
						{
							echo "Error getting se result<br>" .$query. "<br> Error: " . mysql_error()."<br> Err #". mysql_errno();
							exit;
						}
					else
						{
						$row = mysql_fetch_array( $res );
						$row_rk['rank'][$j] = $row['sr_rank'];
						}

					echo "<td>".$row_rk['rank'][$j]."</td>";
					}
				}

			echo "</tr>";
			}

		echo "</table>";

		mysql_free_result($result_rk);
		}

    // OK that's the SE stuff done.  Now grab some info the the DB.  Mainly ripped off from Matt & Nam's very special code.

    // unique clients
    $foo = mysql_fetch_array( mysql_query( "SELECT COUNT(DISTINCT or_us_id) AS DistinctCustomers FROM shopsystem_orders" ) );
    $unique_clients = $foo['DistinctCustomers'];

    // newsletter clients
    $foo = mysql_fetch_array( mysql_query( 
            "SELECT count(*) FROM users, user_user_groups
                WHERE uug_us_id = us_id
                    AND uug_ug_id = 3
                    AND us_no_spam IS NULL " ) );
    $newsletter_clients = $foo[0];

    // wishlist clients
    $foo = mysql_fetch_array( mysql_query( 
            "SELECT COUNT(DISTINCT stn_us_id) AS TheValue FROM shopsystem_stock_notifications" ) );
    $wishlist_clients = $foo['TheValue'];

    // blacklist clients
    $foo = mysql_fetch_array( mysql_query( 
            "SELECT COUNT(*) AS TheValue FROM shopsystem_blacklist" ) );
    $blacklist_clients = $foo['TheValue'];

    // repeat clients
    $repeat_clients = mysql_num_rows( mysql_query( 
            "SELECT or_us_id, COUNT(*) AS OrderCount FROM shopsystem_orders
                GROUP BY or_us_id
                having count(*) > 1" ) );

    // shipping delay
    $foo = mysql_fetch_array( mysql_query( 
            "SELECT AVG(shp_days_since_ordered) AS AverageDays FROM shopsystem_shipped_products
                WHERE shp_days_since_ordered IS NOT NULL" ) );
    $averageShipping = $foo['AverageDays'];

    // WTF?
    $foo = mysql_fetch_array( mysql_query( 
            "SELECT BaBaAmount FROM ShopSystem_BankBalances
                ORDER BY BaBaID DESC
                LIMIT 1 " ) );
    $bank_amount = $foo['BaBaAmount'];

    // debt etc
    $foo = mysql_fetch_array( mysql_query( 
            "SELECT SUM(sos_total),COUNT(*), MIN(sos_date) AS Owe FROM shopsystem_supplier_order_sheets
                WHERE sos_paid IS NULL " ) );
    $supplier_debt = $foo[0];
    $num_supplier_unpaid = $foo[1];
    $oldest_shipping_unpaid = $foo[2];
    

    // 
    $foo = mysql_fetch_array( mysql_query( 
            "SELECT SUM(ssc_amount), COUNT(*), MIN(ssc_date) FROM shopsystem_shipping_charges
                WHERE ssc_paid IS NULL" ) );
    $shipping_owed = $foo[0];
    $num_shipping_unpaid = $foo[1];
    $oldest_shipping_unpaid = $foo[2];

    if( $_GET['date'] != null )
        {
        $target_res = mysql_fetch_array( mysql_query( "select to_days('".$_GET['date']."'), DATE_SUB('".$_GET['date']."', INTERVAL 1 DAY)" ) );
        $target_date = $target_res[0];
		$yesterday = $target_res[1];
        }
    else
        {
        $target_res = mysql_fetch_array( mysql_query( "select to_days(CURDATE()), DATE_SUB( CURDATE(), INTERVAL 1 DAY )" ) );
        $target_date = $target_res[0];
		$yesterday = $target_res[1];
        }

	$dateDisplayRes = mysql_fetch_array( mysql_query( "select from_days(".$target_date.")" ) );
	$dateDisplay = $dateDisplayRes[0];

    // num orders today
    $foo = mysql_fetch_array( mysql_query( 
            "SELECT COUNT(*) AS TheValue FROM shopsystem_orders
                WHERE to_days(or_shipped) = ".$target_date ) );
    $num_orders = $foo['TheValue'];

    // num boxes shipped today
    $foo = mysql_fetch_array( mysql_query( 
            "SELECT COUNT(*) AS TheValue FROM shopsystem_shipped_products
                WHERE to_days(shp_date) = ".$target_date ) );
    $num_boxes = $foo['TheValue'];

    // average shipment value
    $foo = mysql_fetch_array( mysql_query( 
            "SELECT AVG(in_total_value) AS TheValue FROM shopsystem_transit_documents, shopsystem_invoices
                WHERE TrDoInvoiceLink = inv_id and to_days(TrDoLasPalmasdeGCFecha) = ".$target_date  ) );
    $avg_ship_value = $foo['TheValue'];

    // total supplier purchases
    $foo = mysql_fetch_array( mysql_query( 
            "SELECT SUM(sos_total) AS TheValue FROM shopsystem_supplier_order_sheets
                WHERE sos_date = ".$target_date  ) );
    $total_supplier_purchases = $foo['TheValue'];
    if( !IsSet( $total_supplier_purchases ) )
        $total_supplier_purchases = 0;

    // profit
    $foo = mysql_fetch_array( mysql_query( 
            "SELECT SUM( or_profit ) AS TheValue FROM `shopsystem_orders`
                WHERE to_days(or_recorded) = ".$target_date  ) );
    $profit = $foo['TheValue'];

    // sales
    $foo = mysql_fetch_array( mysql_query( 
            "SELECT SUM( tr_total ) AS TheValue FROM transactions
                WHERE tr_charge_total IS NOT NULL
                    AND tr_completed = 1
                    AND tr_status_link < 3
                    AND to_days(tr_timestamp) = ".$target_date  ) );
    $sales = $foo['TheValue'];

    // reshipment
    $foo = mysql_fetch_array( mysql_query( 
            "SELECT COUNT(*) AS TheValue FROM shopsystem_shipped_products, shopsystem_orders 
                WHERE to_days(shp_date) = ".$target_date." AND shp_or_id = or_id AND or_reshipment IS NOT NULL" ) );
    $reshipped_boxes = $foo[''];
    if( !IsSet( $reshipped_boxes ) )
        $reshipped_boxes = 0;

    // reshipment value
    $foo = mysql_fetch_array( mysql_query( 
            "SELECT SUM(in_total_value) AS TheValue FROM shopsystem_transit_documents, shopsystem_invoices, shopsystem_orders 
                WHERE to_days(TrDoLasPalmasdeGCFecha) = ".$target_date
                    ." AND TrDoInvoiceLink = inv_id AND in_or_id = or_id AND or_reshipment IS NOT NULL" ) );
    $reshipped_value = $foo['TheValue'];
    if( !IsSet( $reshipped_value ) )
        $reshipped_value = 0;

    // refunds
    $foo = mysql_fetch_array( mysql_query( 
            "SELECT SUM(rfd_amount) AS TheValue FROM shopsystem_refunds, shopsystem_orders 
                WHERE to_days(rfd_timestamp) = ".$target_date 
                    ." AND rfd_or_id = or_id" ) );
    $refund_value = $foo['TheValue'];
    if( !IsSet( $refund_value ) )
        $refund_value = 0;

    echo "<br><b>Website Data for <a href=entry.php?date=".$yesterday.">".$dateDisplay."</a><br><form action=write.php>";
	echo "<input type=hidden name=target_date value=\"".$target_date."\">";
    echo "<table>";
	echo "<tr><td> boxes_sold </td><td><input maxlength=10 size=10 name=ss_boxes_sold value=\"".$num_boxes."\"> </td></tr>";
	echo "<tr><td> orders </td><td><input maxlength=10 size=10 name=ss_orders value=\"".$num_orders."\"></td></tr>";
	echo "<tr><td> sales </td><td><input maxlength=10 size=10 name=ss_sales value=\"".$sales."\"></td></tr>";
	echo "<tr><td> CM </td><td><input maxlength=10 size=10 name=ss_profit value=\"".$profit."\"></td></tr>";
	echo "<tr><td> stock_cost </td><td><input maxlength=10 size=10 name=ss_stock_cost value=\"".$total_supplier_purchases."\"></td></tr>";
	echo "<tr><td> overheads </td><td><input maxlength=10 size=10 name=ss_overheads  value=\"\"></td></tr>";
	echo "<tr><td> clients </td><td><input maxlength=10 size=10 name=ss_clients value=\"".$unique_clients."\"></td></tr>";
	echo "<tr><td> repeat_clients </td><td><input maxlength=10 size=10 name=ss_repeat_clients value=\"".$repeat_clients."\"></td></tr>";
	echo "<tr><td> wishlist_clients </td><td><input maxlength=10 size=10 name=ss_wishlist_clients value=\"".$wishlist_clients."\"></td></tr>";
	echo "<tr><td> blacklist_clients </td><td><input maxlength=10 size=10 name=ss_blacklist_clients value=\"".$blacklist_clients."\"></td></tr>";
	echo "<tr><td> warehouse_stock </td><td><input maxlength=10 size=10 name=ss_warehouse_stock  value=\"\"></td></tr>";
	echo "<tr><td> newsletter_subscribed </td><td><input maxlength=10 size=10 name=ss_newsletter_subscribed value=\"".$newsletter_clients."\"></td></tr>";
	echo "<tr><td> supplier_debt_owed </td><td><input maxlength=10 size=10 name=ss_supplier_debt_owed value=\"".$supplier_debt."\"></td></tr>";
	echo "<tr><td> supplier_unpaid_orders </td><td><input maxlength=10 size=10 name=ss_supplier_unpaid_orders value=\"".$num_supplier_unpaid."\"></td></tr>";
	echo "<tr><td> supplier_oldest_unpaid </td><td><input maxlength=10 size=10 name=ss_supplier_oldest_unpaid value=\"".$oldest_shipping_unpaid."\"></td></tr>";
	echo "<tr><td> shipping_debt_owed </td><td><input maxlength=10 size=10 name=ss_shipping_debt_owed value=\"".$shipping_owed."\"></td></tr>";
	echo "<tr><td> shipping_unpaid_orders </td><td><input maxlength=10 size=10 name=ss_shipping_unpaid_orders value=\"".$num_shipping_unpaid."\"></td></tr>";
	echo "<tr><td> shipping_oldest_unpaid </td><td><input maxlength=10 size=10 name=ss_shipping_oldest_unpaid value=\"".$oldest_shipping_unpaid."\"></td></tr>";
	echo "<tr><td> reshipment_boxes </td><td><input maxlength=10 size=10 name=ss_reshipment_boxes value=\"".$reshipped_boxes."\"></td></tr>";
	echo "<tr><td> reshipment_value </td><td><input maxlength=10 size=10 name=ss_reshipment_value value=\"".$reshipped_value."\"></td></tr>";
	echo "<tr><td> refunds </td><td><input maxlength=10 size=10 name=ss_refunds value=\"".$refund_value."\"></td></tr>";
	echo "<tr><td> projected_bank_balance </td><td><input maxlength=10 size=10 name=ss_projected_bank_balance  value=\"\"></td></tr>";
	echo "<tr><td> actual_bank_balance </td><td><input maxlength=10 size=10 name=ss_actual_bank_balance  value=\"\"></td></tr>";
	echo "<tr><td> avg_shipping_days </td><td><input maxlength=10 size=10 name=ss_avg_shipping_days value=\"".$averageShipping."\"></td></tr>";
	echo "</table>";
	echo "<input type=submit value=\"Save\" name=btnG>";

    // TODO, get to grips with warehouse stock, why is the table mostly empty?
    // figure out some sort of running bank balance system?
    // ask about overheads?  Fixed, enterable, hinted?
    // need code for working out top referrers, keywords, unique visitors etc

    //  then submit all this information back to sales_summary
    //   then write the reporting system, will be based on report-engine.

?>
