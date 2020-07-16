<?php
	$Title = "Generated Report";
    require_once('session.php');
    echo "<style type=\"text/css\">@import url(\"/DashBoard/site.css\");</style>";

	$SiteVars = array();
	$Cols = array();
	$ColumnName = array();
	$TotalColumn = array();
	$PrintStatement = array();
	$do_join = false;

    function getInclude( $ind )
    {
		if( ($result = mysql_query( "select in_include, in_formula, in_label, in_join, in_total, in_print_statement from includes where in_include = ".$ind )) == false)
			{
			echo "Error selecting from includes -- " . mysql_error();
			exit;
			}
		return mysql_fetch_array($result);
    }

    function getSort( $ind )
    {
		if( ($result = mysql_query( "select so_sort, so_formula, so_label, so_join, so_print_statement from sorts where so_sort = ".$ind )) == false)
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

		$insQ = "insert into reports (re_vars, re_label) values ('".addslashes(serialize( $_POST ))."', '".$new_report_name."')";
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
	if( !IsSet( $use_year )
		&& !IsSet( $use_month )
		&& !IsSet( $use_DOM )
		&& !IsSet( $use_DOW )
		&& !IsSet( $use_WOY )
		&& !IsSet( $use_from )
		&& !IsSet( $use_to )
		&& !IsSet( $use_tu )
		&& !IsSet( $use_se ) )
		echo "Result set unfiltered";

	if( IsSet( $use_year ) )
		echo "Year :".$filter_year.", ";
	if( IsSet( $use_month ) )
		echo "Month :".$filter_month.", ";
	if( IsSet( $use_DOM ) )
		echo "DayOfMonth :".$filter_DOM.", ";
	if( IsSet( $use_DOW ) )
		{
		echo "DayOfWeek :".$filter_DOW.", ";
		switch( $filter_DOW )
		{
		case 1:
			echo "Sunday";
			break;
		case 2:
			echo "Monday";
			break;
		case 3:
			echo "Tuesday";
			break;
		case 4:
			echo "Wednesday";
			break;
		case 5:
			echo "Thursday";
			break;
		case 6:
			echo "Friday";
			break;
		case 7:
			echo "Saturday";
			break;
		}
		echo ", ";
		}
	if( IsSet( $use_WOY ) )
		echo "WeekOfYear :".$filter_WOY.", ";
	if( IsSet( $use_from ) )
		printf( "Date from :%04d-%02d-%02d, ", $FromYear, $FromMonth, $FromDay );
	if( IsSet( $use_to ) )
		printf( "Date to :%04d-%02d-%02d, ", $ToYear, $ToMonth, $ToDay );
	if( IsSet( $use_tu ) )
		{
		echo "Target URL :";
		if( ($res_se = mysql_query( "select tu_label from target_url where tu = ".$filter_tu )) == false)
			{
			echo "Error selecting from target_url -- " . mysql_error();
			exit;
			}
		if( $row = mysql_fetch_row($res_se) )
			{
			echo $row[0];
			}
		mysql_free_result( $res_se );
		echo ", ";
		}
	if( IsSet( $use_se ) )
		{
		echo "Seach Engine :";
		if( ($res_se = mysql_query( "select se_label from search_engine where se_search_engine = ".$filter_se )) == false)
			{
			echo "Error selecting from search_engine -- " . mysql_error();
			exit;
			}
		if( $row = mysql_fetch_row($res_se) )
			{
			echo $row[0];
			}
		mysql_free_result( $res_se );
		echo ", ";
		}
	if( IsSet( $use_keywords ) )
		echo "Keywords :".$filter_keywords;
	//echo "<br><a href=report.php?Screen=4?"
	//	."use_year=yes&filter_year=20".date("y")
	//	."&use_month=yes&filter_month=".date("m")
	//	."&use_DOM&filter_DOM=".date("d").">Home</a> ";
	//echo "<a href=report.php?Screen=2>Archive</a> ";
	echo "<br><a href=specify_se_report.php>Search</a>";
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

	if( $use_year == "yes" )
		{
		if( $filternum++ > 0 )
			$whereclause .= " and ";
		$whereclause .= "ss_year = " . $filter_year;
		}

	if( $use_month == "yes" )
		{
		if( $filternum++ > 0 )
			$whereclause .= " and ";
		$whereclause .= "ss_month = " . $filter_month;
		}

	if( $use_DOM == "yes" )
		{
		if( $filternum++ > 0 )
			$whereclause .= " and ";
		$whereclause .= "ss_dom = " . $filter_DOM;
		}

	if( $use_DOW == "yes" )
		{
		if( $filternum++ > 0 )
			$whereclause .= " and ";
		$whereclause .= "ss_dow = " . $filter_DOW;
		}

	if( $use_se == "yes" )
		{
		if( $filternum++ > 0 )
			$whereclause .= " and ";
		$whereclause .= "sr_search_engine = \"" . $filter_se . "\"";
		$do_join = true;
		}

	if( $use_tu == "yes" )
		{
		if( $filternum++ > 0 )
			$whereclause .= " and ";
		$whereclause .= "sr_target_url = \"" . $filter_tu . "\"";
		$do_join = true;
		}

	if( $use_keywords == "yes" )
		{
		if( $filternum++ > 0 )
			$whereclause .= " and ";
		$whereclause .= "sr_keywords = \"" . $filter_keywords . "\"";
		$do_join = true;
		}

	if( $use_from == "yes" )
		{
		if( $filternum++ > 0 )
			$whereclause .= " and ";
		$whereclause .= "ss_date >= \"" . $FromYear . "-" . $FromMonth . "-" . $FromDay . "\"";
		}

	if( $use_to == "yes" )
		{
		$use_detail_table++;
		if( $filternum++ > 0 )
			$whereclause .= " and ";
		$whereclause .= "ss_date <= \"" . $ToYear . "-" . $ToMonth . "-" . $ToDay . "\"";
		}

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
			if( $res['so_join'] > 0 )
				$do_join = true;
			}

		}

	$nsort = $cols;
	$Cols[$cols] = $includecolsRes['in_formula'];
	$ColumnName[$cols] = $includecolsRes['in_label'];
	$PrintStatement[$cols] = $includecolsRes['so_print_statement'];
	$TotalColumn[$cols] = $includecolsRes['in_total'];
	$cols++;
	if( $includecolsRes['in_join'] > 0 )
		$do_join = true;

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
			if( $res['in_join'] > 0 )
				$do_join = true;
			}
	}

	$final_tablename = "sales_summary";

	$query = "select ".$Cols[0];
	for( $i = 1; $i < $nsort; $i++ )
		$query .= ", ".$Cols[$i];
	for( ; $i < $cols; $i++ )
		$query .= ", ".$Cols[$i];

	if( $do_join )
		$query .= " from ".$final_tablename." left join se_rank on sr_date = ss_date left join search_engine on se_search_engine = sr_search_engine left join target_url on tu = sr_target_url";
	else
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
		echo "Error selecting from traffic -- " . mysql_error();
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
