<?php

function extract_rate( $from, $to, $arr )
	{
	if( $from == $to )
		return 1;

	$index = $from.'_'.$to;
	if( array_key_exists( $index, $arr ) )
		return $arr[$index];

	return 0;
	}

	$results = array();
	$Title = "Processor Take";
    require_once('session.php');
    echo "<style type=\"text/css\">@import url(\"/DashBoard/bigger.css\");</style>";

	echo "</head>";

	echo "<table border='1'>";

	$year = date( 'Y' );
	if( array_key_exists( 'year', $_GET) )
		$year = $_GET['year'];

	$month = date( 'm' );
	if( array_key_exists( 'month', $_GET) )
		$month = $_GET['month'];

	$from = sprintf( '%04d', $year ) . '-' . sprintf( '%02d', $month ) . '-01';

	echo "<h2>Processor Take for month starting $from</h2>";

    $q = "select pg_name, DATE(tr_timestamp) as TrDate, OERValues, tr_currency_code, sum(tr_total-tr_processor_cost) as tr_total
		from transactions join shopsystem_orders on tr_id = or_tr_id
			join payment_gateways on tr_bank = pg_id
			left join OldExchangeRates on OERID = tr_exchange_rate_index
	 where tr_completed > 0 and or_paid IS NOT NULL
	   and tr_timestamp >= '$from'
	   and tr_timestamp < '$from' + interval 30 day
	 group by pg_name, DATE(tr_timestamp), tr_exchange_rate_index, tr_currency_code
	 order by 2, 1";

	print($q);
	echo "<br/>";
	$result = mysql_query($q);

	$totalfoo = array();
	$foofoo = array();

	$show_currencies = array( 'USD', 'EUR' );
	$show_processors = array( 'Cybersource' => array( 'Cybersource USD', 'Cybersource AUD', 'Cybersource BRL', 'Cybersource EUR', 'Cybersource JPY', 'Cybersource NZD', 'Cybersource HKD', 'Cybersource CNY', 'Cybersource DKK', 'Cybersource KRW', 'Cybersource SGD', 'Cybersource SEK', 'Cybersource THB', 'Cybersource GBP'),
							'Deutsche Bank' => array( 'DeutscheBankVisa', 'DeutscheBankAmex' ),
							'Acceptance' => array( 'Acceptance' ),
							'BankaMarch' => array( 'BankaMarch', 'BankaMarch/Amex' ),
	);

	if ($result)
		{
		while( $row = mysql_fetch_assoc($result) )
			{
			if( !array_key_exists( $row['TrDate'], $foofoo ) )
				$foofoo[ $row['TrDate'] ] = array();

			foreach( $show_processors as $parent => $children )
				{
				if( in_array( $row['pg_name'], $children ) )
					{
					if( !array_key_exists( $parent, $totalfoo ) )
						$totalfoo[$parent] = array();

					if( !array_key_exists( $parent, $foofoo[ $row['TrDate']] ) )
						$foofoo[ $row['TrDate'] ][$parent] = array();

					$exr = unserialize( $row['OERValues'] );

					foreach( $show_currencies as $currency )
						{
						$val = $row['tr_total'] * extract_rate($row['tr_currency_code'], $currency, $exr );

						if( !array_key_exists( $currency, $foofoo[ $row['TrDate']][$parent] ) )
							$foofoo[ $row['TrDate']][$parent][$currency] = $val;
						else
							$foofoo[ $row['TrDate']][$parent][$currency] += $val;

						if( !array_key_exists( $currency, $totalfoo[$parent] ) )
							$totalfoo[$parent][$currency] = $val;
						else
							$totalfoo[$parent][$currency] += $val;

						}
					}
				}
			}

		mysql_free_result($result);

		foreach( $show_processors as $parent=>$children )
			{
			echo "<h1>$parent</h1><br/>";
			echo "<table border=1>\n";
			echo "<tr><th>Date</th><th>equiv USD</th><th>equiv EUR</th></tr>";
			foreach( $foofoo as $day => $dv )
				{
				$pv = $dv[$parent];
				echo "<tr>";
				echo "<td>$day</td>";
				$usd = number_format( $pv['USD'], 2 );
				$eur = number_format( $pv['EUR'], 2 );
				echo "<td>$usd</td>";
				echo "<td>$eur</td>";
				echo "</tr>";
				}

			$pv = $totalfoo[$parent];
			echo "<tr>";
			echo "<td>Total</td>";
			$usd = number_format( $pv['USD'], 2 );
			$eur = number_format( $pv['EUR'], 2 );
			echo "<td>$usd</td>";
			echo "<td>$eur</td>";
			echo "</tr>";

			echo "</table>";
			}
		}
	echo "</html>";
?>
