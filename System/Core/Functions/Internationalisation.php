<?php

	$GLOBALS['exchangeRates'] = array();
	$GLOBALS['EUCountries'] = array(
						"BE",
						"BG",
						"CY",
						"CZ",
						"DK",
						"EE",
						"FI",
						"FR",
						"DE",
						"GR",
						"HU",
						"IE",
						"IT",
						"LV",
						"LT",
						"LU",
						"MT",
						"NL",
						"PL",
						"PT",
						"RO",
						"SI",
						"SK",
						"ES",
						"SE",
						"GB"
						);

	
	
	
	
	function ss_getExchangeRate($source, $dest) {
		
		// src and dest should be currency three letters not country three code

		// Grab from cache if possible
		if (array_key_exists($source.'_'.$dest,$GLOBALS['exchangeRates'])) {
			return $GLOBALS['exchangeRates'][$source.'_'.$dest];	
		}
		if ($source == $dest) {
			return 1;
		}
		timerStart('Get Exchange Rate');
		
		startTransaction('commonDB');
		
		// check the shared db for the rate
		$Q_Rate = $GLOBALS['commonDB']->query("
			SELECT * FROM exchange_rate
			WHERE er_source = '".escape($source)."' AND
				er_destination = '".escape($dest)."'
		");
		$result = null;
		$expiredResult = null;
		if ($Q_Rate->numRows()) {
			// Rate is already in the db. just fetch the row and grab the rate
			$rate = $Q_Rate->fetchRow();
			
			$result = $rate['er_rate'];
			if( $rate['ForceRate'] !== NULL )
				$result = $rate['ForceRate'];

			// If the rate is older than one day then grab a new one
/*			if ($rate['LastUpdated'] > date("Y-m-d H:i:s",time()-86400)) {
				$result = $rate['Rate'];
			} else {
				$expiredResult = $rate['Rate'];	
			}*/
		}
		
		if ($result == null and $expiredResult != null) {
			//print('using expired result');
			$result = $expiredResult;	
		}
		
		commit('commonDB');

		// Cache the result 
		if ($result != null) {
			$GLOBALS['exchangeRates'][$source.'_'.$dest] = $result;
		}
		
		timerFinish('Get Exchange Rate');

		if( !$result )
		{
			$teQ = $GLOBALS['commonDB']->query("SELECT * FROM exchange_rate WHERE er_destination = '".escape($source)."' AND er_source = '".escape($dest)."'");
			if( $teQ->numRows() )
				if( $rate = $teQ->fetchRow() )
					$result = 1/$rate['er_rate'];
		}

		if( !$result )
		{
			$teQ = $GLOBALS['commonDB']->query("SELECT * FROM exchange_rate WHERE er_source = '".escape($source)."' AND er_destination = 'EUR'");
			if( $teQ->numRows() )
				if( $rate = $teQ->fetchRow() )
				{
					$to_EUR = $rate['er_rate'];
					$feQ =  $GLOBALS['commonDB']->query("SELECT * FROM exchange_rate WHERE er_source = 'EUR' AND er_destination = '".escape($dest)."'" );
					if( $feQ->numRows() )
						if( $rate = $feQ->fetchRow() )
						{
							$from_EUR = $rate['er_rate'];
							if( $to_EUR && $from_EUR )
								$result = $to_EUR * $from_EUR;
						}
				}
		}

		if( !$result )
		{
			$teQ = $GLOBALS['commonDB']->query("SELECT * FROM exchange_rate WHERE er_source = '".escape($source)."' AND er_destination = 'USD'");
			if( $teQ->numRows() )
				if( $rate = $teQ->fetchRow() )
				{
					$to_USD = $rate['er_rate'];
					$feQ =  $GLOBALS['commonDB']->query("SELECT * FROM exchange_rate WHERE er_source = 'USD' AND er_destination = '".escape($dest)."'" );
					if( $feQ->numRows() )
						if( $rate = $feQ->fetchRow() )
						{
							$from_USD = $rate['er_rate'];
							if( $to_USD && $from_USD )
								$result = $to_USD * $from_USD;
						}
				}
		}

		if( !$result )
		{
			$teQ = $GLOBALS['commonDB']->query("SELECT * FROM exchange_rate WHERE er_source = '".escape($source)."' AND er_destination = 'BTC'");
			if( $teQ->numRows() )
				if( $rate = $teQ->fetchRow() )
				{
					$to_BTC = $rate['er_rate'];
					$feQ =  $GLOBALS['commonDB']->query("SELECT * FROM exchange_rate WHERE er_source = 'BTC' AND er_destination = '".escape($dest)."'" );
					if( $feQ->numRows() )
						if( $rate = $feQ->fetchRow() )
						{
							$from_BTC = $rate['er_rate'];
							if( $to_BTC && $from_BTC )
								$result = $to_BTC * $from_BTC;
						}
				}
		}

  		return $result;
	}

	function ss_countryISEU($country = null)
	{
		return in_array( $country, $GLOBALS['EUCountries'] );
	}

	function ss_getCountry($ipaddress = null, $return = 'cn_three_code') {
		// If null then use the remote ip address
		$countryCode = getField("SELECT $return FROM countries WHERE cn_id = 840");				
		if ($ipaddress === null) {
		    if (array_key_exists("HTTP_X_FORWARDED_FOR",$_SERVER)) { 
		        $ipaddress = $_SERVER["HTTP_X_FORWARDED_FOR"];
    		} else { 
        		$ipaddress = $_SERVER["REMOTE_ADDR"];
    		}
		}
		
		$addressParts = explode(".",$ipaddress);
		if (count($addressParts) == 4) {
			$addressValue = $addressParts[0]*(256*256*256)+
							$addressParts[1]*(256*256)+
							$addressParts[2]*(256)+
							$addressParts[3];
			
			$Q_Country = $GLOBALS['commonDB']->query("
				SELECT IPCoCoThreeCode AS cn_three_code, IPCoCoName AS cn_name, IPCoCoTwoCode  AS cn_two_code 
				FROM IPCountries INNER JOIN IPCountryCountries ON IPCountries.IPCoCountryLink = IPCountryCountries.IPCoCoID
				WHERE $addressValue BETWEEN IPCoFromIP AND IPCoToIP
			");
			
			if ($Q_Country->numRows()) {
				$row = $Q_Country->fetchRow();
				$countryCode = $row[$return];				
			}
		}
		
		return $countryCode;
	}	
	
	function ss_getCountryID($ipaddress = null) {
		$Q_Country = query("	
			SELECT * FROM countries
			WHERE cn_three_code LIKE '".ss_getCountry($ipaddress)."'
		");
		if ($Q_Country->numRows()) {
			$row = $Q_Country->fetchRow();
			return $row['cn_id'];
		}
		return null;
	}

?>
