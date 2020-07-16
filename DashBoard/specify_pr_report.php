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


	if( ($result_currencies = mysql_query( "select distinct po_currency, po_currency_name from payment_gateway_options;" )) == false)
		{
		echo "Error selecting from payment_gateway_options -- " . mysql_error();
		exit;
		}

	if( ($resultp = mysql_query( "select pg_id, pg_name from payment_gateways order by pg_name" )) == false)
		{
		echo "Error selecting from payment_gateways -- " . mysql_error();
		exit;
		}

	if( ($result2 = mysql_query( "select ca_id, ca_name from shopsystem_categories order by ca_name" )) == false)
		{
		echo "Error selecting from categories -- " . mysql_error();
		exit;
		}

	if( ($result4 = mysql_query( "select so_sort, so_formula, so_label from pr_sorts order by so_sort asc" )) == false)
		{
		echo "Error selecting from sorts -- " . mysql_error();
		exit;
		}

	if( ($result5 = mysql_query( "select in_include, in_formula, in_label from pr_includes order by in_include asc" )) == false)
		{
		echo "Error selecting from includes -- " . mysql_error();
		exit;
		}

	if( ($result6 = mysql_query( "select re_report, re_label, re_vars from pr_reports order by re_report asc" )) == false)
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
	{
		if( ($result7 = mysql_query( "select re_vars from pr_reports where re_report = ".$report )) == false)
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
	}
	else
	{
		$rvars = $_GET;
	}

	if( mysql_num_rows( $result6 ) > 0 )
	{
		echo    "<select name=\"Report\">";
		echo	"<option value=\"-1\" onclick=\"window.location='specify_pr_report.php'\">Predefined Reports";
		echo	"<option value=\"-2\" onclick=\"window.location='pr_report_list.php'\">Manage this list";
		echo	"<option value=\"-3\">-----------------------";
		while ($row = mysql_fetch_row($result6))
			echo "<option value =\"".$row[0]."\" onclick=\"window.location='specify_pr_report.php?report=".$row[0]."'\"".($report==$row[0]?" selected":"").">".$row[1];
		echo     "</select><br><br>";
	}
	echo "<form ACTION=\"pr_report.php\" METHOD=POST NAME=\"ShowReport\">";
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
	echo "<td>Currency</td>";
	echo "<td></td>";
	echo "<td>";
	echo     "<select name=\"filter_currency\">";
	mysql_data_seek( $result_currencies, 0 );
	while ($row = mysql_fetch_array($result_currencies))
		echo "<option value =\"".$row['po_currency']."\"".($rvars['filter_currency']==$row['po_currency']?" selected":"").">".$row['po_currency_name'];
	echo "<option value =\"TO_USD\"".($rvars['filter_currency']=='TO_USD'?" selected":"").">Converted to USD";
	echo     "</select>";
	echo "</td>";
	echo "</tr>";

	echo "<tr>";
	echo "<td></td>";
	echo "<td>Year</td>";
	echo "<td> <input type=\"Checkbox\" name=\"use_year\" value=\"yes\"".($rvars['use_year']=='yes'?" checked":"")."> </td>";
	echo "<td>";
	echo     "<select name=\"filter_year\">";
	for( $i = 2005; $i < 2020; $i++ )
		echo     "<option value =\"$i\" onclick=\"check('use_year');\"".($rvars['filter_year']==$i?" selected":"").">$i";
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
	for( $i = 2005; $i < 2020; $i++ )
		echo       "<option onclick=\"check('use_from');\"".($rvars['FromYear']==$i?" selected":"").">$i";
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
	for( $i = 2005; $i < 2020; $i++ )
		echo       "<option onclick=\"check('use_to');\"".($rvars['ToYear']==$i?" selected":"").">$i";
	echo     "</select>";

	echo "</td>";
	echo "</tr>";

	echo "<tr>";
	echo "<td></td>";
	echo "<td>vendor</td>";
	echo "<td> <input type=\"Checkbox\" name=\"use_vendor\" value=\"yes\"".($rvars['use_vendor']=='yes'?" checked":"")."> </td>";
	echo "<td>";
	echo     "<select name=\"filter_vendor\">";
	echo     "<option value =\"NULL\" onclick=\"check('use_vendor');\"".($rvars['filter_vendor']=='NULL'?" selected":"").">Las Palmas";
	echo     "<option value =\"1\" onclick=\"check('use_vendor');\"".($rvars['filter_vendor']=='1'?" selected":"").">Accessories";
	echo     "<option value =\"2\" onclick=\"check('use_vendor');\"".($rvars['filter_vendor']=='2'?" selected":"").">Swiss";
	echo     "<option value =\"3\" onclick=\"check('use_vendor');\"".($rvars['filter_vendor']=='3'?" selected":"").">Gum";
	echo     "<option value =\"4\" onclick=\"check('use_vendor');\"".($rvars['filter_vendor']=='4'?" selected":"").">Marbella";
	echo     "<option value =\"5\" onclick=\"check('use_vendor');\"".($rvars['filter_vendor']=='5'?" selected":"").">Ravi";
	echo     "<option value =\"6\" onclick=\"check('use_vendor');\"".($rvars['filter_vendor']=='6'?" selected":"").">Carlos";
	echo     "</select>";
	echo "</td>";
	echo "</tr>";

	echo "<tr>";
	echo "<td></td>";
	echo "<td>Category</td>";
	echo "<td> <input type=\"Checkbox\" name=\"use_category\" value=\"yes\"".($rvars['use_category']=='yes'?" checked":"")."> </td>";
	echo "<td>";
	echo     "<select name=\"filter_category\">";
	mysql_data_seek( $result2, 0 );
	while ($row = mysql_fetch_array($result2))
		echo "<option value =\"".$row['ca_id']."\"".($rvars['filter_category']==$row['ca_id']?" selected":"")." onclick=\"check('use_category');\">".$row['ca_name'];
	echo     "</select>";
	echo "</td>";
	echo "</tr>";

	echo "<tr>";
	echo "<td></td>";
	echo "<td>Payment Gateway</td>";
	echo "<td> <input type=\"Checkbox\" name=\"use_payment_gateway\" value=\"yes\"".($rvars['use_payment_gateway']=='yes'?" checked":"")."> </td>";
	echo "<td>";
	echo     "<select name=\"filter_payment_gateway\">";
	mysql_data_seek( $resultp, 0 );
	while ($row = mysql_fetch_array($resultp))
		echo "<option value =\"".$row['pg_id']."\"".($rvars['filter_payment_gateway']==$row['pg_id']?" selected":"")." onclick=\"check('use_payment_gateway');\">".$row['pg_name'];
	echo     "</select>";
	echo "</td>";
	echo "</tr>";

	echo "<tr>";
	echo "<td></td>";
	echo "<td>Use Archived</td>";
	echo "<td> <input type=\"Checkbox\" name=\"use_archived\" value=\"yes\"".($rvars['use_archived']=='yes'?" checked":"")."> </td>";
	echo "<td>";
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
