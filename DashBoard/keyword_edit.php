<?php
	$Title = "Manage Search Engine Keywords";
    require_once('session.php');
    echo "<style type=\"text/css\">@import url(\"/DashBoard/bigger.css\");</style>";

	import_request_variables('G');
	import_request_variables('P');

	$query = "";
	if( IsSet( $action ) && ( $action == "insert" ) )
		{
		$sum = 0;
		for( $i = 1; $i < 10; $i++ )
			if( IsSet( ${'rk_keywords'.$i} ) && strlen(  ${'rk_keywords'.$i} ) > 0
					&& IsSet( ${'rk_weight'.$i} ) && ${'rk_weight'.$i} > 0 )
				$sum += ${'rk_weight'.$i};

		if( $sum != 100 )
			{
			echo "Percentages must add to 100<br>Try again<br>";
			}
		else
			{

			$query = "delete from rank_keywords where rk_date = CURDATE()";

			if( ($result = mysql_query( $query )) == false)
				{
				echo "Error removing old keywords -- " . mysql_error();
				exit;
				}

			// might as well nuke the seach engine results for this day too

			$query = "delete from se_rank where sr_date = CURDATE()";

			if( ($result = mysql_query( $query )) == false)
				{
				echo "Error removing old se results -- " . mysql_error();
				exit;
				}

			$inserted = 0;
			for( $i = 1; $i < 10; $i++ )
				{
				if( IsSet( ${'rk_keywords'.$i} ) && strlen(  ${'rk_keywords'.$i} ) > 0
						&& IsSet( ${'rk_weight'.$i} ) && ${'rk_weight'.$i} > 0 )
					{
					$query = "insert into rank_keywords (rk_date, rk_order, rk_keywords, rk_weight)"
							." values (CURDATE(), $i, '".${'rk_keywords'.$i}."', ".${'rk_weight'.$i}.")";

					if( ($result = mysql_query( $query )) == false)
						{
						echo "Error inserting keywords -- " . mysql_error();
						}
					else
						$inserted++;
					}
				}
			echo "inserted ".$inserted." keywords<br>";
			}
		}


	echo "<br>";
	echo $Title;
	echo "<br>";
//	echo $query;
	echo "<br>";

	$query = "select rk_date, rk_order, rk_keywords, rk_weight
					from rank_keywords 
					where rk_date <= CURDATE()
					order by rk_date desc, rk_order asc";

	if( ($result_rk = mysql_query( $query )) == false)
		{
		echo "Error selecting from keywords -- " . mysql_error();
		exit;
		}

	echo "<form ACTION=\"keyword_edit.php\"METHOD=POST NAME=\"ReportEdit\">";

	echo "<input type=\"hidden\" value=\"insert\" name=\"action\">";

	echo "<table BORDER>";
	echo "<tr>";
	echo "<td>Order</td>";
	echo "<td>Keyword</td>";
	echo "<td>Percentage Relevence</td>";
	echo "</tr>";

	$fdate = "";
	$order = 1;
	while ($row_rk = mysql_fetch_array($result_rk))
		{
		if( $fdate == "" )
			$fdate = $row_rk['rk_date'];
		else
			if( $fdate != $row_rk['rk_date'] )
				break;

		echo "<tr>";
		echo "<td>".$order."</td>";
		echo "<td><input name=\"rk_keywords".$order."\" value=\"".$row_rk['rk_keywords']."\"></td>";
		echo "<td><input name=\"rk_weight".$order."\" value=\"".$row_rk['rk_weight']."\"></td>";
		echo "</tr>";

		$order++;
		}

	while( $order < 10 )
		{
		echo "<tr>";
		echo "<td>".$order."</td>";
		echo "<td><input name=\"rk_keywords".$order."\" value=\"\"></td>";
		echo "<td><input name=\"rk_weight".$order."\" value=\"\"></td>";
		echo "</tr>";

		$order++;
		}

	echo "</table>";
	echo "<br>";
	echo "<input type=\"submit\" value=\"Update\" name=\"Submit\">";

	mysql_free_result($result_rk);

	echo "</form>";
	echo "<br>";

//	echo "<form ACTION=\"keyword_edit.php\" METHOD=POST>";
//	echo "<input type=\"hidden\" value=\"".$keyword."\" name=\"keyword\">";
//	echo "<input type=\"hidden\" value=\"remove\" name=\"action\">";
//	echo "<td><input type=\"submit\" value=\"Remove\" name=\"remove\"></td>";
//	echo "</form>";

	echo "<br>";
	echo "<a href=\"index.php\">Back</a>";

	exit;
?>
