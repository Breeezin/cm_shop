<?php
	$Title = "Manage Search Engine Keywords";
    require_once('session.php');
    echo "<style type=\"text/css\">@import url(\"/DashBoard/bigger.css\");</style>";

	import_request_variables('G');

	switch( $order )
		{
	case 0:
		$query = "select re_keyword, re_label, re_vars from keywords order by re_keyword asc";
		break;
	case 1;
		$query = "select re_keyword, re_label, re_vars from keywords order by re_label asc";
		break;

	default:
		$query = "select re_keyword, re_label, re_vars from keywords order by re_keyword asc";
		}

	if( ($result = mysql_query( $query )) == false)
		{
		echo "Error selecting from keywords -- " . mysql_error();
		exit;
		}

	echo "<br>".$Title."<br>";
	echo "<br>";

	echo "<table border>";

	echo "<tr>";
	echo "<td><a href=\"keyword_list.php?order=0\">Report#</a></td>";
	echo "<td><a href=\"keyword_list.php?order=1\">Label</a></td>";
	echo "<td></td>";
	echo "</tr>";

	while ($row = mysql_fetch_array($result))
		{
		echo "<tr><a NAME=\"c".$row["re_keyword"]."\"></a>";
		echo "<td>".$row["re_keyword"]."</td>";
		echo "<td>".$row["re_label"]."</td>";
		echo "<td><a href=\"keyword_edit.php?keyword=".$row["re_keyword"]."\">Edit</td>";
		echo "</tr>";
		}
	echo "</table><br><br>";

	mysql_free_result($result);

	echo "<a href=\"index.php\">Back</a>";

	exit;
?>
