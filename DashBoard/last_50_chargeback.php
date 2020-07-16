<?php
    require_once('session.php');
    require_once('func.php');
	$year = 2011;
	if( array_key_exists( 'year', $_GET ) )
		$year = (int) $_GET['year'];
	$nextyear = $year+1;

    echo "<style type=\"text/css\">@import url(\"/DashBoard/site.css\");</style>";

    display_query( "select * from blacklist where bl_reason = 'OWNER_CHARGEBACK' or bl_reason = 'STOLEN_CREDITCARD' order by bl_id desc limit 50",
		1, array( 9999999 => 'Total' ));

?>
