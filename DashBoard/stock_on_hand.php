<?php
	$Title = "Stock on hand Report";
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

	if( !IsSet( $_GET['vendor'] ) )
		$vendor = 'IS NULL';
	else
		$vendor = $_GET['vendor'];

	if( IsSet( $_GET['remove'] ) )
		{
		// remove this product from the interesting llamas
		// mysql_query( "delete from stock_report_products where srPrID = ".$_GET['remove'] );
		mysql_query( "update shopsystem_products set pr_stock_graph = NULL where pr_id = ".$_GET['remove'] );
		}

	$result = mysql_query("select pr_name, pr_id, pro_stock_available from shopsystem_products JOIN shopsystem_product_extended_options ON pr_id = pro_pr_id where pr_stock_graph is not null and pr_ve_id $vendor order by 3 desc");
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
		echo "<li class='P2' style='height: 0px; left: ".$xOffset."px;' title=''><br /><a href='${_SERVER['SCRIPT_NAME']}?vendor=$vendor&remove=${row['pr_id']}'>x</a></li>\n";

		$xOffset = $xOffset + $xIncrement;
		}
	mysql_free_result($result);
	echo '</ul>';

	exit;

?>
