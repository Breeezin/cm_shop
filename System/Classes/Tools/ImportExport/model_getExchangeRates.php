<?php 
	global $rates;
	// check the shared db for the rate
	// delete old rates 
	set_time_limit(0);

	ini_set("user_agent", "Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 5.1; GTB6; .NET CLR 1.1.4322; .NET CLR 2.0.50727)" );
	@apache_setenv('no-gzip', 1);
	@ini_set('zlib.output_compression', 0);
	@ini_set('implicit_flush', 1);
	for ($i = 0; $i < ob_get_level(); $i++) { ob_end_flush(); }
	ob_implicit_flush(1);

	if( 0 )
	if( $next_transaction = getField( "select max(tr_id) from transactions" ) )
	{
		echo "Random jump in transaction number from $next_transaction";
		$next_transaction += rand(3, 100);
		echo " to $next_transaction<br />";

		query( "insert into transactions (tr_id) values ($next_transaction)" );
		query( "delete from transactions where tr_id = $next_transaction" );
	}
	else
		echo "select max(tr_id) from transactions, failed<br />";

//	grab BTC exchange rates
//	$exc = file_get_contents( "http://blockchain.info/ticker" );
//	$ratesObj = json_decode( $exc );

//	foreach( $ratesObj as $c=>$rate )
//	{
//		echo 'BTC ->'.$c." is ".$rate->last." <br />";
//		update_exchange_rate( 'BTC', $c, $rate->last );
//		update_exchange_rate( $c, 'BTC', 1.0/$rate->last );
//	}

	$rates = array();

	function init_exchange_rate( )
	{
		global $rates;

		// init the matrix

		$crypto = [];

		$valid_eu_dest = array( 'USD', 'JPY', 'BGN', 'CZK', 'DKK', 'EEK', 'GBP', 'HUF', 'PLN', 'RON', 'SEK', 'CHF', 'NOK', 'HRK', 'RUB', 'TRY', 'AUD', 'BRL', 'CAD', 'CNY', 'HKD', 'IDR', 'INR', 'KRW', 'MXN', 'MYR', 'NZD', 'PHP', 'SGD', 'THB', 'ZAR', );
		//	grab BTC exchange rates

		foreach( $valid_eu_dest as $src )
			$rates[$src] = array();

		$rates['USD'] = array();
		$rates['BTC'] = array();

		//if( $cmcpage = file_get_contents( "https://coinmarketcap.com" ) )
		if( $cmcpage = file_get_contents( "https://coinmarketcap.com/all/views/all/" ) )
		{
			/*
			<span class="currency-symbol"><a href="/currencies/bitcoin/">BTC</a></span>
			<a class="currency-name-container" href="/currencies/bitcoin/">Bitcoin</a>
			 <a href="/currencies/bitcoin/#markets" class="price" data-usd="14103.5" data-btc="1.0" >$14103.50</a>

			                         <span class="currency-symbol"><a href="/currencies/ripple/">XRP</a></span>
                        <a class="currency-name-container" href="/currencies/ripple/">Ripple</a>
						  <a href="/currencies/ripple/#markets" class="price" data-usd="2.2872" data-btc="0.000164166" >$2.29</a>


						                          <span class="currency-symbol"><a href="/currencies/ethereum/">ETH</a></span>
                        <a class="currency-name-container" href="/currencies/ethereum/">Ethereum</a>
						<a href="/currencies/ethereum/#markets" class="price" data-usd="752.879" data-btc="0.0540386" >$752.88</a>

                        <span class="currency-symbol"><a href="/currencies/bitcoin-cash/">BCH</a></span>
                        <a class="currency-name-container" href="/currencies/bitcoin-cash/">Bitcoin Cash</a>
                        <a href="/currencies/bitcoin-cash/#markets" class="price" data-usd="2531.33" data-btc="0.181688" >$2531.33</a>
			&*/
			if( strlen( $cmcpage ) > 1000 )
			{
				ss_log_message( "coinmarketcap, page len ".strlen( $cmcpage ) );
				if( preg_match_all( "/<span class=\"currency-symbol visible-xs\">/", $cmcpage, $matches, PREG_OFFSET_CAPTURE ) )
				{
					echo "Matches ".count( $matches[0] )."<br/>";

					for( $i = 0; $i < count( $matches[0] ); $i++ )
					{
						$start = $matches[0][$i][1];
						if( $i == count( $matches[0] )-1 )
							$end = strlen( $cmcpage );
						else
							$end = $matches[0][$i+1][1];

						$chunk = str_replace( ["\n", "\r"], '', substr( $cmcpage, $start, $end - $start ) );

						// find in chunk, symbol and price
						$symbol = safe(preg_filter( "/<span class=\"currency-symbol visible-xs\"><a [^>]*>([^<]*).*/", '$1', $chunk ));
						$price = (double)(preg_filter( "/.*class=\"price\" data-usd=\"([^\"]*)\".*/", '$1', $chunk ));

						echo $symbol.'-> USD is '.$price.' <br />';
						$crypto[] = $symbol;
						if( $price > 0 )
						{
							$rates[$symbol]['USD'] = $price;
							$rates['USD'][$symbol] = 1.0/$price;
						}
					}
				}
				else
					echo "No matches";
			}

		}

		$exc = file_get_contents( "http://blockchain.info/ticker" );
		$btcRates = json_decode( $exc );
		foreach( $btcRates as $c=>$rate )
		{
			echo 'BTC ->'.$c." is ".$rate->last." <br />";
//			$rates['BTC'][$c] = $rate->last;
//			$rates[$c]['BTC'] = 1.0/$rate->last;
		}

		foreach( $valid_eu_dest as $eudest )
		{
			$ldest = strtolower( $eudest );
			$get_url = "https://www.ecb.europa.eu/rss/fxref-$ldest.html";

			$opts = array( 'http'=>array( 'method'=>"GET",
											'header'=>"Accept-language: en\r\n" .
											"Referer: https://www.ecb.europa.eu/home/html/rss.en.html\r\n"
						  ));

			$context = stream_context_create($opts);
			/*
			$ch = curl_init( );
			curl_setopt($ch, CURLOPT_URL, $get_url);
			curl_setopt($ch, CURLOPT_PROXY, 'http://127.0.0.1');
			curl_setopt($ch, CURLOPT_PROXYPORT, 8123);
			curl_setopt($ch, CURLOPT_REFERER, 'https://www.ecb.europa.eu/home/html/rss.en.html');
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_AUTOREFERER, true);
			curl_setopt($ch, CURLOPT_MAXREDIRS, 10);

			$page = curl_exec($ch);
			echo "retrieved ".strlen( $page )." bytes from $get_url<br/>";

			if( strlen( $page ) == 0 )
				echo "Tor retrieve failed, trying direct<br/>";
			*/
			{
				$page = file_get_contents( $get_url, false, $context );
				echo "retrieved ".strlen( $page )." bytes from URL $get_url<br/>";
			}

			if( strlen( $page ) == 0 )
			{
				echo "Unable to retrieve file, from $get_url";
				ss_log_message( "URGENT: Unable to get URL  $get_url" );
				die;
			}

			$num = '';
			$nre = '<title xml:lang="en">';
			$pos = mb_strpos( $page, $nre );
			if( $pos !== false )
			{
				$foo = substr( $page, $pos+mb_strlen( $nre ) );
				// echo "found at $pos in extracted '$foo'<br/>";
				$copying = false;
				for( $i = 0; $i < mb_strlen( $foo ); $i++ )
				{
					if( ( $foo[$i] >= '0' && $foo[$i] <= '9' ) || ($foo[$i] == '.') )
					{
						if( !$copying )
							$copying = true;
						$num .= $foo[$i];
					}
					else
					{
						if( $copying )
							break;
					}
				}
			}
			else
			{
				echo "unable to find '".htmlentities($nre)."' in $get_url";
				echo $page;
				ss_log_message( "ERROR: page format changed, token missing" );
				die;
			}

			if( strlen( $num ) && ($num > 0) )
				$rates['EUR'][$eudest] = (double)$num;
			else
			{
				echo "found invalid token $num as rate";
				ss_log_message( "ERROR: found invalid token $num as rate" );
			}
		}

		if( array_key_exists( 'USD', $rates['EUR'] ) )
		{
			$usd_rate = $rates['EUR']['USD'];
			echo "usd rate = ".$usd_rate;
			// fabricate USD source

			foreach( $valid_eu_dest as $eudest )
			{
				if( array_key_exists( $eudest, $rates['EUR'] ) )
					if( $eudest == 'USD' )
						$rates['USD']['EUR'] = 1 / $usd_rate;
					else
						$rates['USD'][$eudest] = $rates['EUR'][$eudest] / $usd_rate;
			}

			foreach( $crypto as $dest )
			{
				if( array_key_exists( $dest, $rates['USD'] ) )
				{
					$rates['EUR'][$dest] = $rates['USD'][$dest]*$usd_rate;
					$rates[$dest]['EUR'] = 1/$rates['EUR'][$dest];
					ss_log_message( "fabicated EUR -> $dest is ".$rates['EUR'][$dest] );
				}
				else
					ss_log_message( "missing $rates[USD][$dest]" );
					
			}
		}
		ss_log_message_r(  'Log:'.__FILE__.':'.__LINE__, $rates );
	}


	function update_exchange_rate( $source, $dest, $rate )
	{
		$GLOBALS['commonDB']->query("replace into exchange_rate set er_rate = $rate, er_stale = false, er_source = '$source', er_destination = '$dest'" );
	}

	echo "Getting New Exchange Rates<br>";

	init_exchange_rate();

    $GLOBALS['commonDB']->query('update exchange_rate set er_stale = true' );

	foreach( $rates as $src => $destArray )
		foreach( $destArray as $dest => $rate )
			update_exchange_rate( $src, $dest, $rate );
	
	die;

		$temp = new Request("Email.Send",array(
			'from'		=>	'bugreports@admin.com',
			'to'		=>	array('im@admin.com'),
			'subject'	=>	'Exchange Rates Scraper is down!',
			'text'		=>	'Please change the exchange rate scraper.  Exchange rates have not updated for more than 2 days.',
		));
?>
