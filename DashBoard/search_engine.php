<?php
	$Title = "Search Engine statistics";
    require_once('session.php');

	if( ($result_rk = mysql_query( 
				"select rk_date, rk_order, rk_keywords, rk_weight
					from rank_keywords 
					where rk_date <= CURDATE()
					order by rk_date desc, rk_order asc" )) == false)
		{
		echo "Error selecting from rank_keywords -- " . mysql_error();
		exit;
		}

	$tu = Array();
	$tu_n = 0;

	if( ($result_tu = mysql_query( "select * from target_url" )) == false)
		{
		echo "Error selecting from target_url -- " . mysql_error();
		exit;
		}

	while( $row_tu = mysql_fetch_array($result_tu))
		{
		$tu[] = $row_tu;
		$tu_n++;
		}

	mysql_free_result($result_tu);

	$se = Array();
	$se_n = 0;

	if( ($result_se = mysql_query( "select * from search_engine order by se_search_engine" )) == false)
		{
		echo "Error selecting from search_engine -- " . mysql_error();
		exit;
		}


	while( $row_se = mysql_fetch_array($result_se))
		{
		$se[] = $row_se;
		$se_n++;
		}

	mysql_free_result($result_se);

	echo "New results<br>";

	echo "<br>Search Engine Rankings<br>";
	echo "<table BORDER WIDTH=\"100%\" BGCOLOR=\"#FFFFFF\" NOSAVE >";
	echo "<tr>";
	echo "<td></td>";
	echo "<td></td>";
	for($i = 0; $i < $se_n; $i++ )
		echo "<td colspan=".$tu_n."><b>".$se[$i]['se_label']."</b></td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td></td>";
	echo "<td><b>Keywords</b></td>";
	for($i = 0; $i < $se_n; $i++ )
		for( $j = 0; $j < $tu_n; $j++ )
			echo "<td><b>".$tu[$j]['tu_label']."</b></td>";
	echo "</tr>";


	$fdate = "";

	while ($row_rk = mysql_fetch_array($result_rk))
		{
		if( $fdate == "" )
			$fdate = $row_rk['rk_date'];
		else
			if( $fdate != $row_rk['rk_date'] )
				break;

		echo "<tr>";
		echo "<td>".$row_rk['rk_order']."</td>";
		echo "<td>".$row_rk['rk_keywords']."</td>";
		$row_rk['rank'] = array();

		for($i = 0; $i < $se_n; $i++ )
			{
			$args = str_replace( ' ', $se[$i]['se_space_char'], $row_rk['rk_keywords'] );
			$url = $se[$i]['se_submit_url'].$args;
//            echo "<td>".$url."</td>";
			if( 1 )
				{
				$wp = curl_init( $url );
				curl_setopt( $wp, CURLOPT_RETURNTRANSFER, TRUE );
				curl_setopt( $wp, CURLOPT_COOKIE, $se[$i]['se_pref_cookie'] );
				$page = curl_exec( $wp );
	//            echo "<td>".strlen($page)."</td>";
	//            echo "<td>".$page."</td>";
				$pos = strpos( $page, $se[$i]['se_skip_to'] );
				if ($pos === false)
					$pos = 0;


				for( $j = 0; $j < $tu_n; $j++ )
					{
					$page2 = substr( $page, $pos );
					
					if( strpos( $page2, $tu[$j]['tu_target_url'] ) == false )
						echo "<td>Not in first 100</td>";
					else
						if( strpos( $page2, $se[$i]['se_delimit_tag'] ) === false )
							echo "<td>Config error, delimiter </td>";
						else
							{
							echo "<td>";
							$rank = 1;
							$first_del = strpos( $page2, $se[$i]['se_delimit_tag'] );
							$url_pos = strpos( $page2, $tu[$j]['tu_target_url'] );
		//                    echo "<br>".$first_del."<br>".$url_pos."<br>";
							while( $first_del < $url_pos )
								{
								$page2 = substr( $page2, strpos( $page2, $se[$i]['se_delimit_tag'] ) + strlen( $se[$i]['se_delimit_tag'] ) );
								$rank++;
								$first_del = strpos( $page2, $se[$i]['se_delimit_tag'] );
								$url_pos = strpos( $page2, $tu[$j]['tu_target_url'] );
		 //                       echo "<br>".$first_del."<br>".$url_pos."<br>";
								}
							$row_rk['rank'][$j] = $rank;
							echo $rank."</td>";
							}
					}
				}
			else
				{       // create random rank
				for( $j = 0; $j < $tu_n; $j++ )
					{
					$rank = rand(1, 100);
					$row_rk['rank'][$j] = $rank;
					echo "<td>".$rank."</td>";
					}
				}

			// record the result
			for( $j = 0; $j < $tu_n; $j++ )
				{
				if( !IsSet( $row_rk['rank'][$j] ) )
					$row_rk['rank'][$j] = 999;

				$insQ = "insert into se_rank 
					(sr_date, sr_keywords, sr_search_engine, sr_target_url, sr_weight, sr_rank)
					values 
					(CURDATE(), '".$row_rk['rk_keywords']."', ".$se[$i]['se_search_engine'].", "
					.$tu[$j]['tu'].", ".$row_rk['rk_weight'].", ".$row_rk['rank'][$j].")";

				echo $insQ."<br>";
				if( mysql_query( $insQ ) == false )
					{
					if( mysql_errno() == 1062 )     // dup key on ins
						{
						$insQ = "update se_rank set sr_rank = ".$row_rk['rank'][$j]
								." where sr_date = CURDATE() and sr_keywords = '".$row_rk['rk_keywords']
								."' and sr_search_engine = ".$se[$i]['se_search_engine']
								." and sr_target_url = ".$tu[$j]['tu'];
						if( mysql_query( $insQ ) == false )
							{
							echo "Error inserting se result-- " . mysql_error()." - #". mysql_errno();
							exit;
							}
						}
					else
						{
						echo "Error inserting se result-- " . mysql_error()." - #". mysql_errno();
						exit;
						}
					}
				}
			}

		echo "</tr>";
		}

	echo "</table>";

	mysql_free_result($result_rk);
?>
