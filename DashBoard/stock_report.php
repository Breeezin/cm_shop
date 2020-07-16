<?php
	$Title = "Generated Report";
    require_once('session.php');
    echo "<style type=\"text/css\">@import url(\"/DashBoard/site.css\");</style>";

	$SiteVars = array();
	$Cols = array();
	$ColumnName = array();
	$TotalColumn = array();
	$PrintStatement = array();

    function getInclude( $ind )
    {
		if( ($result = mysql_query( "select in_include, in_formula, in_label, in_join, in_total, in_print_statement from stock_includes where in_include = ".$ind )) == false)
			{
			echo "Error selecting from includes -- " . mysql_error();
			exit;
			}
		return mysql_fetch_array($result);
    }

    function getSort( $ind )
    {
		if( ($result = mysql_query( "select so_sort, so_formula, so_label, so_join, so_print_statement from stock_sorts where so_sort = ".$ind )) == false)
			{
			echo "Error selecting from sorts -- " . mysql_error();
			exit;
			}
		return mysql_fetch_array($result);
    }

//	import_request_variables('P');
	extract( $_POST );

	if( strlen( $new_report_name ) > 0 )
		{
		// save the POST variables in 'reports'

		$insQ = "insert into stock_reports (re_vars, re_label) values ('".addslashes(serialize( $_POST ))."', '".$new_report_name."')";
		if( mysql_query( $insQ ) == false )
			{
			echo "Error inserting reports -- " . mysql_error()." - #". mysql_errno();
			exit;
			}
		}

