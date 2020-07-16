<?php
/*
shopsystem_refunds rfd_summarized = false

account_summary as_year, as_month, as_day

shopsystem_orders or_summarised = false
*/
$Title = "Reset Accounting Summary";
require_once('session.php');

if( array_key_exists( 'fromdate', $_POST )
 && array_key_exists( 'todate', $_POST ) )
{
	// reset then redirect to summary.php to redo stuff
	echo "Resetting stats from ".$_POST['fromdate']." to ".$_POST['todate']."<br />";
	print( "update shopsystem_refunds set rfd_summarized = 0 where DATE(rfd_timestamp) >= '{$_POST['fromdate']}' and DATE(rfd_timestamp) <= '{$_POST['todate']}'<br />" );
	mysql_query( "update shopsystem_refunds set rfd_summarized = 0 where DATE(rfd_timestamp) >= '{$_POST['fromdate']}' and DATE(rfd_timestamp) <= '{$_POST['todate']}'" );
	print( "update shopsystem_orders set or_summarised = 0 where DATE(or_recorded) >= '{$_POST['fromdate']}' and DATE(or_recorded) <= '{$_POST['todate']}'<br />" );
	mysql_query( "update shopsystem_orders set or_summarised = 0 where DATE(or_recorded) >= '{$_POST['fromdate']}' and DATE(or_recorded) <= '{$_POST['todate']}'" );
	print( "delete from account_summary where DATE(concat( as_year, '-', as_month, '-', as_day )) >= '{$_POST['fromdate']}' and DATE(concat( as_year, '-', as_month, '-', as_day )) <= '{$_POST['todate']}'<br />" );
	mysql_query( "delete from account_summary where DATE(concat( as_year, '-', as_month, '-', as_day )) >= '{$_POST['fromdate']}' and DATE(concat( as_year, '-', as_month, '-', as_day )) <= '{$_POST['todate']}'" );

	echo "<a href='accumulation.php?allyears=1'>Click here</a>";
	die;
}
echo "<style type=\"text/css\">@import url(\"/DashBoard/bigger.css\");</style>";
echo "</head>";
echo "<form ACTION=\"reset_summary.php\" METHOD=POST>";
echo "<input type=\"text\" name=\"fromdate\" value=\"\"/> (YYYY-MM-DD)<br />";
echo "<input type=\"text\" name=\"todate\" value=\"\"/> (YYYY-MM-DD)<br />";
echo "<input type=\"submit\" value=\"Update\" name=\"Submit\">";
echo "</form>";
?>
