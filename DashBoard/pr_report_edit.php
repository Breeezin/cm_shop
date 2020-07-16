<?php
	if( !IsSet( $_POST['report'] ) && !IsSet( $_GET['report'] ) )
		{
		header( "Location:pr_report_list.php" );
		exit;
		}

	$Title = "Manage Report";
    require_once('session.php');
    echo "<style type=\"text/css\">@import url(\"/DashBoard/bigger.css\");</style>";

	import_request_variables('G');
	import_request_variables('P');

	$query = "";
	if( IsSet( $action ) && ( $action == "update" ) )
		{
		$query = "UPDATE pr_reports set re_label = \"".$re_label."\" "
			." where re_report = ".$report;

		if( ($result = mysql_query( $query )) == false)
			{
			echo "Error updating reports -- " . mysql_error();
			exit;
			}
		}

    if( IsSet( $action ) && ( $action == "remove" ) )
        {
        $query = "delete from pr_reports "
            ." where re_report = \"".$report."\"";

        if( ($result = mysql_query( $query )) == false)
            {
            echo "Error removing from reports -- " . mysql_error();
            exit;
            }

		// now what?
		echo "<td><a href=\"pr_report_list.php\">Back</a></td>";
		echo "</html>";
		exit;
        }

	echo "<br>";
	echo $Title;
	echo "<br>";
//	echo $query;
	echo "<br>";

	$query = "SELECT * FROM pr_reports where re_report =".$report;

	if( ($result = mysql_query( $query )) == false)
		{
		echo "Error selecting from reports -- " . mysql_error();
		exit;
		}

	echo "<form ACTION=\"pr_report_edit.php\"METHOD=POST NAME=\"ReportEdit\">";

	echo "<input type=\"hidden\" value=\"".$report."\" name=\"report\">";
	echo "<input type=\"hidden\" value=\"update\" name=\"action\">";

	if ($row = mysql_fetch_array($result))
		{
		echo "<table BORDER>";
		echo "<tr>";
		echo "<td>Column</td>";
		echo "<td>Value</td>";
		echo "<td>New Value</td>";
		echo "</tr>";

		echo "<tr>";
		echo "<td>Report. Number</td>";
		echo "<td>".$row["re_report"]."</td>";
		echo "<td></td>";
		echo "</tr>";

		echo "<tr>";
		echo "<td>Label</td>";
		echo "<td>".$row["re_label"]."</td>";
		echo "<td> <input name=\"re_label\" value=\"".$row["re_label"]."\"></td>";
		echo "</tr>";

		echo "</table>";
		echo "<br>";

		echo "<input type=\"submit\" value=\"Update\" name=\"Submit\">";
		}
	else
		{
		echo "You've gone off the deep end!<br>";
		}

	mysql_free_result($result);


	$previous = "";
	$next = "";

	$query = "SELECT max(re_report) as re_report FROM pr_reports where re_report < ".$report;

	if( ($result = mysql_query( $query )) == false)
		{
		echo "Error selecting from reports -- " . mysql_error();
		exit;
		}
	else
		if ($row = mysql_fetch_array($result))
			{
			$previous = $row['re_report'];
			}

	mysql_free_result($result);

	$query = "SELECT min(re_report) as re_report FROM pr_reports where re_report > ".$report;
	if( ($result = mysql_query( $query )) == false)
		{
		echo "Error selecting from reports -- " . mysql_error();
		exit;
		}
	else
		if ($row = mysql_fetch_array($result))
			{
			$next = $row['re_report'];
			}

	mysql_free_result($result);


	echo "</form>";
	echo "<br>";

	echo "<form ACTION=\"pr_report_edit.php\" METHOD=POST>";
	echo "<input type=\"hidden\" value=\"".$report."\" name=\"report\">";
	echo "<input type=\"hidden\" value=\"remove\" name=\"action\">";
	echo "<td><input type=\"submit\" value=\"Remove\" name=\"remove\"></td>";
	echo "</form>";

	echo "<br>";
	echo "<table>";
	echo "<tr>";
	if( IsSet($previous) && strlen( $previous ) > 0 )
		echo "<td><a href=\"pr_report_edit.php?report=".$previous."\">Previous</a> </td>";
	else
		echo "<td>Previous </td>";
	echo "<td><a href=\"pr_report_list.php\">Back</a></td>";
	if( IsSet($next) && strlen( $next ) > 0 )
		echo "<td><a href=\"pr_report_edit.php?report=".$next."\">Next</a> </td>";
	else
		echo "<td>Next </td>";
	echo "</tr>";
	echo "</table>";

	exit;
?>
