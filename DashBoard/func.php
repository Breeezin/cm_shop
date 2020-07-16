<?php


function display_query( $query, $border = NULL, $subs = array() )
	{
	$th = false;

	$result = mysql_query($query);
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
				if( array_key_exists($val, $subs) && $subs[$val] )
					echo "<td><span style='font-weight:bold;'>$subs[$val]</span></td>";
				else
					echo "<td>$val</td>";
		echo "</tr>";
		}
	echo "</table>";

	mysql_free_result($result);

	return;
	}
?>
