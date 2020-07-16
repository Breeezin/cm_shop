<?php
    require_once('session.php');
    require_once('func.php');
    echo "<style type=\"text/css\">@import url(\"/DashBoard/site.css\");</style>";

	display_query( "select cn_name, DATE(orsi_date_shipped), or_tr_id, count(orsi_received) from shopsystem_order_sheets_items join shopsystem_orders on or_id = orsi_or_id join countries on or_country = cn_id where orsi_date_shipped > now() - interval 200 week and orsi_received IS NOT NULL group by cn_name, DATE(orsi_date_shipped), or_tr_id order by cn_name desc, DATE(orsi_date_shipped) desc" );

	exit;
?>
