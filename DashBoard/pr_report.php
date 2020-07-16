<?php
	$Title = "Generated Report";
    require_once('session.php');
    echo "<style type=\"text/css\">@import url(\"/DashBoard/site.css\");</style>";

	$SiteVars = array();
	$Cols = array();
	$ColumnName = array();
	$TotalColumn = array();
	$PrintStatement = array();
	$hashval = '';
	$dtree = array( );

	function recordGraph( $thisSortVal, $name, $val, $pretty ) 
		{
		global $dtree;

		if( !array_key_exists( $name, $dtree ) )
			$dtree[$name] = array( 'name' => 'flare', 'children' => array() );

		$children = &$dtree[$name]['children'];

//		print "recordGraph( ";
//		print_r( $thisSortVal );
//		print ", $name, $val )\n";

		for( $i = 0; $i < count($thisSortVal) - 1; $i++ )
			{
			$found = false;

//			print " search for \$thisSortVal[$i] = {$thisSortVal[$i]} in \n";
//			print_r( $children );

			for( $j = 0; $j < count( $children ); $j++ )
				if( is_array( $children[$j] ) && array_key_exists( 'name', $children[$j] ) && !strcmp($children[$j]['name'], $thisSortVal[$i]) )
					{
					$found = true;
					$children = &$children[$j]['children'];		// descend
//					print " found, children now\n";
//					print_r( $children );
					break;
					}

			if( !$found )
				{
//				print "not found adding\n";
				$newChild = array();
				$children[] = array( 'name' => $thisSortVal[$i], 'children' => &$newChild );
				$children = &$newChild;
				}
			}

		// now $i indexes the last element in $thisSortVal and $children should be the leaf
		$children[] = array( 'name' => $thisSortVal[$i].' - '.$pretty, 'size' => $val );
//		print " children now\n";
//		print_r( $children );
//		print "\n<br/>";
		}

	function saveGraph( )
		{
		global $hashval, $dtree;

//		print_r( $dtree );

		foreach( $dtree as $name=>$encode )
			{
			$fname = 'graphs/'.hash( 'md5', $hashval.$name ).'.json';
			echo "Name $name, filename $fname<br />";
			if( !($fd = fopen( $fname, 'w' ) ) )
				echo "Unable to open file ".$fname."<br />";
			else
				{
				echo "Write file ".$fname."<br />";
				fwrite( $fd, json_encode( $encode ) );
				fclose( $fd );
				}
			}
		}

    function getInclude( $ind )
		{
		if( ($result = mysql_query( "select in_include, in_formula, in_label, in_join, in_total, in_print_statement from pr_includes where in_include = ".$ind )) == false)
			{
			echo "Error selecting from includes -- " . mysql_error();
			exit;
			}
		return mysql_fetch_array($result);
		}

    function getSort( $ind )
		{
		if( ($result = mysql_query( "select so_sort, so_formula, so_label, so_join, so_print_statement from pr_sorts where so_sort = ".$ind )) == false)
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

		$insQ = "insert into pr_reports (re_vars, re_label) values ('".addslashes(serialize( $_POST ))."', '".$new_report_name."')";
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

	echo "<br><a href=specify_pr_report.php?foo=bar";
	foreach( $_POST as $key=>$val )
		echo '&'.htmlentities($key).'='.htmlentities($val);
	echo ">Back</a>";

	echo "<br><b>Filter Settings</b>->";
	if( !IsSet( $use_year )
		&& !IsSet( $use_archived )
		&& !IsSet( $use_payment_gateway )
		&& !IsSet( $use_category )
		&& !IsSet( $use_vendor )
		&& !IsSet( $use_month )
		&& !IsSet( $use_DOM )
		&& !IsSet( $use_DOW )
		&& !IsSet( $use_WOY )
		&& !IsSet( $use_from )
		&& !IsSet( $use_to )
		&& !IsSet( $use_tu )
		&& !IsSet( $use_se ) )
		echo "Result set unfiltered";

	echo "Currency : ".$filter_currency.", ";
	if( IsSet( $use_archived ) )
		echo "Includes archived : yes, ";
	else
		echo "Includes archived : NO, ";
	if( IsSet( $use_payment_gateway ) )
		echo "Payment Gateway :".$filter_payment_gateway.", ";
	if( IsSet( $use_category ) )
		echo "Product Category :".$filter_category.", ";
	if( IsSet( $use_vendor ) )
		echo "vendor :".$filter_vendor.", ";
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
	//echo "<br><a href=report.php?Screen=4?"
	//	."use_year=yes&filter_year=20".date("y")
	//	."&use_month=yes&filter_month=".date("m")
	//	."&use_DOM&filter_DOM=".date("d").">Home</a> ";
	//echo "<a href=report.php?Screen=2>Archive</a> ";
//	echo "<br><a href=specify_pr_report.php>Search</a>";
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

	$whereclause = "or_card_denied IS NULL and or_cancelled IS NULL and or_deleted = 0 and tr_completed = 1 and ";

	if( $use_archived == "yes" )
		$whereclause .= "pr_deleted IS NULL";
	else
		$whereclause .= "pr_deleted IS NULL and or_archive_year IS NULL";

//	$filternum = 0;

	if( $filter_currency != 'TO_USD' )
		$whereclause .= " and op_currency_code = '$filter_currency'";

	if( $use_payment_gateway == "yes" )
		{
//		if( $filternum++ > 0 )
			$whereclause .= " and ";
		$whereclause .= "tr_bank  = " . $filter_payment_gateway;
		}

	if( $use_category == "yes" )
		{
//		if( $filternum++ > 0 )
			$whereclause .= " and ";
		$whereclause .= "pr_ca_id  = " . $filter_category;
		}

	if( $use_vendor == "yes" )
		{
//		if( $filternum++ > 0 )
			$whereclause .= " and ";
		$whereclause .= "pr_ve_id  = " . $filter_vendor;
		}

	if( $use_year == "yes" )
		{
//		if( $filternum++ > 0 )
			$whereclause .= " and ";
		$whereclause .= "EXTRACT( YEAR from or_recorded ) = " . $filter_year;
		}

	if( $use_month == "yes" )
		{
//		if( $filternum++ > 0 )
			$whereclause .= " and ";
		$whereclause .= "EXTRACT( MONTH from or_recorded ) = " . $filter_month;
		}

	if( $use_DOM == "yes" )
		{
//		if( $filternum++ > 0 )
			$whereclause .= " and ";
		$whereclause .= "EXTRACT( DAY from or_recorded ) = " . $filter_DOM;
		}

	if( $use_from == "yes" )
		{
//		if( $filternum++ > 0 )
			$whereclause .= " and ";
		$whereclause .= "or_recorded >= \"" . $FromYear . "-" . $FromMonth . "-" . $FromDay . "\"";
		}

	if( $use_to == "yes" )
		{
//		if( $filternum++ > 0 )
			$whereclause .= " and ";
		$whereclause .= "or_recorded <= \"" . $ToYear . "-" . $ToMonth . "-" . $ToDay . "\"";
		}

	$full = false;
	if( $sortcolsRes['so_join'] )
		$full = true;
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
			if( $res['so_join'] )
				$full = true;
			$Cols[$cols] = $res['so_formula'];
			$ColumnName[$cols] = $res['so_label'];
			$PrintStatement[$cols] = $res['so_print_statement'];
			$cols++;
			$sortcols .= ", ".$res['so_formula'];
			$sortorder .= ", ".$i;
			$tcols .= ", '~Total'";
			}
		}

	$hashval = $sortcols;

	$nsort = $cols;
	if( $includecolsRes['in_join'] )
		$full = true;
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
			if( $res['in_join'] )
				$full = true;
			$Cols[$cols] = $res['in_formula'];
			$ColumnName[$cols] = $res['in_label'];
			$PrintStatement[$cols] = $res['so_print_statement'];
			$includecols .= ", ".$res['in_formula'];
			$TotalColumn[$cols] = $res['in_total'];
			$cols++;
			}
	}


