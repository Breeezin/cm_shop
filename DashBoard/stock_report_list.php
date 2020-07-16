<?php
	$Title = "Manage Report Lists";
    require_once('session.php');
    echo "<style type=\"text/css\">@import url(\"/DashBoard/bigger.css\");</style>";

	import_request_variables('G');

	switch( $order )
		{
	case 0:
		$query = "select re_report, re_label, re_vars from stock_reports order by re_report asc";
		break;
	case 1;
		$query = "select re_report, re_label, re_vars from stock_reports order by re_label asc";
		break;

	default:
		$query = "select re_report, re_label, re_vars from stock_reports order by re_report asc";
		}

	if( ($result = mysql_query( $query )) == false)
		{
		echo "Error selecting from reports -- " . mysql_error();
		exit;
		}

	echo "<br>".$Title."<br>";
	echo "<br>";

	echo "<table border>";

	echo "<tr>";
	echo "<td><a href=\"stock_report_list.php?order=0\">Report#</a></td>";
	echo "<td><a href=\"stock_report_list.php?order=1\">Label</a></td>";
	echo "<td></td>";
	echo "</tr>";

	while ($row = mysql_fetch_array($result))
		{
		echo "<tr><a NAME=\"c".$row["re_report"]."\"></a>";
		echo "<td>".$row["re_report"]."</td>";
		echo "<td>".$row["re_label"]."</td>";
		echo "<td><a href=\"stock_report_edit.php?report=".$row["re_report"]."\">Edit</td>";
		echo "</tr>";
		}
	echo "</table><br><br>";

	mysql_free_result($result);

	echo "<a href=\"index.php\">Back</a>";

	exit;
?>
