<?php
	include( "session.php" );


	$border = 1;
	$th = false;
	$subs = array(
		'Only_this_country' => array(NULL => 'Any'),
		'not_this_country' => array(NULL => ''),
		'First_Name_Match' => array( '.*' => 'Any' ),
		'Last_Name_Match' => array( '.*' => 'Any' ),
		);

	$result = mysql_query(
	"select si_name as Site, po_id as Rule, CnTo.cn_name as Only_this_country, CnFrom.cn_name as not_this_country, po_firstname_regex as First_Name_Match, po_lastname_regex as Last_Name_Match, cct_name as Card, (pg_limit-pg_accumulation) as AccumulationLeft, pg_name as Goes_To from payment_gateways join payment_gateway_options on pg_id = po_pg_id join configured_sites on si_id = po_site join credit_card_types on cct_id = po_card_type left join countries as CnTo on CnTo.cn_id = po_restrict_to_country left join countries as CnFrom on CnFrom.cn_id = po_restrict_from_country
                                        where po_active = true
                                         and po_restrict_to_person = false
                                         order by po_preference, (pg_limit-pg_accumulation)"
		);

	if (!$result)
		{
		echo "no results available!<br/>";
		return;
		}

	if( $border )
		echo "<table border='$border'>";
	else
		echo "<table>";
	while($row = mysql_fetch_assoc($result)) 
		{
		if( !$th )
			{
			echo "<tr>";
			foreach( $row as $index => $val )
				echo "<th>$index</th>";
			echo "</tr>";
			$th = true;
			}
		echo "<tr>";
		foreach( $row as $index => $val )
			if( strstr( $index, "\$" ) )
				echo "<td>".number_format( $val, 2 )."</td>";
			else
				if( array_key_exists( $index, $subs ) && array_key_exists( $val, $subs[$index] ) )
					echo "<td><span style='font-weight:bold;'>{$subs[$index][$val]}</span></td>";
				else
					echo "<td>$val</td>";
		echo "</tr>";
		}
	echo "</table>";

	mysql_free_result($result);
?>