/*
	if( IsSet( $Screen ) )
		{

		switch( $Screen )
		{
		case 1:
			Header( "Location: specify_report.php");
			break;

		case 2:
			$Sort1 = "Year";
			$use_sort2 = "yes";
			$Sort2 = "Month";
			$use_sort3 = "yes";
			$Sort3 = "Company";
			$Include1 = "count(*)";
			$ColumnName[3] = "Count";
	//		$TotalColumn[4] = 0;
	//		$ordercols = "3 desc";
			break;

		case 3:
			$Sort1 = "company.Username";
			$ColumnName[0] = "Username";
	//        $use_sort2 = "yes";
	//        $Sort2 = "name";
			$Include1 = "sum(InputBytes)";
			$Include2 = "sum(OutputBytes)";
			$Include3 = "sum(OutputBytes)+sum(InputBytes)";
			$ColumnName[1] = "In";
			$ColumnName[2] = "Out";
			$ColumnName[3] = "Total Bytes";
			$use_include2 = "yes";
			$use_include3 = "yes";
			$TotalColumn[3] = 0;
			$ordercols = "4 desc";
			break;

		case 4:
			$Sort1 = "DOM";
			$ColumnName[0] = "Day Of Month";
	//        $use_sort2 = "yes";
	//        $Sort2 = "name";
			$Include1 = "sum(InputBytes)";
			$ColumnName[1] = "Total Bytes In";
			$TotalColumn[1] = 0;
			$use_include2 = "yes";
			$Include2 = "sum(OutputBytes)";
			$ColumnName[2] = "Total Bytes Out";
			$TotalColumn[2] = 0;
			$use_include3 = "yes";
			$Include3 = "sum(OutputBytes)+sum(InputBytes)";
			$ColumnName[3] = "Total Bytes";
			$TotalColumn[3] = 0;
	//		$ordercols = "4 desc";
			$ordercols = "1";
			break;

		case 5:
			$Sort1 = "EventDateTime";
			$use_sort2 = "yes";
			$Sort2 = "Status";
			$use_sort3 = "yes";
			$Sort3 = "TerminateCause";
			$Include1 = "count(*)";
			$ColumnName[3] = "Count";
			$TotalColumn[3] = 0;
			break;

		case 6:
			$Sort1 = "Hour";
			$Include1 = "sum(InputBytes)";
			$Include2 = "sum(OutputBytes)";
			$Include3 = "sum(OutputBytes)+sum(InputBytes)";
			$ColumnName[1] = "In";
			$ColumnName[2] = "Out";
			$ColumnName[3] = "Total Bytes";
			$use_include2 = "yes";
			$use_include3 = "yes";
			$TotalColumn[1] = 0;
			$TotalColumn[2] = 0;
			$TotalColumn[3] = 0;
			break;

		case 7:
			$Sort1 = "Company";
			$Include1 = "sum(InputBytes)";
			$Include2 = "sum(OutputBytes)";
			$Include3 = "sum(OutputBytes)+sum(InputBytes)";
			$ColumnName[1] = "In";
			$ColumnName[2] = "Out";
			$ColumnName[3] = "Total Bytes";
			$use_include2 = "yes";
			$use_include3 = "yes";
			$TotalColumn[1] = 0;
			$TotalColumn[2] = 0;
			$TotalColumn[3] = 0;
			break;

		case 8:
			$Sort1 = "DOM";
			$Include1 = "sum(InputBytes)";
			$Include2 = "sum(OutputBytes)";
			$Include3 = "sum(OutputBytes)+sum(InputBytes)";
			$ColumnName[1] = "In";
			$ColumnName[2] = "Out";
			$ColumnName[3] = "Total Bytes";
			$use_include2 = "yes";
			$use_include3 = "yes";
			$TotalColumn[1] = 0;
			$TotalColumn[2] = 0;
			$TotalColumn[3] = 0;
			break;

		case 9:
			$Sort1 = "Hour";
			$Include1 = "sum(InputBytes)";
			$Include2 = "sum(OutputBytes)";
			$Include3 = "sum(OutputBytes)+sum(InputBytes)";
			$ColumnName[1] = "In";
			$ColumnName[2] = "Out";
			$ColumnName[3] = "Total Bytes";
			$use_include2 = "yes";
			$use_include3 = "yes";
			$TotalColumn[1] = 0;
			$TotalColumn[2] = 0;
			$TotalColumn[3] = 0;
			break;


		}
		}
	*/

	echo "</head><body>";

	echo "<br><b>Filter Settings</b>->";

	if( !IsSet( $use_vendor )
	 && !IsSet( $use_offline )
	 && !IsSet( $use_combo )
	 && !IsSet( $use_upsell )
	 && !IsSet( $use_ship_eu )
	 && !IsSet( $use_ship_non_eu ) )
		echo "Result set unfiltered";

	if( IsSet( $use_vendor ) )
		{
		echo "vendor :";
		if( $filter_vendor == NULL )
			$vsql = "select ve_name from vendor where ve_id IS NULL";
		else
			$vsql = "select ve_name from vendor where ve_id <=> ".$filter_vendor;
		if( ($res_se = mysql_query( $vsql )) == false)
			{
			echo "Error selecting from vendor -- " . mysql_error();
			exit;
			}
		if( $row = mysql_fetch_row($res_se) )
			{
			echo $row[0];
			}
		mysql_free_result( $res_se );
		echo ", ";
		}

	if( IsSet( $use_offline ) )
		{
		echo "Offline :";
		if( $filter_offline )
			echo 'True';
		else
			echo 'False';
		echo ", ";
		}

	if( IsSet( $use_combo ) )
		{
		echo "Combo :";
		if( $filter_combo )
			echo 'True';
		else
			echo 'False';
		echo ", ";
		}

	if( IsSet( $use_upsell ) )
		{
		echo "Upsell :";
		if( $filter_upsell )
			echo 'True';
		else
			echo 'False';
		echo ", ";
		}

	if( IsSet( $use_ship_eu ) )
		{
		echo "Ship to EU :";
		if( $filter_ship_eu )
			echo 'True';
		else
			echo 'False';
		echo ", ";
		}

	if( IsSet( $use_ship_non_eu ) )
		{
		echo "Ship to Non-EU :";
		if( $filter_ship_non_eu )
			echo 'True';
		else
			echo 'False';
		echo ", ";
		}

	echo "<br><a href=specify_stock_report.php>Search</a>";
	echo "<br>";


	// Get the Remote IP address. If HTTP_X_FORWARDED_FOR is set, then REMOTE_ADDR
	// is the IP of the forwarding cache.  HTTP_X_FORWARDED_FOR contains the
	// actual user IP.

	if (getenv(HTTP_USER_AGENT))
	{
	  $User_Agent = getenv(HTTP_USER_AGENT);
	}

	if (getenv(HTTP_CLIENT_IP)) {
	  $Remote_IP = getenv(HTTP_CLIENT_IP);
	}
	else if (getenv(HTTP_X_FORWARDED_FOR)){
	  $Remote_IP = getenv(HTTP_X_FORWARDED_FOR);
	}
	else {
	  $Remote_IP = getenv(REMOTE_ADDR);
	}


	// Get Remote hostname if possible

	$Remote_Name = @gethostbyaddr($Remote_IP);


	// Include variables from global variable file

	$SiteVars = array();
	$Cols = array();
	$ColumnName = array();

	// Okay, let's take all of our returned form variables and throw them into an array
	// so that we can pass the whole array to ValidateOrderPage().  We need to do this
	// because the form variables won't be available within the function scope.

	while ($nextvar = each($HTTP_POST_VARS))
		{
		$SiteVars[$nextvar["key"]]=$nextvar["value"];
		}

	$SiteVars[ "Remote_IP" ] = $Remote_IP;
	$SiteVars[ "Remote_Name" ] = $Remote_Name;
	$SiteVars[ "User_Agent" ] = $User_Agent;

	$sortcolsRes = getSort($Sort1);
	$sortcols = $sortcolsRes['so_formula'];
	$tcols = "'~Total'";
	$includecolsRes = getInclude($Include1);
	$includecols = $includecolsRes['in_formula'];
	$whereclause = "";
	$filternum = 0;

	if( $use_vendor == "yes" )
		{
		if( $filternum++ > 0 )
			$whereclause .= " and ";

		if( $filter_vendor == NULL )
			$whereclause .= " pr_ve_id IS NULL";
		else
			$whereclause .= " pr_ve_id <=> ".$filter_vendor;
		}

	if( $use_offline )
		{
		if( $filternum++ > 0 )
			$whereclause .= " and ";

		if( $filter_offline )
			$whereclause .= " pr_offline = 1";
		else
			$whereclause .= " (pr_offline IS NULL or pr_offline = 0)";
		}

	if( $use_combo )
		{
		if( $filternum++ > 0 )
			$whereclause .= " and ";

		if( $filter_combo )
			$whereclause .= " pr_combo = 1";
		else
			$whereclause .= " (pr_combo IS NULL or pr_combo = 0)";
		}

	if( $use_upsell )
		{
		if( $filternum++ > 0 )
			$whereclause .= " and ";

		if( $filter_upsell )
			$whereclause .= " pr_upsell = 1";
		else
			$whereclause .= " (pr_upsell IS NULL or pr_upsell = 0)";
		}

	if( $use_ship_non_eu )
		{
		if( $filternum++ > 0 )
			$whereclause .= " and ";

		if( $filter_ship_eu )
			$whereclause .= " pr_ship_to_eu = 1";
		else
			$whereclause .= " (pr_ship_to_eu IS NULL or pr_ship_to_eu = 0)";
		}

	if( $use_ship_eu )
		{
		if( $filternum++ > 0 )
			$whereclause .= " and ";

		if( $filter_ship_non_eu )
			$whereclause .= " pr_ship_to_non_eu = 1";
		else
			$whereclause .= " (pr_ship_to_non_eu IS NULL or pr_ship_to_non_eu = 0)";
		}