/*
	for( $i = 0; $i < $nsort; $i++ )
		if( strstr( $Cols[$i], "Or" ) )
			$full = true;
	for( ; $i < $cols; $i++ )		// is this report to be order based, or product based?  Look for the telltale marker.
		if( strstr( $Cols[$i], "OrPr" ) )
			$full = true;
	if( $full )
*/
/*		$final_tablename = "shopsystem_orders JOIN transactions on or_tr_id = tr_id JOIN shopsystem_order_products ON or_id = orpr_or_id JOIN shopsystem_products ON pr_id = orpr_pr_id JOIN shopsystem_product_extended_options ON pr_id = pro_pr_id LEFT JOIN countries on cn_id = or_country"; 	*/
		$final_tablename = "shopsystem_orders JOIN transactions on or_tr_id = tr_id LEFT JOIN payment_gateways on pg_id = tr_bank JOIN ordered_products ON or_id = op_or_id LEFT JOIN countries on cn_id = or_country join shopsystem_products on pr_id = op_pr_id join shopsystem_categories on pr_ca_id = ca_id"; 
	/*
	else
		$final_tablename = "shopsystem_products JOIN transactions on or_tr_id = tr_id LEFT JOIN payment_gateways on pg_id = tr_bank JOIN shopsystem_product_extended_options ON pr_id = pro_pr_id LEFT JOIN countries on cn_id = or_country"; 
		*/

	$query = "select ".$Cols[0];
	for( $i = 1; $i < $nsort; $i++ )
		$query .= ", ".$Cols[$i];
	for( ; $i < $cols; $i++ )
		$query .= ", ".$Cols[$i];

	$query .= " from ".$final_tablename;

	$hashval .= $whereclause;

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

	echo "PieCharts<br />";
	for( $i = 0; $i < $cols; $i++ )
		{
		if( $i >= $nsort )
			{
			echo "<a href='piechart.php?fname=graphs/".hash( 'md5', $hashval.$ColumnName[$i] ).".json&Name=".htmlentities($ColumnName[$i])."'>";
			echo $ColumnName[$i];
			echo "</a><br />";
			}
		}

	echo "<table BORDER COLS=".$cols."WIDTH=\"100%\" BGCOLOR=\"#FFFFFF\" NOSAVE >";

	for( $i = 0; $i < $cols; $i++ )
		if( !IsSet( $ColumnName[$i] ) )
			$ColumnName[$i] = $Cols[$i];

	echo "<tr>";
	for( $i = 0; $i < $cols; $i++ )
		{
		echo "<td><b>";
		echo $ColumnName[$i];
		echo "</b></td>";
		}
	echo "</tr>";

	$thisSortVal = array();
	while ($row = mysql_fetch_row($result))
		{
		echo "<tr>";
		for( $i = 0; $i < $cols; $i++ )
			{
			echo "<td>";

			ob_start();

			if( IsSet( $TotalColumn[$i] ) && strlen($TotalColumn[$i]) )
				{
				$TotalColumn[$i] += $row[$i];

				if( strlen( $PrintStatement[$i] ) > 0 )
					{
					$r = $row[$i];		//	assumption in these statements is that $r holds the print variable
					eval( $PrintStatement[$i] );
					}
				else
					if( $row[$i] - (int)$row[$i] )
						echo number_format( $row[$i], 2, '.', ',' );
					else
						echo number_format( $row[$i], 0, '.', ',' );
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

			$val = ob_get_contents();
			ob_end_clean();
			echo $val;

			echo "</td>";

			if( $i < $nsort )					// this is a sort column
				$thisSortVal[$i] = iconv( "ISO-8859-1", "UTF-8//TRANSLIT", $val );
			else								// this is a total column
				recordGraph( $thisSortVal, $ColumnName[$i], $row[$i], $val );
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

//	print_r( $TotalColumn );

	mysql_free_result($result);

	saveGraph();

	exit;

?>
