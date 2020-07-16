<?php
    require_once('session.php');
    require_once('func.php');
    echo "<style type=\"text/css\">@import url(\"/DashBoard/site.css\");</style>";

	display_query( "select cn_name, or_shipped, or_tr_id, or_purchaser_email, orn_text from shopsystem_orders join countries on or_country = cn_id join shopsystem_order_notes on orn_or_id = or_id where (or_archive_year = 2011 or or_archive_year = 2010 or or_archive_year IS NULL) and orn_text NOT LIKE '%This order is%' and orn_text NOT LIKE '%Vacuum%' order by or_shipped desc
	" );

	exit;
?>
