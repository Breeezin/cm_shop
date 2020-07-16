<?php
    require_once('session.php');
    require_once('func.php');
    echo "<style type=\"text/css\">@import url(\"/DashBoard/site.css\");</style>";

    echo "Last mark paid<br/>";
    display_query( "select or_tr_id, pg_name, or_paid, or_paid_not_shipped from shopsystem_orders join transactions on tr_id = or_tr_id left join payment_gateways on pg_id = tr_bank where or_paid_not_shipped IS NOT NULL and or_archive_year IS NULL order by or_paid desc limit 150
	", 1, array( 9999999 => 'Total' ));
?>
