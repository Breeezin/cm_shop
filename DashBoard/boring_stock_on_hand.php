<?php
	$Title = "Removed Stock on hand Report";
    require_once('session.php');
    echo "<style type=\"text/css\">@import url(\"/DashBoard/site.css\");</style>";

	echo "</head>";
	echo "<a href='custom_reports.php'>Back</a>";
	echo "<body class='bar'>";

	$days = array();
	$xOffset = 0;
	$xIncrement = 60; // width of bars
	$graphHeight = 700; // target height of graph
	$maxResult = 1;
	$scale = 0;
	
	if( IsSet( $_GET['remove'] ) )
		{
		// remove this product from the interesting llamas
		mysql_query( "insert into stock_report_products (srPrID) values (".$_GET['remove'].")" );
		}

	$result = mysql_query("select pr_name, pr_id, pro_stock_available from shopsystem_products JOIN shopsystem_product_extended_options ON pr_id = pro_pr_id where pr_id not in (select srPrID from stock_report_products) order by 3 desc");
	if (!$result)
		die("no results available!");

	echo '<ul class="TGraph">';
	// biggest first...
	while($row = mysql_fetch_assoc($result)) 
		{
		if( $scale == 0 )
			$scale = $graphHeight / $row['pro_stock_available'];

		$height = ($row['pro_stock_available']*$scale);
			
		echo "<li class='P1' style='height: ".$height."px; left: ".$xOffset."px;' title='${row['pr_name']}'>${row['pro_stock_available']}<br />${row['pr_name']}<br/></li>\n";
		echo "<li class='P2' style='height: 0px; left: ".$xOffset."px;' title=''><br /><a href='${_SERVER['SCRIPT_NAME']}?remove=${row['pr_id']}'>+</a></li>\n";

		$xOffset = $xOffset + $xIncrement;
		}
	mysql_free_result($result);
	echo '</ul>';

	exit;

?>
