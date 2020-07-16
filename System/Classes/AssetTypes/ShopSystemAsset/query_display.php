<?php

	$this->asset =&	$asset;

    $defaultService = ss_optionExists('Shop Default Service');
    if ($defaultService) {
        $this->param('Service',$defaultService);
    }else if (ss_optionExists('Shop Skip Search')) {
		$this->param('Service','Engine');
	} else {
		$this->param('Service','Search');
	}
	$assetPath = ss_withoutPreceedingSlash($asset->getPath());
	$assetID = $asset->getID();
	
	// Always have a shop structure in the session
	ss_paramKey($_SESSION,'Shop',array());
	ss_paramKey($_SESSION['Shop'],'Basket',array());
	ss_paramKey($_SESSION['Shop']['Basket'],'Products',array());	
	ss_paramKey($_SESSION['Shop']['Basket'],'Total',0);	
	ss_paramKey($_SESSION['Shop'],'DiscountCode',null);
	
	// See if we need to try and log into the special categories
	if (array_key_exists('AccessCode',$this->ATTRIBUTES)) {
		$Q_RestrictedCats = query("
			SELECT ca_id FROM shopsystem_categories
			WHERE ca_password LIKE '".$this->ATTRIBUTES['AccessCode']."'
		");	
		if ($Q_RestrictedCats) {
			ss_paramKey($_SESSION,'CanViewCategory',array());
			while ($row = $Q_RestrictedCats->fetchRow()) {
				array_push($_SESSION['CanViewCategory'],$row['ca_id']);
			}
		}
		
	}
	
	$this->setDefaultFreight();
	
	// Try detect a country based on the user's IP address
	if (!array_key_exists('CurrencyCountry',$_SESSION['Shop'])) {
		$this->setCurrencyCountry(ss_getCountry());
		defaultGatewayOption( );
	}			

	// Try detect a country based on the user's IP address
	if (ss_optionExists('Shop Product Multi-Country Prices') !== false) {
		$multiCountries = ss_optionExists('Shop Product Multi-Country Prices');
		if (!array_key_exists('MultiCurrencyCountryDef',$_SESSION['Shop'])) {
			$tlc = ss_getCountry();
			
			// hard code to USA for fun :)
			//$tlc = 'ESP';
			
			// The countries in the list resolve to the "Euro" country
			if (ListFind("AUT,BEL,DEU,ESP,FIN,FRA,GRC,IRL,ITA,LUX,NLD,PRT,VAT",$tlc)) {
				$tlc = "EUR";	
			}

			foreach (ListToArray($multiCountries,':') as $currencyDef) {
				$code = ListFirst($currencyDef);
				if ($code == $tlc) {
					$def = ListToArray($currencyDef);
					$_SESSION['Shop']['MultiCurrencyCountryDef'] = array(
						'CountryCode'	=>	$def[0],
						'CurrencyCode'	=>	$def[1],
						'Symbol'		=>	$def[2],
						'Appears'		=>	$def[3],
						'MinimumOrder'	=>	$def[4],
					);
					break;	
				}	
			}
			
			// If still not found..
			if (!array_key_exists('MultiCurrencyCountryDef',$_SESSION['Shop'])) {
				// Default to the first one
				$def = ListToArray(ListFirst($multiCountries,':'));
				$_SESSION['Shop']['MultiCurrencyCountryDef'] = array(
					'CountryCode'	=>	$def[0],
					'CurrencyCode'	=>	$def[1],
					'Symbol'		=>	$def[2],
					'Appears'		=>	$def[3],
					'MinimumOrder'	=>	$def[4],
				);
			}		
		}
	}

	
	$rootFolder = str_replace('index.php','',$_SERVER['SCRIPT_FILENAME']);
	$customFolder = $rootFolder.'Custom/Classes/ShopSystemAsset';
	
	foreach(array('query','model','view') as $prefix) {
		$name = $prefix.'_'.strtolower($this->ATTRIBUTES['Service']).'.php';
		if (file_exists($customFolder.'/Services/'.$name)) {
			ss_log_message( "ShopSystem calling service $customFolder/Services/$name" );
			include($customFolder."/Services/".$name);
		} else if (file_exists(dirname(__FILE__).'/Services/'.$name)) {
			ss_log_message( "ShopSystem calling service Services/$name" );
			include("Services/".$name);
		}
	}
	

?>
