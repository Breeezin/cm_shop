<?php
	//ss_DumpVarDie($Q_Currencies);
	
			requireOnceClass("CountriesAdministration");
	// Make a new users admin class
	$countryAdmin = new CountriesAdministration();
	
	startAdminPercentageBar('Importing countries...');

	$counter = 0;
	
	$not = "";
	while ($currency = $Q_Currencies->fetchRow()) {
		$Q_Country = query("SELECT * FROM countries WHERE cn_name LIKE '".$currency['cn_name']."'");
		
		if ($Q_Country->numRows()) {					
			$Q_Update = query("
				UPDATE countries 
				SET
					cn_currency = '".escape($currency['cn_currency'])."', 
					cn_currency_code = '".escape($currency['cn_currency_code'])."'
				WHERE 
					cn_name LIKE '".escape($currency['cn_name'])."'
			");
			
		} else {
			$not .= ss_HTMLEditFormat($currency['cn_name'])." was not loaded.\n<BR>";
		}
		
		$counter++;
		updateAdminPercentageBar($counter/$Q_Currencies->numRows());
	}
	/*
	while ($country = $Q_Countries->fetchRow()) {
		
		$Q_Insert = query("INSERT INTO countries 
			(cn_id, cn_name, cn_two_code, cn_three_code) 
			VALUES 
			({$country['cn_id']}, '".ss_HTMLEditFormat($country['cn_name'])."','{$country['cn_two_code']}','{$country['cn_three_code']}')
		");
		
		$counter++;
		updateAdminPercentageBar($counter/$Q_Countries->numRows());
	}*/
	
	stopAdminPercentageBar('nothing');
	print($not);
	
?>