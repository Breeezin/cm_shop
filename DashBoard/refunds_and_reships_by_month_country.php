<?php
    require_once('session.php');
    require_once('func.php');
    echo "<style type=\"text/css\">@import url(\"/DashBoard/site.css\");</style>";

	$now = getdate();
	$max_year = $now['year'];
/*
	echo "<h1>2011</h1>";
    display_query( "select cn_name, as_year, as_month, sum(as_reship_value), sum(as_refund_value), sum(as_profit) from account_summary join countries on as_country = cn_id where as_year = 2011 group by cn_name, as_year, as_month
     UNION
	select cn_name, as_year, 9999999, sum(as_reship_value), sum(as_refund_value), sum(as_profit) from account_summary join countries on as_country = cn_id where  as_year = 2011 group by cn_name, as_year
     ORDER by 1, 2, 3
	", 1, array( 9999999 => 'Total' ));
*/

	for( $i = $max_year; $i >= 2012; $i-- )
	{
		echo "<h1>$i</h1>";
		display_query( "select cn_name, as_year as Year, as_month as Month, as_currency, sum(as_reship_value) as \$reship, sum(as_refund_value) as \$refund, sum(as_profit) as \$profit from account_summary join countries on as_country = cn_id where as_year = $i group by cn_name, as_year, as_month, as_currency
		 UNION
		select cn_name, as_year, 9999999, as_currency, sum(as_reship_value), sum(as_refund_value), sum(as_profit) from account_summary join countries on as_country = cn_id where  as_year = $i group by cn_name, as_year, as_currency
		 ORDER by 1, 2, 3, 4
		", 1, array( 9999999 => 'Total' ));
	}


?>
