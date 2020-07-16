<?php
	$Title = "Specify Report";
    require_once('session.php');
    echo "<style type=\"text/css\">@import url(\"/DashBoard/site.css\");</style>";

?>
<SCRIPT language="Javascript">
	function check(name)
		{
		box = eval("document.ShowReport."+name); 
		box.checked = true;
		}
</SCRIPT>
<?php

	if( ($result2 = mysql_query( "select ve_id, ve_name from vendor order by ve_id" )) == false)
		{
		echo "Error selecting from vendor -- " . mysql_error();
		exit;
		}

	if( ($result4 = mysql_query( "select so_sort, so_formula, so_label from stock_sorts order by so_sort asc" )) == false)
		{
		echo "Error selecting from sorts -- " . mysql_error();
		exit;
		}

	if( ($result5 = mysql_query( "select in_include, in_formula, in_label from stock_includes order by in_include asc" )) == false)
		{
		echo "Error selecting from includes -- " . mysql_error();
		exit;
		}

	if( ($result6 = mysql_query( "select re_report, re_label, re_vars from stock_reports order by re_report asc" )) == false)
		{
		echo "Error selecting from reports -- " . mysql_error();
		exit;
		}


	echo "</head><body>";

	// options to save the chosen reports...

	$report = "";
	if( IsSet( $_POST['report'] ) )
		$report = $_POST['report'];

	if( IsSet( $_GET['report'] ) )
		$report = $_GET['report'];

	$rvars = array();
	$report = addslashes( $report );
	if( strlen( $report ) > 0 )
		if( ($result7 = mysql_query( "select re_vars from stock_reports where re_report = ".$report )) == false)
			{
			echo "Error selecting from reports -- " . mysql_error();
			exit;
			}
		else
			{
			// fill in the values...
			if( mysql_num_rows( $result7 ) > 0 )
				{
				$row = mysql_fetch_row($result7);
				$rvars = unserialize( $row[0] );
				if( $rvars == false )
					echo "<b> Oh no >".$row[0]."< is not unserializable<br>";
				else
					{

					}
				}
			}

	if( mysql_num_rows( $result6 ) > 0 )
	{
		echo    "<select name=\"Report\">";
		echo	"<option value=\"-1\" onclick=\"window.location='specify_stock_report.php'\">Predefined Reports";
		echo	"<option value=\"-2\" onclick=\"window.location='stock_report_list.php'\">Manage this list";
		echo	"<option value=\"-3\">-----------------------";
		while ($row = mysql_fetch_row($result6))
			echo "<option value =\"".$row[0]."\" onclick=\"window.location='specify_stock_report.php?report=".$row[0]."'\"".($report==$row[0]?" selected":"").">".$row[1];
		echo     "</select><br><br>";
	}
	echo "<form ACTION=\"stock_report.php\" METHOD=POST NAME=\"ShowReport\">";
	echo "<td><input type=\"submit\" value=\"Show Report\" name=\"Submit\"></td>";
	echo "<br><br><br><br>";

	echo "<table>";

	echo "<tr>";
	echo "<td><b>Type</b></td>";
	echo "<td><b>Spec</b></td>";
	echo "<td><b>Use</b></td>";
	echo "<td><b>Value</b></td>";
	echo "</tr>";

	echo "<tr>";
	echo "<td><b>Filters</b></td>";

	echo "<tr>";
	echo "<td></td>";
	echo "<td>vendor</td>";
	echo "<td> <input type=\"Checkbox\" name=\"use_vendor\" value=\"yes\"".($rvars['use_vendor']=='yes'?" checked":"")."> </td>";
	echo "<td>";
	echo     "<select name=\"filter_vendor\">";
	mysql_data_seek( $result2, 0 );
	while ($row = mysql_fetch_array($result2))
		echo "<option value =\"".$row['ve_id']."\"".($rvars['filter_vendor']==$row['ve_id']?" selected":"")." onclick=\"check('use_vendor');\">".$row['ve_name'];
	echo     "</select>";
	echo "</td>";
	echo "</tr>";

	echo "<tr>";
	echo "<td></td>";
	echo "<td>Offline</td>";
	echo "<td> <input type=\"Checkbox\" name=\"use_offline\" value=\"yes\"".($rvars['use_offline']=='yes'?" checked":"")."> </td>";
	echo "<td>";
	echo     "<select name=\"filter_offline\">";
	echo 		"<option value =\"0\" onclick=\"check('use_offline');\">False";
	echo 		"<option value =\"1\" onclick=\"check('use_offline');\">True";
	echo     "</select>";
	echo "</td>";
	echo "</tr>";

	echo "<tr>";
	echo "<td></td>";
	echo "<td>Combo</td>";
	echo "<td> <input type=\"Checkbox\" name=\"use_combo\" value=\"yes\"".($rvars['use_combo']=='yes'?" checked":"")."> </td>";
	echo "<td>";
	echo     "<select name=\"filter_combo\">";
	echo 		"<option value =\"0\" onclick=\"check('use_combo');\">False";
	echo 		"<option value =\"1\" onclick=\"check('use_combo');\">True";
	echo     "</select>";
	echo "</td>";
	echo "</tr>";

	echo "<tr>";
	echo "<td></td>";
	echo "<td>Upsell</td>";
	echo "<td> <input type=\"Checkbox\" name=\"use_upsell\" value=\"yes\"".($rvars['use_upsell']=='yes'?" checked":"")."> </td>";
	echo "<td>";
	echo     "<select name=\"filter_upsell\">";
	echo 		"<option value =\"0\" onclick=\"check('use_upsell');\">False";
	echo 		"<option value =\"1\" onclick=\"check('use_upsell');\">True";
	echo     "</select>";
	echo "</td>";
	echo "</tr>";

	echo "<tr>";
	echo "<td></td>";
	echo "<td>Ship to EU</td>";
	echo "<td> <input type=\"Checkbox\" name=\"use_ship_eu\" value=\"yes\"".($rvars['use_ship_eu']=='yes'?" checked":"")."> </td>";
	echo "<td>";
	echo     "<select name=\"filter_ship_eu\">";
	echo 		"<option value =\"0\" onclick=\"check('use_ship_eu');\">False";
	echo 		"<option value =\"1\" onclick=\"check('use_ship_eu');\">True";
	echo     "</select>";
	echo "</td>";
	echo "</tr>";

	echo "<tr>";
	echo "<td></td>";
	echo "<td>Ship to Non-EU</td>";
	echo "<td> <input type=\"Checkbox\" name=\"use_ship_non_eu\" value=\"yes\"".($rvars['use_ship_non_eu']=='yes'?" checked":"")."> </td>";
	echo "<td>";
	echo     "<select name=\"filter_ship_non_eu\">";
	echo 		"<option value =\"0\" onclick=\"check('use_ship_non_eu');\">False";
	echo 		"<option value =\"1\" onclick=\"check('use_ship_non_eu');\">True";
	echo     "</select>";
	echo "</td>";
	echo "</tr>";

	/*
	echo "<td></td>";
	echo "<td>Year</td>";
	echo "<td> <input type=\"Checkbox\" name=\"use_year\" value=\"yes\"".($rvars['use_year']=='yes'?" checked":"")."> </td>";
	echo "<td>";
	echo     "<select name=\"filter_year\">";
	echo     "<option value =\"2005\" onclick=\"check('use_year');\"".($rvars['filter_year']=='2005'?" selected":"").">2005";
	echo     "<option value =\"2006\" onclick=\"check('use_year');\"".($rvars['filter_year']=='2006'?" selected":"").">2006";
	echo     "<option value =\"2007\" onclick=\"check('use_year');\"".($rvars['filter_year']=='2007'?" selected":"").">2007";
	echo     "<option value =\"2008\" onclick=\"check('use_year');\"".($rvars['filter_year']=='2008'?" selected":"").">2008";
	echo     "</select>";
	echo "</td>";
	echo "</tr>";

	echo "<tr>";
	echo "<td></td>";
	echo "<td>Month</td>";
	echo "<td> <input type=\"Checkbox\" name=\"use_month\" value=\"yes\"".($rvars['use_month']=='yes'?" checked":"")."> </td>";
	echo "<td>";
	echo     "<select name=\"filter_month\">";
	echo        "<option value=\"01\" onclick=\"check('use_month');\"".($rvars['filter_month']=='01'?" selected":"").">January";
	echo        "<option value=\"02\" onclick=\"check('use_month');\"".($rvars['filter_month']=='02'?" selected":"").">February";
	echo        "<option value=\"03\" onclick=\"check('use_month');\"".($rvars['filter_month']=='03'?" selected":"").">March";
	echo        "<option value=\"04\" onclick=\"check('use_month');\"".($rvars['filter_month']=='04'?" selected":"").">April";
	echo        "<option value=\"05\" onclick=\"check('use_month');\"".($rvars['filter_month']=='05'?" selected":"").">May";
	echo        "<option value=\"06\" onclick=\"check('use_month');\"".($rvars['filter_month']=='06'?" selected":"").">June";
	echo        "<option value=\"07\" onclick=\"check('use_month');\"".($rvars['filter_month']=='07'?" selected":"").">July";
	echo        "<option value=\"08\" onclick=\"check('use_month');\"".($rvars['filter_month']=='08'?" selected":"").">August";
	echo        "<option value=\"09\" onclick=\"check('use_month');\"".($rvars['filter_month']=='09'?" selected":"").">September";
	echo        "<option value=\"10\" onclick=\"check('use_month');\"".($rvars['filter_month']=='10'?" selected":"").">October";
	echo        "<option value=\"11\" onclick=\"check('use_month');\"".($rvars['filter_month']=='11'?" selected":"").">November";
	echo        "<option value=\"12\" onclick=\"check('use_month');\"".($rvars['filter_month']=='12'?" selected":"").">December";
	echo     "</select>";
	echo "</td>";
	echo "</tr>";

	echo "<tr>";
	echo "<td></td>";
	echo "<td>Day of Month</td>";
	echo "<td> <input type=\"Checkbox\" name=\"use_DOM\" value=\"yes\"".($rvars['use_DOM']=='yes'?" checked":"")."> </td>";
	echo "<td>";
	echo     "<select name=\"filter_DOM\">";
	for( $i = 1; $i < 32; $i++ )
		echo "<option value=".$i." onclick=\"check('use_DOM');\">".$i;
	echo     "</select>";
	echo "</td>";
	echo "</tr>";

	echo "<tr>";
	echo "<td></td>";
	echo "<td>From Date</td>";
	echo "<td> <input type=\"Checkbox\" name=\"use_from\" value=\"yes\"".($rvars['use_from']=='yes'?" checked":"")."> </td>";
	echo "<td>";

	echo     "<select name=\"FromDay\">";
	for( $i = 1; $i < 32; $i++ )
		echo "<option value=".$i." onclick=\"check('use_from');\"".($rvars['FromDay']==$i?" selected":"").">".$i;
	echo     "</select>";

	echo     "<select name=\"FromMonth\">";
	echo        "<option value=\"01\" onclick=\"check('use_from');\"".($rvars['FromMonth']=='01'?" selected":"").">January";
	echo        "<option value=\"02\" onclick=\"check('use_from');\"".($rvars['FromMonth']=='02'?" selected":"").">February";
	echo        "<option value=\"03\" onclick=\"check('use_from');\"".($rvars['FromMonth']=='03'?" selected":"").">March";
	echo        "<option value=\"04\" onclick=\"check('use_from');\"".($rvars['FromMonth']=='04'?" selected":"").">April";
	echo        "<option value=\"05\" onclick=\"check('use_from');\"".($rvars['FromMonth']=='05'?" selected":"").">May";
	echo        "<option value=\"06\" onclick=\"check('use_from');\"".($rvars['FromMonth']=='06'?" selected":"").">June";
	echo        "<option value=\"07\" onclick=\"check('use_from');\"".($rvars['FromMonth']=='07'?" selected":"").">July";
	echo        "<option value=\"08\" onclick=\"check('use_from');\"".($rvars['FromMonth']=='08'?" selected":"").">August";
	echo        "<option value=\"09\" onclick=\"check('use_from');\"".($rvars['FromMonth']=='09'?" selected":"").">September";
	echo        "<option value=\"10\" onclick=\"check('use_from');\"".($rvars['FromMonth']=='10'?" selected":"").">October";
	echo        "<option value=\"11\" onclick=\"check('use_from');\"".($rvars['FromMonth']=='11'?" selected":"").">November";
	echo        "<option value=\"12\" onclick=\"check('use_from');\"".($rvars['FromMonth']=='12'?" selected":"").">December";
	echo     "</select>";

	echo     "<select name=\"FromYear\">";
	echo       "<option onclick=\"check('use_from');\"".($rvars['FromYear']=='2005'?" selected":"").">2005";
	echo       "<option onclick=\"check('use_from');\"".($rvars['FromYear']=='2006'?" selected":"").">2006";
	echo       "<option onclick=\"check('use_from');\"".($rvars['FromYear']=='2007'?" selected":"").">2007";
	echo       "<option onclick=\"check('use_from');\"".($rvars['FromYear']=='2008'?" selected":"").">2008";
	echo     "</select>";

	echo "</td>";pr_report.php?report=1
	echo "</tr>";

	echo "<tr>";
	echo "<td></td>";
	echo "<td>To Date</td>";
	echo "<td> <input type=\"Checkbox\" name=\"use_to\" value=\"yes\"".($rvars['use_to']=='yes'?" checked":"")."> </td>";
	echo "<td>";
	echo     "<select name=\"ToDay\">";
	for( $i = 1; $i < 32; $i++ )
		echo "<option value=".$i." onclick=\"check('use_to');\"".($rvars['ToDay']==$i?" selected":"").">".$i;
	echo     "</select>";

	echo     "<select name=\"ToMonth\">";
	echo        "<option value=\"01\" onclick=\"check('use_to');\"".($rvars['ToMonth']=='01'?" selected":"").">January";
	echo        "<option value=\"02\" onclick=\"check('use_to');\"".($rvars['ToMonth']=='02'?" selected":"").">February";
	echo        "<option value=\"03\" onclick=\"check('use_to');\"".($rvars['ToMonth']=='03'?" selected":"").">March";
	echo        "<option value=\"04\" onclick=\"check('use_to');\"".($rvars['ToMonth']=='04'?" selected":"").">April";
	echo        "<option value=\"05\" onclick=\"check('use_to');\"".($rvars['ToMonth']=='05'?" selected":"").">May";
	echo        "<option value=\"06\" onclick=\"check('use_to');\"".($rvars['ToMonth']=='06'?" selected":"").">June";
	echo        "<option value=\"07\" onclick=\"check('use_to');\"".($rvars['ToMonth']=='07'?" selected":"").">July";
	echo        "<option value=\"08\" onclick=\"check('use_to');\"".($rvars['ToMonth']=='08'?" selected":"").">August";
	echo        "<option value=\"09\" onclick=\"check('use_to');\"".($rvars['ToMonth']=='09'?" selected":"").">September";
	echo        "<option value=\"10\" onclick=\"check('use_to');\"".($rvars['ToMonth']=='10'?" selected":"").">October";
	echo        "<option value=\"11\" onclick=\"check('use_to');\"".($rvars['ToMonth']=='11'?" selected":"").">November";
	echo        "<option value=\"12\" onclick=\"check('use_to');\"".($rvars['ToMonth']=='12'?" selected":"").">December";
	echo     "</select>";

	echo     "<select name=\"ToYear\">";
	echo       "<option onclick=\"check('use_to');\"".($rvars['ToYear']=='2005'?" selected":"").">2005";
	echo       "<option onclick=\"check('use_to');\"".($rvars['ToYear']=='2006'?" selected":"").">2006";
	echo       "<option onclick=\"check('use_to');\"".($rvars['ToYear']=='2007'?" selected":"").">2007";
	echo       "<option onclick=\"check('use_to');\"".($rvars['ToYear']=='2008'?" selected":"").">2008";
	echo     "</select>";

	echo "</td>";
	echo "</tr>";
	*/

	for($i = 1; $i < 10; $i++)
		{
		echo "<tr>";
		if( $i == 1 )
			echo "<td><b>Sorts</b></td>";
		else
			echo "<td></td>";
		echo "<td>Sort #".$i."</td>";
		if( $i > 1 )
			echo "<td> <input type=\"Checkbox\" name=\"use_sort".$i."\" value=\"yes\"".($rvars['use_sort'.$i]=='yes'?" checked":"")."> </td>";
		else
			echo "<td></td>";
		echo "<td>";
		echo     "<select name=\"Sort".$i."\">";
		mysql_data_seek( $result4, 0 );
		while ($row = mysql_fetch_row($result4))
			echo "<option value =\"".$row[0]."\"".($rvars['Sort'.$i]==$row[0]?" selected":"")." onclick=\"check('use_sort".$i."');\">".$row[2];
		echo     "</select>";
		echo "</td>";
		echo "</tr>";
		}

	for($i = 1; $i < 10; $i++)
		{
		echo "<tr>\n";
		if( $i == 1 )
			echo "<td><b>Results</b></td>";
		else
			echo "<td></td>";
		echo "<td>Result #".$i."</td>\n";
		if( $i > 1 )
			echo "<td> <input type=\"Checkbox\" name=\"use_include".$i."\" value=\"yes\"".($rvars['use_include'.$i]=='yes'?" checked":"")."> </td>";
		else
			echo "<td></td>";

		echo "<td>\n";
		echo     "<select name=\"Include".$i."\">\n";
		mysql_data_seek( $result5, 0 );
		while ($row = mysql_fetch_row($result5))
			echo "<option value =\"".$row[0]."\"".($rvars['Include'.$i]==$row[0]?" selected":"")." onclick=\"check('use_include".$i."');\">".$row[2]."\n";
		echo     "</select>\n";
		echo "</td>\n";
		echo "</tr>\n";
		}

	echo "</table>";
	echo "Optionally, save this report as a new report called <input maxlength=20 size=20 name=\"new_report_name\"><br><br>";
	echo "<td><input type=\"submit\" value=\"Go\" name=\"Submit\"></td>";
	echo "</form>";

	echo "<br><br><a href=\"index.php\">Back</a></body></html>";
	exit;
?>
