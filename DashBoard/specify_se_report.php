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
	$target_res = mysql_fetch_array( mysql_query( "select 
		YEAR(CURDATE()), MONTH(CURDATE()), DAYOFMONTH(CURDATE()),
		YEAR(DATE_SUB( CURDATE(), INTERVAL 1 MONTH )), MONTH(DATE_SUB( CURDATE(), INTERVAL 1 MONTH )
), DAYOFMONTH( DATE_SUB( CURDATE(), INTERVAL 1 MONTH ))
		" ) );
	$today_year = $target_res[0];
	$today_month = $target_res[1];
	$today_day = $target_res[2];
	$yesterday_year = $target_res[4];
	$yesterday_month = $target_res[5];
	$yesterday_day = $target_res[6];

	if( ($result = mysql_query( "select distinct rk_keywords from rank_keywords" )) == false)
		{
		echo "Error selecting from rank_keywords -- " . mysql_error();
		exit;
		}

	if( ($result2 = mysql_query( "select se_label, se_search_engine from search_engine" )) == false)
		{
		echo "Error selecting from search_engine -- " . mysql_error();
		exit;
		}

	if( ($result3 = mysql_query( "select tu_label, tu from target_url" )) == false)
		{
		echo "Error selecting from target_url -- " . mysql_error();
		exit;
		}

	if( ($result4 = mysql_query( "select so_sort, so_formula, so_label from sorts order by so_sort asc" )) == false)
		{
		echo "Error selecting from sorts -- " . mysql_error();
		exit;
		}

	if( ($result5 = mysql_query( "select in_include, in_formula, in_label from includes order by in_include asc" )) == false)
		{
		echo "Error selecting from includes -- " . mysql_error();
		exit;
		}

	if( ($result6 = mysql_query( "select re_report, re_label, re_vars from reports order by re_report asc" )) == false)
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
		if( ($result7 = mysql_query( "select re_vars from reports where re_report = ".$report )) == false)
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
		echo	"<option value=\"-1\" onclick=\"window.location='specify_se_report.php'\">Predefined Reports";
		echo	"<option value=\"-2\" onclick=\"window.location='se_report_list.php'\">Manage this list";
		echo	"<option value=\"-3\">-----------------------";
		while ($row = mysql_fetch_row($result6))
			echo "<option value =\"".$row[0]."\" onclick=\"window.location='specify_se_report.php?report=".$row[0]."'\"".($report==$row[0]?" selected":"").">".$row[1];
		echo     "</select><br><br>";
	}
	echo "<form ACTION=\"se_report.php\" METHOD=POST NAME=\"ShowReport\">";
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
	echo "<td>Keywords</td>";
	echo "<td> <input type=\"Checkbox\" name=\"use_keywords\" value=\"yes\"".($rvars['use_keywords']=='yes'?" checked":"")."> </td>";
	echo "<td>";
	echo     "<select name=\"filter_keywords\">";
	while ($row = mysql_fetch_row($result))
		echo "<option value =\"".$row[0]."\"".($rvars['filter_keywords']==$row[0]?" selected":"")." onclick=\"check('use_keywords');\">".$row[0];
	echo     "</select>";
	echo "</td>";
	echo "</tr>";

	echo "<tr>";
	echo "<td></td>";
	echo "<td>Search Engine</td>";
	echo "<td> <input type=\"Checkbox\" name=\"use_se\" value=\"yes\"".($rvars['use_se']=='yes'?" checked":"")."> </td>";
	echo "<td>";
	echo     "<select name=\"filter_se\">";
	while ($row = mysql_fetch_row($result2))
		echo "<option value =\"".$row[1]."\"".$row[0]."\"".($rvars['filter_se']==$row[0]?" selected":"")." onclick=\"check('use_se');\">".$row[0];
	echo     "</select>";
	echo "</td>";
	echo "</tr>";

	echo "<tr>";
	echo "<td></td>";
	echo "<td>Target URL</td>";
	echo "<td> <input type=\"Checkbox\" name=\"use_tu\" value=\"yes\"".($rvars['use_tu']=='yes'?" checked":"")."> </td>";
	echo "<td>";
	echo     "<select name=\"filter_tu\">";
	while ($row = mysql_fetch_row($result3))
		echo "<option value =\"".$row[1]."\"".$row[0]."\"".($rvars['filter_tu']==$row[0]?" selected":"")." onclick=\"check('use_tu');\">".$row[0];
	echo     "</select>";
	echo "</td>";
	echo "</tr>";

	echo "<tr>";
	echo "<td></td>";
	echo "<td>Year</td>";
	echo "<td> <input type=\"Checkbox\" name=\"use_year\" value=\"yes\"".($rvars['use_year']=='yes'?" checked":"")."> </td>";
	echo "<td>";
	echo     "<select name=\"filter_year\">";
	echo     "<option value =\"2005\" onclick=\"check('use_year');\">2005";
	echo     "<option value =\"2006\" onclick=\"check('use_year');\">2006";
	echo     "<option value =\"2007\" onclick=\"check('use_year');\">2007";
	echo     "<option value =\"2008\" onclick=\"check('use_year');\">2008";
	echo     "</select>";
	echo "</td>";
	echo "</tr>";

	echo "<tr>";
	echo "<td></td>";
	echo "<td>Month</td>";
	echo "<td> <input type=\"Checkbox\" name=\"use_month\" value=\"yes\"".($rvars['use_month']=='yes'?" checked":"")."> </td>";
	echo "<td>";
	echo     "<select name=\"filter_month\">";
	echo        "<option value=\"01\" onclick=\"check('use_month');\">January";
	echo        "<option value=\"02\" onclick=\"check('use_month');\">February";
	echo        "<option value=\"03\" onclick=\"check('use_month');\">March";
	echo        "<option value=\"04\" onclick=\"check('use_month');\">April";
	echo        "<option value=\"05\" onclick=\"check('use_month');\">May";
	echo        "<option value=\"06\" onclick=\"check('use_month');\">June";
	echo        "<option value=\"07\" onclick=\"check('use_month');\">July";
	echo        "<option value=\"08\" onclick=\"check('use_month');\">August";
	echo        "<option value=\"09\" onclick=\"check('use_month');\">September";
	echo        "<option value=\"10\" onclick=\"check('use_month');\">October";
	echo        "<option value=\"11\" onclick=\"check('use_month');\">November";
	echo        "<option value=\"12\" onclick=\"check('use_month');\">December";
	echo     "</select>";
	echo "</td>";
	echo "</tr>";

	echo "<tr>";
	echo "<td></td>";
	echo "<td>Day of Month</td>";
	echo "<td> <input type=\"Checkbox\" name=\"use_DOM\" value=\"yes\"".($rvars['use_DOM']=='yes'?" checked":"")."> </td>";
	echo "<td>";
	echo     "<select name=\"filter_DOW\">";
	for( $i = 1; $i < 32; $i++ )
		echo "<option value=".$i." onclick=\"check('use_DOM');\">".$i;
	echo     "</select>";
	echo "</td>";
	echo "</tr>";

	echo "<tr>";
	echo "<td></td>";
	echo "<td>Day</td>";
	echo "<td> <input type=\"Checkbox\" name=\"use_DOW\" value=\"yes\"".($rvars['use_DOW']=='yes'?" checked":"")."> </td>";
	echo "<td>";
	echo     "<select name=\"filter_DOW\">";
	echo        "<option value=\"01\" onclick=\"check('use_DOW');\">Sunday";
	echo        "<option value=\"02\" onclick=\"check('use_DOW');\">Monday";
	echo        "<option value=\"03\" onclick=\"check('use_DOW');\">Tuesday";
	echo        "<option value=\"04\" onclick=\"check('use_DOW');\">Wednesday";
	echo        "<option value=\"05\" onclick=\"check('use_DOW');\">Thursday";
	echo        "<option value=\"06\" onclick=\"check('use_DOW');\">Friday";
	echo        "<option value=\"07\" onclick=\"check('use_DOW');\">Saturday";
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
		echo "<option value=".$i." onclick=\"check('use_from');\">".$i;
	echo     "</select>";

	echo     "<select name=\"FromMonth\">";
	echo        "<option value=\"01\" onclick=\"check('use_from');\">January";
	echo        "<option value=\"02\" onclick=\"check('use_from');\">February";
	echo        "<option value=\"03\" onclick=\"check('use_from');\">March";
	echo        "<option value=\"04\" onclick=\"check('use_from');\">April";
	echo        "<option value=\"05\" onclick=\"check('use_from');\">May";
	echo        "<option value=\"06\" onclick=\"check('use_from');\">June";
	echo        "<option value=\"07\" onclick=\"check('use_from');\">July";
	echo        "<option value=\"08\" onclick=\"check('use_from');\">August";
	echo        "<option value=\"09\" onclick=\"check('use_from');\">September";
	echo        "<option value=\"10\" onclick=\"check('use_from');\">October";
	echo        "<option value=\"11\" onclick=\"check('use_from');\">November";
	echo        "<option value=\"12\" onclick=\"check('use_from');\">December";
	echo     "</select>";

	echo     "<select name=\"FromYear\">";
	echo       "<option onclick=\"check('use_from');\">2005";
	echo       "<option onclick=\"check('use_from');\">2006";
	echo       "<option onclick=\"check('use_from');\">2007";
	echo       "<option onclick=\"check('use_from');\">2008";
	echo     "</select>";

	echo "</td>";
	echo "</tr>";

	echo "<tr>";
	echo "<td></td>";
	echo "<td>To Date</td>";
	echo "<td> <input type=\"Checkbox\" name=\"use_to\" value=\"yes\"".($rvars['use_to']=='yes'?" checked":"")."> </td>";
	echo "<td>";
	echo     "<select name=\"ToDay\">";
	for( $i = 1; $i < 32; $i++ )
		echo "<option value=".$i." onclick=\"check('use_to');\">".$i;
	echo     "</select>";

	echo     "<select name=\"ToMonth\">";
	echo        "<option value=\"01\" onclick=\"check('use_to');\">January";
	echo        "<option value=\"02\" onclick=\"check('use_to');\">February";
	echo        "<option value=\"03\" onclick=\"check('use_to');\">March";
	echo        "<option value=\"04\" onclick=\"check('use_to');\">April";
	echo        "<option value=\"05\" onclick=\"check('use_to');\">May";
	echo        "<option value=\"06\" onclick=\"check('use_to');\">June";
	echo        "<option value=\"07\" onclick=\"check('use_to');\">July";
	echo        "<option value=\"08\" onclick=\"check('use_to');\">August";
	echo        "<option value=\"09\" onclick=\"check('use_to');\">September";
	echo        "<option value=\"10\" onclick=\"check('use_to');\">October";
	echo        "<option value=\"11\" onclick=\"check('use_to');\">November";
	echo        "<option value=\"12\" onclick=\"check('use_to');\">December";
	echo     "</select>";

	echo     "<select name=\"ToYear\">";
	echo       "<option value=2005 onclick=\"check('use_to');\">2005";
	echo       "<option value=2006 onclick=\"check('use_to');\">2006";
	echo       "<option value=2007 onclick=\"check('use_to');\">2007";
	echo       "<option value=2008 onclick=\"check('use_to');\">2008";
	echo     "</select>";

	echo "</td>";
	echo "</tr>";

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
