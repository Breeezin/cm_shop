<?php
	$results = array();
	$Title = "Accounting Summary Non US Orders";
    require_once('session.php');
    echo "<style type=\"text/css\">@import url(\"/DashBoard/bigger.css\");</style>";
	echo "</head>";

	$NoUSA = true;

function output_hours( $seconds )
	{
	$remain = $seconds;
	$h = (int) ($remain / 60 / 60 );
	$remain -= $h * 60 * 60;
	$m = (int) ($remain / 60);
	$remain -= $s * 60;
	$s = (int) $remain;

	return sprintf( "%02d:%02d", $h, $d );
	}

function output_header( )
	{
	echo "<th>orders</th>";
	echo "<th>&euro; sales</th>";
	echo "<th>&euro; shipping</th>";
	echo "<th>reship boxes</th>";
	echo "<th>&euro; reship value</th>";
	echo "<th>% reship</th>";
	echo "<th>&euro; refund value</th>";
	echo "<th>% refund</th>";
//	echo "<th>&euro; cm</th>";
//	echo "<th>% cm</th>";
	echo "<th>&euro; CM</th>";
	echo "<th>&euro; box cost</th>";
	echo "<th>% Margin</th>";
	}

function output_row( $row )
	{
	echo "<td>".$row['orders']."</td>";
	echo "<td align='right'>".number_format($row['sales'], 2, '.', ',')."</td>";
	echo "<td align='right'>".number_format($row['shipping'], 2, '.', ',')."</td>";
	echo "<td align='right'>".$row['reship_boxes']."</td>";
	echo "<td align='right'>".number_format($row['reship_value'], 2, '.', ',')."</td>";
	if( $row['sales'] > 0 )
		echo "<td align='right'>".number_format($row['reship_value']*100/$row['sales'], 1)."</td>";
	else
		echo "<td align='right'></td>";
	echo "<td align='right'>".number_format($row['refund_value'], 2, '.', ',')."</td>";
	if( $row['sales'] > 0 )
		echo "<td align='right'>".number_format($row['refund_value']*100/$row['sales'], 1)."</td>";
	else
		echo "<td align='right'></td>";
//	echo "<td align='right'>".number_format($row['cm'], 2, '.', ',')."</td>";
//	echo "<td align='right'>".number_format($row['cm_percent'], 1)."</td>";
	echo "<td align='right'>".number_format($row['profit'], 2, '.', ',')."</td>";
	echo "<td align='right'>".number_format($row['sales']-$row['profit'], 2, '.', ',')."</td>";
	echo "<td align='right'>".number_format($row['margin_percent'], 1)."</td>";
	}

	$first_year = 2004;
	$this_year = date( 'Y' );
	$this_month = date( 'm' );
	$this_day = date( 'd' );
	$this_script = 'summary_non_us.php';
	$now = time(NULL);
	echo "Date/Time is ".strftime( "%d / %m / %Y %H:%M", $now )."<br/>";

	if( array_key_exists( 'year', $_GET ) )
		$want_year = (int) $_GET['year'];

	if( array_key_exists( 'month', $_GET ) )
		$want_month = (int) $_GET['month'];

	if( IsSet( $want_year ) and !IsSet( $want_month ) )		// show all months for want_year
		echo "<h2>NON US Orders, All months in year $want_year</h2><br/>";
	else
		if( IsSet( $want_year ) and IsSet( $want_month ) )		// show all days for this month and year
			echo "<h2>NON US Orders, All days in $want_month / $want_year</h2><br/>";
		else
			{
			if( $NoUSA )
				$result = mysql_query("select as_month, sum(as_num_orders) as orders, sum(as_sales) as sales, sum(as_shipping_value) as shipping, sum(as_reship_value) as reship_value, sum(as_reship_boxes) as reship_boxes, sum(as_refund_value) as refund_value, sum(as_cm_value) as cm, sum(as_cm_value)*100/sum(as_sales) as cm_percent, sum(as_profit) as profit, sum(as_profit)*100/sum(as_sales) as margin_percent from account_summary join countries on as_country = cn_id where as_year = $want_year and cn_id != 840 group by as_month order by as_month asc" );
			else
				$result = mysql_query("select as_month, sum(as_num_orders) as orders, sum(as_sales) as sales, sum(as_shipping_value) as shipping, sum(as_reship_value) as reship_value, sum(as_reship_boxes) as reship_boxes, sum(as_refund_value) as refund_value, sum(as_cm_value) as cm, sum(as_cm_value)*100/sum(as_sales) as cm_percent, sum(as_profit) as profit, sum(as_profit)*100/sum(as_sales) as margin_percent from account_summary join countries on as_country = cn_id where as_year = $want_year group by as_month order by as_month asc" );
			}

	echo "<table border='1'>";
	echo "<tr>";
	echo "<th>period</th>";
	output_header( );
	echo "</tr>";
	if( IsSet( $want_year ) and !IsSet( $want_month ) )		// show all months for want_year
		{
		if( $NoUSA )
			$result = mysql_query("select as_month, sum(as_num_orders) as orders, sum(as_sales) as sales, sum(as_shipping_value) as shipping, sum(as_reship_value) as reship_value, sum(as_reship_boxes) as reship_boxes, sum(as_refund_value) as refund_value, sum(as_cm_value) as cm, sum(as_cm_value)*100/sum(as_sales) as cm_percent , sum(as_profit) as profit, sum(as_profit)*100/sum(as_sales) as margin_percent from account_summary join countries on as_country = cn_id where as_year = $want_year and cn_id != 840 group by as_month order by as_month asc" );
		else
			$result = mysql_query("select as_month, sum(as_num_orders) as orders, sum(as_sales) as sales, sum(as_shipping_value) as shipping, sum(as_reship_value) as reship_value, sum(as_reship_boxes) as reship_boxes, sum(as_refund_value) as refund_value, sum(as_cm_value) as cm, sum(as_cm_value)*100/sum(as_sales) as cm_percent , sum(as_profit) as profit, sum(as_profit)*100/sum(as_sales) as margin_percent from account_summary join countries on as_country = cn_id where as_year = $want_year group by as_month order by as_month asc" );
		if ($result)
			{
			while( $row = mysql_fetch_assoc($result) )
				{
				echo "<tr>";
				echo "<td><a href='$this_script?year=$want_year&month={$row['as_month']}'>{$row['as_month']} / $want_year</a></td>";
				output_row( $row );
				echo "</tr>";
				}

			mysql_free_result($result);
			}
		}
	else
		if( IsSet( $want_year ) and IsSet( $want_month ) )		// show all days for this month and year
			{
			if( $NoUSA )
				$result = mysql_query("select as_day, sum(as_num_orders) as orders, sum(as_sales) as sales, sum(as_shipping_value) as shipping, sum(as_reship_value) as reship_value, sum(as_reship_boxes) as reship_boxes, sum(as_refund_value) as refund_value, sum(as_cm_value) as cm, sum(as_cm_value)*100/sum(as_sales) as cm_percent, sum(as_profit) as profit, sum(as_profit)*100/sum(as_sales) as margin_percent from account_summary join countries on as_country = cn_id where as_year = $want_year and as_month = $want_month and cn_id != 840 group by as_day order by as_day asc" );
			else
				$result = mysql_query("select as_day, sum(as_num_orders) as orders, sum(as_sales) as sales, sum(as_shipping_value) as shipping, sum(as_reship_value) as reship_value, sum(as_reship_boxes) as reship_boxes, sum(as_refund_value) as refund_value, sum(as_cm_value) as cm, sum(as_cm_value)*100/sum(as_sales) as cm_percent, sum(as_profit) as profit, sum(as_profit)*100/sum(as_sales) as margin_percent from account_summary join countries on as_country = cn_id where as_year = $want_year and as_month = $want_month group by as_day order by as_day asc" );
			if ($result)
				{
				while( $row = mysql_fetch_assoc($result) )
					{
					echo "<tr>";
					echo "<td>{$row['as_day']} / $want_month / $want_year</td>";
					output_row( $row );
					echo "</tr>";
					}

				mysql_free_result($result);
				}
			}
		else
			{

			echo "<br/><br/><h2>Processed Orders NON US only</h2><br/>";
			if( $NoUSA )
				$year_result = mysql_query("select as_year, sum(as_num_orders) as orders, sum(as_sales) as sales, sum(as_shipping_value) as shipping, sum(as_reship_value) as reship_value, sum(as_reship_boxes) as reship_boxes, sum(as_refund_value) as refund_value, sum(as_cm_value) as cm, sum(as_cm_value)*100/sum(as_sales) as cm_percent, sum(as_profit) as profit, sum(as_profit)*100/sum(as_sales) as margin_percent  from account_summary join countries on as_country = cn_id where as_year >= $first_year and as_year <= $this_year and cn_id != 840 group by as_year order by as_year, cn_id" );
			else
				$year_result = mysql_query("select as_year, sum(as_num_orders) as orders, sum(as_sales) as sales, sum(as_shipping_value) as shipping, sum(as_reship_value) as reship_value, sum(as_reship_boxes) as reship_boxes, sum(as_refund_value) as refund_value, sum(as_cm_value) as cm, sum(as_cm_value)*100/sum(as_sales) as cm_percent, sum(as_profit) as profit, sum(as_profit)*100/sum(as_sales) as margin_percent  from account_summary join countries on as_country = cn_id where as_year >= $first_year and as_year <= $this_year group by as_year order by as_year, cn_id" );
			if ($year_result)
				{

				while( $row = mysql_fetch_assoc($year_result) )
					{
					echo "<tr>";
					echo "<td><a href='$this_script?year={$row['as_year']}'>{$row['as_year']}</a></td>";
					output_row( $row );
					echo "</tr>";
					}

				mysql_free_result($year_result);
				}

			if( $NoUSA )
				$month_result = mysql_query("select sum(as_num_orders) as orders, sum(as_sales) as sales, sum(as_shipping_value) as shipping, sum(as_reship_value) as reship_value, sum(as_reship_boxes) as reship_boxes, sum(as_refund_value) as refund_value, sum(as_cm_value) as cm, sum(as_cm_value)*100/sum(as_sales), sum(as_profit) as profit, sum(as_profit)*100/sum(as_sales) as margin_percent from account_summary join countries on as_country = cn_id where as_year = $this_year and as_month = $this_month and cn_id != 840 order by cn_id" );
			else
				$month_result = mysql_query("select sum(as_num_orders) as orders, sum(as_sales) as sales, sum(as_shipping_value) as shipping, sum(as_reship_value) as reship_value, sum(as_reship_boxes) as reship_boxes, sum(as_refund_value) as refund_value, sum(as_cm_value) as cm, sum(as_cm_value)*100/sum(as_sales), sum(as_profit) as profit, sum(as_profit)*100/sum(as_sales) as margin_percent from account_summary join countries on as_country = cn_id where as_year = $this_year and as_month = $this_month order by cn_id" );

			if ($month_result)
				{
				while( $row = mysql_fetch_assoc($month_result) )
					{
					echo "<tr>";
					echo "<td><a href='$this_script?year=$this_year&month=$this_month'>This Month</a></td>";
					output_row( $row );
					echo "</tr>";
					}

				mysql_free_result($month_result);
				}
			else
				{
				echo "<tr>";
				echo "<td><a href='$this_script?year=$this_year&month=$this_month'>This Month</a></td>";
				echo "</tr>";
				}

			if( $NoUSA )
				$day_result = mysql_query("select sum(as_num_orders) as orders, sum(as_sales) as sales, sum(as_shipping_value) as shipping, sum(as_reship_value) as reship_value, sum(as_reship_boxes) as reship_boxes, sum(as_refund_value) as refund_value, sum(as_cm_value) as cm, sum(as_cm_value)*100/sum(as_sales) as cm_percent, sum(as_profit) as profit, sum(as_profit)*100/sum(as_sales) as margin_percent  from account_summary join countries on as_country = cn_id where as_year = $this_year and as_month = $this_month and as_day = $this_day and cn_id != 840 order by cn_id" );
			else
				$day_result = mysql_query("select sum(as_num_orders) as orders, sum(as_sales) as sales, sum(as_shipping_value) as shipping, sum(as_reship_value) as reship_value, sum(as_reship_boxes) as reship_boxes, sum(as_refund_value) as refund_value, sum(as_cm_value) as cm, sum(as_cm_value)*100/sum(as_sales) as cm_percent, sum(as_profit) as profit, sum(as_profit)*100/sum(as_sales) as margin_percent  from account_summary join countries on as_country = cn_id where as_year = $this_year and as_month = $this_month and as_day = $this_day order by cn_id" );

			if ($day_result)
				{
				while( $row = mysql_fetch_assoc($day_result) )
					{
					echo "<tr>";
					echo "<td>This Day</td>";
					output_row( $row );
					echo "</tr>";
					}

				mysql_free_result($day_result);
				}


			}

	echo "</table>";
	echo "</html>";
?>
