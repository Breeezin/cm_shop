<?php

	
	$countries = array();
	$Q_Countries = query("SELECT * FROM countries");
	
	while($aCon = $Q_Countries->fetchRow()) {
		$countries[$aCon['cn_three_code']] = $aCon['cn_id'];
	}
	
	$stateNames = array();
	$stateCodes = array();
	$Q_States = query("SELECT * FROM country_states");
	
	while($ast = $Q_States->fetchRow()) {
		$stateNames[strtolower($ast['StCode'])] = $ast['sts_id'];
		$stateCodes[strtolower($ast['StName'])] = $ast['sts_id'];
	}
	
	
	
	// Field Names
	// Company = "Us0_5BD8" 
	// County = "Us0_5C5B"
	// Phone = "Us0_5BD9"
	// Firstly, we'll grab some users =b
	$Q_Users = query("
		SELECT 
			us_id, us_first_name, us_email, Us0_50A3 AS Country, us_0_50A4 AS State
	 	FROM users
		WHERE 
			us_0_50A4 IS NOT NULL OR Us0_50A3 IS NOT NULL		
	");
	

	while ($row = $Q_Users->fetchRow()) {
		$anyError = false;
		$country = '';
		if (strlen($row['Country'])) {
			$country = $countries[$row['Country']];
		}
		$type = 'text';
		if ($country == '124' or $country == '840') {
			$type = 'select';
		} 
		
		$state = '';		
		if ($type == 'select') {
			if (strlen($row['State'])) {
				if (strlen($country)) {
					$temp = strtolower($row['State']);
					if (array_key_exists($temp, $stateNames)) {
						$state = $stateNames[$temp];
					} elseif (array_key_exists($temp, $stateCodes)) {
						$state = $stateCodes[$temp];
					} else {
						print ("User ID - ".$row['us_id'].": no such state ($country, {$row['State']}) <BR>");		
						$anyError = true;
					}
				} else {
					print ("User ID - ".$row['us_id'].": no county but state <BR>");		
					$anyError = true;
				}
			} else {
				print ("User ID - ".$row['us_id'].": no state <BR>");	
				$anyError = true;
			}
		} else {
			$state = $row['State'];			
			
			if (!strlen($country)) { 
				print ("User ID - ".$row['us_id'].": no county<BR>");		
				$anyError = true;
			}
		}			
		$result = escape("$country&|&$type&|&$state");
		
		if (!$anyError) {
			/*
			$Q_update = query("
				UPDATE users 
				SET UsTemp = '$result'
				WHERE
					us_id = {$row['us_id']}
			");
			*/
			//print ("<strong>Update User {$row['us_id']}</strong> - ".$result."<BR>");
		} 
	}		
?>