/*

	if( $use_year == "yes" )
		{
		if( $filternum++ > 0 )
			$whereclause .= " and ";
		$whereclause .= "EXTRACT( YEAR from or_recorded ) = " . $filter_year;
		}

	if( $use_month == "yes" )
		{
		if( $filternum++ > 0 )
			$whereclause .= " and ";
		$whereclause .= "EXTRACT( MONTH from or_recorded ) = " . $filter_month;
		}

	if( $use_DOM == "yes" )
		{
		if( $filternum++ > 0 )
			$whereclause .= " and ";
		$whereclause .= "EXTRACT( DAY from or_recorded ) = " . $filter_DOM;
		}

	if( $use_from == "yes" )
		{
		if( $filternum++ > 0 )
			$whereclause .= " and ";
		$whereclause .= "or_recorded >= \"" . $FromYear . "-" . $FromMonth . "-" . $FromDay . "\"";
		}

	if( $use_to == "yes" )
		{
		if( $filternum++ > 0 )
			$whereclause .= " and ";
		$whereclause .= "or_recorded <= \"" . $ToYear . "-" . $ToMonth . "-" . $ToDay . "\"";
		}
*/

	$Cols[0] = $sortcolsRes['so_formula'];
	$ColumnName[0] = $sortcolsRes['so_label'];
	$PrintStatement[0] = $sortcolsRes['so_print_statement'];
	$cols = 1;
	$sortorder = "1 ";

	for( $i = 2; $i < 10; $i++ )
		{
		if( ${"use_sort".$i} == "yes" )
			{
			$res = getSort( ${"Sort".$i} );
			$Cols[$cols] = $res['so_formula'];
			$ColumnName[$cols] = $res['so_label'];
			$PrintStatement[$cols] = $res['so_print_statement'];
			$cols++;
			$sortcols .= ", ".$res['so_formula'];
			$sortorder .= ", ".$i;
			$tcols .= ", '~Total'";
			}

		}

	$nsort = $cols;
	$Cols[$cols] = $includecolsRes['in_formula'];
	$ColumnName[$cols] = $includecolsRes['in_label'];
	$PrintStatement[$cols] = $includecolsRes['so_print_statement'];
	$TotalColumn[$cols] = $includecolsRes['in_total'];
	$cols++;

	for( $i = 2; $i < 10; $i++ )
	{
		if( ${"use_include".$i} == "yes" )
			{
			$res = getInclude( ${"Include".$i} );
			$Cols[$cols] = $res['in_formula'];
			$ColumnName[$cols] = $res['in_label'];
			$PrintStatement[$cols] = $res['so_print_statement'];
			$includecols .= ", ".$res['in_formula'];
			$TotalColumn[$cols] = $res['in_total'];
			$cols++;
			}
	}

	$final_tablename = "shopsystem_products JOIN shopsystem_categories on pr_ca_id = ca_id JOIN shopsystem_product_extended_options ON pr_id = pro_pr_id LEFT JOIN product_type on pt_id = pr_type"; 

	$query = "select ".$Cols[0];
	for( $i = 1; $i < $nsort; $i++ )
		$query .= ", ".$Cols[$i];
	for( ; $i < $cols; $i++ )
		$query .= ", ".$Cols[$i];

	$query .= " from ".$final_tablename;

	if( $whereclause != "" )
		$query .= " where ".$whereclause;

	$query .= " group by ".$sortcols;
	if( IsSet($ordercols) )
		$query .= " order by ".$ordercols;
	else
		$query .= " order by ".$sortorder;

	echo "<br>";
	echo $query;
	echo "<br>";

	if( ($result = mysql_query( $query )) == false)
		{
		echo "Error selecting from tables -- " . mysql_error();
		exit;
		}

	echo "<br>";

	echo "<table BORDER COLS=".$cols."WIDTH=\"100%\" BGCOLOR=\"#FFFFFF\" NOSAVE >";

	echo "<tr>";
	for( $i = 0; $i < $cols; $i++ )
		{
		if( IsSet( $ColumnName[$i] ) )
			echo "<td><b>".$ColumnName[$i]."</b></td>";
		else
			echo "<td><b>".$Cols[$i]."</b></td>";
		}
	echo "</tr>";

	while ($row = mysql_fetch_row($result))
		{
		echo "<tr>";
		for( $i = 0; $i < $cols; $i++ )
			{
			echo "<td>";

			if( IsSet( $TotalColumn[$i] ) && $TotalColumn[$i] >= 0 )
				{
				$TotalColumn[$i] += $row[$i];

				if( strlen( $PrintStatement[$i] ) > 0 )
					{
					$r = $row[$i];		//	assumption in these statements is that $r hold the print variable
					eval( $PrintStatement[$i] );
					}
				else
					echo number_format( $row[$i] );
				}
			else
				{
				if( strlen( $PrintStatement[$i] ) > 0 )
					{
					$r = $row[$i];
					eval( $PrintStatement[$i] );
					}
				else
					echo $row[$i];
				}

			echo "</td>";
			}
		echo "</tr>";
		}
	echo "<tr><td>Sum</td>";
	for( $i = 1; $i < $cols; $i++ )
		{
		if( IsSet( $TotalColumn[$i] ) && $TotalColumn[$i] >= 0 )
			echo "<td>".number_format( $TotalColumn[$i] )."</td>";
		else
			echo "<td></td>";
		}
	echo "</tr>";
	echo "</table>";

	mysql_free_result($result);

	exit;

?>
