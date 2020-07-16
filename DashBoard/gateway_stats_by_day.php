<?php
    require_once('session.php');
    require_once('func.php');
    echo "<style type=\"text/css\">@import url(\"/DashBoard/site.css\");</style>";

    echo "Gateway statistics for the last month by day<br/>";
    display_query( "select pg_name as Gateway, CAST(tr_timestamp as DATE) as Recorded, sum(if(tr_completed, 1, 0)) as Num_Successful, sum(if(tr_completed, tr_order_total, 0)) as Amount_Successful, count(*) as Num_Total, sum(tr_order_total) as Amount_Total from payment_gateways join transactions on tr_bank  = pg_id join shopsystem_orders on or_tr_id = tr_id join countries on or_country = cn_id where tr_timestamp >= now() - interval 30 day  group by pg_name, CAST(tr_timestamp as DATE)
	", 1, array( 9999999 => 'Total' ));

?>
