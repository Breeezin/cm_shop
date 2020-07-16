<?php
    require_once('session.php');
    require_once('func.php');
    echo "<style type=\"text/css\">@import url(\"/DashBoard/site.css\");</style>";

	$year = (int) $_GET['year'];

/*
MariaDB [pe]> describe account_summary;
+------------------------+-------------+------+-----+---------+-------+
| Field                  | Type        | Null | Key | Default | Extra |
+------------------------+-------------+------+-----+---------+-------+
| as_country             | int(11)     | NO   |     | NULL    |       |
| as_site                | varchar(64) | YES  |     | NULL    |       |
| as_gateway             | int(11)     | NO   |     | NULL    |       |
| as_year                | int(11)     | NO   |     | NULL    |       |
| as_month               | int(11)     | NO   |     | NULL    |       |
| as_day                 | int(11)     | NO   |     | NULL    |       |
| as_num_orders          | int(11)     | NO   |     | 0       |       |
| as_sales               | double      | NO   |     | 0       |       |
| as_shipping_value      | double      | NO   |     | 0       |       |
| as_supplier_cost       | double      | NO   |     | 0       |       |
| as_other_variable_cost | double      | NO   |     | 0       |       |
| as_other_fixed_cost    | double      | NO   |     | 0       |       |
| as_reship_value        | double      | NO   |     | 0       |       |
| as_reship_boxes        | int(11)     | NO   |     | 0       |       |
| as_refund_value        | double      | NO   |     | 0       |       |
| as_cm_value            | double      | NO   |     | 0       |       |
| as_new_blacklist       | int(11)     | NO   |     | 0       |       |
| as_profit              | double      | NO   |     | 0       |       |
| as_currency            | char(3)     | NO   |     | EUR     |       |
| as_dirty               | tinyint(1)  | NO   |     | 0       |       |
| as_usd_rate            | double      | NO   |     | 1       |       |
+------------------------+-------------+------+-----+---------+-------+
21 rows in set (0.00 sec)
*/

	mysql_query( "create temporary table foo as select cn_name, cn_id, tr_currency_code, count(orsi_box_number) as Boxes, cast( 0.0  as double ) as Value from shopsystem_orders JOIN transactions on or_tr_id = tr_id JOIN shopsystem_order_sheets_items ON or_id = orsi_or_id JOIN shopsystem_product_extended_options ON orsi_stock_code = pro_stock_code JOIN shopsystem_products ON pr_id = pro_pr_id JOIN countries on cn_id = or_country where pr_deleted IS NULL and or_archive_year  = $year group by cn_name, tr_currency_code order by 1" );
	mysql_query( "create temporary table foo2 as select as_country, as_currency, sum(as_sales) as as_sales from account_summary where as_year = $year group by as_country, as_currency" );
	mysql_query( "update foo, foo2 set foo.Value = as_sales where cn_id = as_country and tr_currency_code = as_currency" );

	echo "<h1>$year</h1>";
    display_query( "select cn_name, Boxes, tr_currency_code, Value from foo",
		1, array( 9999999 => 'Total' ));

?>
