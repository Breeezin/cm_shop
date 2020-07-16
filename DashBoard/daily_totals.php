<?php
    require_once('session.php');
    require_once('func.php');
    echo "<style type=\"text/css\">@import url(\"/DashBoard/site.css\");</style>";

/*
MariaDB [pe]> describe account_summary;
+-------------------+-------------+------+-----+---------+-------+
| Field             | Type        | Null | Key | Default | Extra |
+-------------------+-------------+------+-----+---------+-------+
| as_country        | int(11)     | NO   |     | NULL    |       |
| as_site           | varchar(64) | YES  |     | NULL    |       |
| as_gateway        | int(11)     | NO   |     | NULL    |       |
| as_year           | int(11)     | NO   |     | NULL    |       |
| as_month          | int(11)     | NO   |     | NULL    |       |
| as_day            | int(11)     | NO   |     | NULL    |       |
| as_num_orders     | int(11)     | NO   |     | 0       |       |
| as_sales          | double      | NO   |     | 0       |       |
| as_shipping_value | double      | NO   |     | 0       |       |
| as_reship_value   | double      | NO   |     | 0       |       |
| as_reship_boxes   | int(11)     | NO   |     | 0       |       |
| as_refund_value   | double      | NO   |     | 0       |       |
| as_cm_value       | double      | NO   |     | 0       |       |
| as_new_blacklist  | int(11)     | NO   |     | 0       |       |
| as_profit         | double      | NO   |     | 0       |       |
| as_currency       | char(3)     | NO   |     | EUR     |       |
| as_dirty          | tinyint(1)  | NO   |     | 0       |       |
+-------------------+-------------+------+-----+---------+-------+
*/

    echo "<h1>USD</h1>";
    display_query( "select as_currency as Currency,  as_year as Year, as_month as Month, as_day as Day, sum(as_num_orders) as NumOrders, sum(as_sales) as \$TotalSales, sum( as_shipping_value ) as \$ShippingCost, sum( as_supplier_cost) as \$SupplierCost, sum( as_other_variable_cost ) as \$OtherVariableCost, sum( as_other_fixed_cost ) as \$OtherFixedCost, sum( as_refund_value) as \$TotalRefunds, sum( as_reship_value ) as \$TotalReships, sum(as_profit) as \$OrderProfit, sum( as_sales) - sum( as_shipping_value ) - sum( as_supplier_cost) - sum( as_refund_value) - sum( as_reship_value ) - sum( as_other_variable_cost ) -  sum( as_other_fixed_cost )  as \$NetPosition, sum(as_profit) - sum( as_other_fixed_cost ) as \$NetPosition2 from account_summary where as_currency = 'USD' group by as_currency, as_year, as_month, as_day order by as_currency, as_year desc, as_month desc, as_day desc limit 30" );

    echo "<h1>EUR</h1>";
    display_query( "select as_currency as Currency,  as_year as Year, as_month as Month, as_day as Day, sum(as_num_orders) as NumOrders, sum(as_sales) as \$TotalSales, sum( as_shipping_value ) as \$ShippingCost, sum( as_supplier_cost) as \$SupplierCost, sum( as_other_variable_cost ) as \$OtherVariableCost, sum( as_other_fixed_cost ) as \$OtherFixedCost, sum( as_refund_value) as \$TotalRefunds, sum( as_reship_value ) as \$TotalReships, sum(as_profit) as \$OrderProfit, sum( as_sales) - sum( as_shipping_value ) - sum( as_supplier_cost) - sum( as_refund_value) - sum( as_reship_value ) - sum( as_other_variable_cost ) -  sum( as_other_fixed_cost ) as \$NetPosition, sum(as_profit) - sum( as_other_fixed_cost ) as \$NetPosition2  from account_summary where as_currency = 'EUR' group by as_currency, as_year, as_month, as_day order by as_currency, as_year desc, as_month desc, as_day desc limit 30" );

    echo "<h1>THE REST</h1>";
    display_query( "select as_currency as Currency,  as_year as Year, as_month as Month, as_day as Day, sum(as_num_orders) as NumOrders, sum(as_sales) as \$TotalSales, sum( as_shipping_value ) as \$ShippingCost, sum( as_supplier_cost) as \$SupplierCost, sum( as_other_variable_cost ) as \$OtherVariableCost, sum( as_other_fixed_cost ) as \$OtherFixedCost, sum( as_refund_value) as \$TotalRefunds, sum( as_reship_value ) as \$TotalReships, sum(as_profit) as \$OrderProfit, sum( as_sales) - sum( as_shipping_value ) - sum( as_supplier_cost) - sum( as_refund_value) - sum( as_reship_value ) - sum( as_other_variable_cost ) -  sum( as_other_fixed_cost )  as \$NetPosition, sum(as_profit) - sum( as_other_fixed_cost ) as \$NetPosition2 from account_summary where as_currency != 'EUR' and as_currency != 'USD' group by as_currency, as_year, as_month, as_day order by as_year desc, as_month desc, as_day desc, as_currency desc limit 60" );

    echo "<h1>Combined</h1>";
    display_query( "select 'USD',  as_year as Year, as_month as Month, as_day as Day, sum(as_num_orders ) as NumOrders, sum(as_sales / as_usd_rate) as \$TotalSales, sum( as_shipping_value / as_usd_rate ) as \$ShippingCost, sum( as_supplier_cost / as_usd_rate) as \$SupplierCost, sum( as_other_variable_cost / as_usd_rate ) as \$OtherVariableCost, sum( as_other_fixed_cost / as_usd_rate ) as \$OtherFixedCost, sum( as_refund_value / as_usd_rate) as \$TotalRefunds, sum( as_reship_value / as_usd_rate ) as \$TotalReships, sum(as_profit / as_usd_rate) as \$OrderProfit, sum( as_sales / as_usd_rate) - sum( as_shipping_value / as_usd_rate ) - sum( as_supplier_cost / as_usd_rate) - sum( as_refund_value / as_usd_rate) - sum( as_reship_value / as_usd_rate ) - sum( as_other_variable_cost / as_usd_rate ) -  sum( as_other_fixed_cost / as_usd_rate )  as \$NetPosition, sum(as_profit / as_usd_rate ) - sum( as_other_fixed_cost / as_usd_rate  ) as \$NetPosition2 from account_summary group by as_year, as_month, as_day order by as_year desc, as_month desc, as_day desc limit 60" );


    exit;
?>
