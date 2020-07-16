<?
	// $pr_id,$optionID = null,$type = 'HTML'

	$logging = false;
	$specialDescription = 'Sale';
	$priceMessage = '';

	$optionsSQL = '';
	if ($optionID !== null) {
		$optionsSQL = 'AND pro_id = '.$optionID;
	}
	/*foreach ($options as $parent => $value) {
		$optionsSQL .= ' AND PrExOpUUIDS LIKE \'%,'.escape($parent).'='.escape($value).'%\'';
	}*/
	
	$key = $pr_id.'_'.$optionID;

	$foobar = getRow( "select pr_name, pr_ve_id, pr_quote_shipping, pr_us_only, pr_restrict_special_to_gateway, pr_restrict_product_to_gateway, pr_specials_sales_zone from shopsystem_products where pr_id = $pr_id" );
	$pr_ve_id = $foobar['pr_ve_id'];
	$QuoteShipping = $foobar['pr_quote_shipping'];
	$UsOnly = ($foobar['pr_us_only'] == 'true');
	if( strlen( $foobar['pr_restrict_special_to_gateway'] ) )
	{
		$gwName = getField( "select pg_name from payment_gateways where pg_id = ".(int) $foobar['pr_restrict_special_to_gateway'] );
		$specialDescription = "$gwName only sale";
	}

	$forceNoSpecial = false;
	if( strlen( $foobar['pr_specials_sales_zone'] ) )
	{
		// which zones is the current country in?
		$zones = explode(',', $_SESSION['ForceCountry'][ 'cn_sales_zones' ] );
		if( !in_array( $foobar['pr_specials_sales_zone'], $zones ) )
			$forceNoSpecial = true;
	}



	if( strlen( $foobar['pr_restrict_product_to_gateway'] ) )
	{
		$gwName = getField( "select pg_name from payment_gateways where pg_id = ".(int) $foobar['pr_restrict_product_to_gateway'] );
		$priceMessage = "This product is only available to purchasers who use the $gwName payment method.\n";
	}
	
	// Cache the product prices queries for a given requrest

	$currency = $this->getDisplayCurrency();
	if( $logging )
	{
		ss_log_message( "Showing price of pr_id:$pr_id ".$foobar['pr_name'] );
		ss_log_message( 'Display currency' );
		ss_log_message_r(  'Log:'.__FILE__.':'.__LINE__, $currency );
	}

	if (!array_key_exists($key,$this->prices)) {
		$this->prices[$key] = getRow("
				SELECT
					MAX(pro_price) AS MaxPrice, MIN(pro_price) AS MinPrice,
					MAX(pro_special_price) AS MaxSpecialPrice, MIN(pro_special_price) AS MinSpecialPrice,
					MAX(pro_member_price) AS MaxMemberPrice, MIN(pro_member_price) AS MinMemberPrice,
					MAX(pro_rrp_price) AS MaxRRPrice, MIN(pro_rrp_price) AS MinRRPrice,
					MIN(pro_source_currency) as pro_source_currency,
					MIN(pro_country_price_override) as CountryPrices
				FROM shopsystem_product_extended_options
				WHERE pro_pr_id = {$pr_id}
					$optionsSQL
					AND pro_price IS NOT NULL
			");
	}
	$prices = $this->prices[$key];

	// do we have a price override for this country?

	$dest3Code = $_SESSION['ForceCountry']['cn_three_code'];
	if( strlen( $prices['CountryPrices'] ) )
	{
		ss_log_message( "parsing {$prices['CountryPrices']}" );
		if( ( $pos = strpos( $prices['CountryPrices'], $dest3Code.'=' ) ) !== false )
		{
			$ps = substr( $prices['CountryPrices'], $pos+4 );
			ss_log_message( "found at position $pos to extract $ps" );
			$prices['MaxPrice'] = $prices['MinPrice'] = (float) $ps;
		}
		else
			ss_log_message( "didn't find $dest3Code=" );
	}

	$exchangeRate = 1;

	if( $forceNoSpecial )
	{
		$prices['MaxSpecialPrice'] = NULL;
		$prices['MinSpecialPrice'] = NULL;
	}

	if( $prices['pro_source_currency'] != $currency['CurrencyCode'] )
	{

		$exchangeRate = ss_getExchangeRate($prices['pro_source_currency'], $currency['CurrencyCode'] );

		if( $logging )
			ss_log_message( "Exchange rate is $exchangeRate" );

		if( $exchangeRate > 0 )
		{
			if( $logging )
			{
				ss_log_message( "was" );
				ss_log_message_r(  'Log:'.__FILE__.':'.__LINE__, $prices );
			}

			if( IsSet( $prices['MaxPrice'] ) )
				$prices['MaxPrice'] = ($prices['MaxPrice'] * $exchangeRate );
			if( IsSet( $prices['MinPrice'] ) )
				$prices['MinPrice'] = ($prices['MinPrice'] * $exchangeRate );
			if( IsSet( $prices['MaxSpecialPrice'] ) )
				$prices['MaxSpecialPrice'] = ($prices['MaxSpecialPrice'] * $exchangeRate );
			if( IsSet( $prices['MinSpecialPrice'] ) )
				$prices['MinSpecialPrice'] = ($prices['MinSpecialPrice'] * $exchangeRate );
			if( IsSet( $prices['MaxMemberPrice'] ) )
				$prices['MaxMemberPrice'] = ($prices['MaxMemberPrice'] * $exchangeRate );
			if( IsSet( $prices['MinMemberPrice'] ) )
				$prices['MinMemberPrice'] = ($prices['MinMemberPrice'] * $exchangeRate );
			if( IsSet( $prices['MaxRRPrice'] ) )
				$prices['MaxRRPrice'] = ($prices['MaxRRPrice'] * $exchangeRate );
			if( IsSet( $prices['MinRRPrice'] ) )
				$prices['MinRRPrice'] = ($prices['MinRRPrice'] * $exchangeRate );

			$prices['pro_source_currency'] = $currency['CurrencyCode'];

			if( $logging )
			{
				ss_log_message( "now" );
				ss_log_message_r(  'Log:'.__FILE__.':'.__LINE__, $prices );
			}
		}
		else
		{
			// bad bad bad.  Cant get a price....  TODO, send off error message.

		}

		if( $logging )
		{
			ss_log_message( "PRICE" );
			ss_log_message_r(  'Log:'.__FILE__.':'.__LINE__, $prices );
			ss_log_message_r(  'Log:'.__FILE__.':'.__LINE__, $currency );
		}

	}

	$prices['MinSellPrice'] = $prices['MinPrice'];
	$prices['MaxSellPrice'] = $prices['MaxPrice'];
	
	// Load the special price if one is set
	$onSpecial = false;
	if (strlen($prices['MinSpecialPrice'])) {
		$onSpecial = true;
		$prices['MinSellPrice'] = $prices['MinSpecialPrice'];
		$prices['MaxSellPrice'] = $prices['MaxSpecialPrice'];
	}	

	// Check if allowing donations
	$donation = false;
	if (ss_optionExists("Shop Donations")) {
		$product = getRow("
			SELECT pr_donation FROM shopsystem_products
			WHERE pr_id = {$pr_id}
		");
		if ($product['pr_donation']) {
			$donation = true;
		}
	}
	
	// Check for discounts
	if (ss_optionExists("Shop Discount Codes")) {
		if (array_key_exists('Shop',$_SESSION) and array_key_exists('DiscountCode',$_SESSION['Shop']) and $_SESSION['Shop']['DiscountCode'] !== null) {
			if (array_key_exists('DiscountRates',$_SESSION['Shop']) and array_key_exists($discount,$_SESSION['Shop']['DiscountRates'])) {
				if ($_SESSION['Shop']['DiscountRates'][$discount] != 0) {				
					$prices['MinDiscountedPrice'] = $prices['MinPrice']*(100-$_SESSION['Shop']['DiscountRates'][$discount])/100;
					$prices['MaxDiscountedPrice'] = $prices['MaxPrice']*(100-$_SESSION['Shop']['DiscountRates'][$discount])/100;
					
					if ($prices['MinSellPrice'] > $prices['MinDiscountedPrice']) {
						$prices['MinSellPrice'] = $prices['MinDiscountedPrice'];
					}
		
					if ($prices['MaxSellPrice'] > $prices['MaxDiscountedPrice']) {
						$prices['MaxSellPrice'] = $prices['MaxDiscountedPrice'];
					}
				
					if ($onSpecial) {
						if ($prices['MinSpecialPrice'] > $prices['MinDiscountedPrice'])	{
							$prices['MinSpecialPrice'] = $prices['MinDiscountedPrice'];
						}
						if ($prices['MaxSpecialPrice'] > $prices['MaxDiscountedPrice'])	{
							$prices['MaxSpecialPrice'] = $prices['MaxDiscountedPrice'];
						}
					} else {
						$onSpecial = true;
						$prices['MinSpecialPrice'] = $prices['MinDiscountedPrice'];	
						$prices['MaxSpecialPrice'] = $prices['MaxDiscountedPrice'];	
					}
				}				
			}
		}
	}
	
	$tax = null;
	$taxZone = null;
	$taxCountry = null;
	$taxRate = null;

	// Get the tax country	
	if (array_key_exists('Shop',$_SESSION) and array_key_exists('TaxCountry',$_SESSION['Shop']) and $_SESSION['Shop']['TaxCountry'] !== false) {
		$taxCountry = $_SESSION['Shop']['TaxCountry']['cn_name'];
	}
	
	// Adjust prices for tax
	if (!ss_optionExists('Shop Tax Excluded')) 
	{
		if (array_key_exists('Shop', $_SESSION)) 
		{
			if (array_key_exists('TaxRate',$_SESSION['Shop']) and $_SESSION['Shop']['TaxRate'] !== false) 
			{
				$tax = $_SESSION['Shop']['TaxRate']['txc_name'];
				$taxZone = $_SESSION['Shop']['TaxZone']['TaZoName'];
				$taxRate = $_SESSION['Shop']['TaxRate']['Rate'];
		
				// Update the prices with the tax rate
				foreach($prices as $key => $price) 
				{
					if ($price !== null) 
					{
						$price = $price*(1+($taxRate/100));
					}
					$prices[$key] = $price;
				}
			}
		}
	}


	// Iff it's foobar, then add the frieght cost into the product price
	$m = '';
	$product = getRow( "select * from shopsystem_products join shopsystem_product_extended_options on pr_id = pro_pr_id where pr_id = {$pr_id}" );
	$includedFreightUSD = includedFreight( $product, $_SESSION['ForceCountry']['cn_id'] );
	$exchangeRateUSD = ss_getExchangeRate('USD', $currency['CurrencyCode'] );
	$includedFreight = $includedFreightUSD * $exchangeRateUSD;

	if( $logging )
		ss_log_message( "included freight on product id $pr_id to cn_id:{$_SESSION['ForceCountry']['cn_id']} is USD $includedFreightUSD -> {$currency['CurrencyCode']} $includedFreight ($exchangeRateUSD)" );

	if( $includedFreight > 0 )
	{
		$m = "Shipping is included in the price.";
		foreach($prices as $key => $price)
		{
			if( $price && $price > 0 )
			{
				$price += $includedFreight;
				if( $logging )
					ss_log_message( "Now $key price is {$currency['CurrencyCode']} $price" );
				$prices[$key] = $price;
			}
		}
	}
	else
		$m = "Shipping is not included in the price.";


	if( array_key_exists( 'ShowFakePrices', $GLOBALS['cfg']) 
		&& in_array( $_SERVER['REMOTE_ADDR'], $GLOBALS['cfg']['ShowFakePrices'] ) )
	{
		ss_log_message( "ShowFakePrices?" );
		ss_log_message( $_SERVER['REMOTE_ADDR'] );
		ss_log_message_r(  'Log:'.__FILE__.':'.__LINE__,  $GLOBALS['cfg']['ShowFakePrices'] );

		foreach($prices as $key => $price)
			if ($price !== null) 
				$prices[$key] = 1.6728*$price;
	}
			
	$priceMessage .= $m;
	
	if( $QuoteShipping == 1 )
		$priceMessage .= "Due to the size and weight of this paddock we will get back to you with a final quote on price including shipping. The order will not be processed until we have received your approval for the total cost including shipping.";

	if( $UsOnly == 1 )
		$priceMessage .= "  Due to carrier restrictions, we can ship this product only to the US";


	$local_tax =  $_SESSION['ForceCountry']['cn_tax_x100' ];

	if( $product['pr_sales_tax_exempt'] == 'true' )
		$local_tax = 0;

	$site_discount = 0;
	if( 1 )
	{
		global $cfg;
		if( array_key_exists( $cfg['multiSites'][$cfg['currentServer']], $cfg['multiSiteDiscount'] ) )
			$site_discount = $cfg['multiSiteDiscount'][$cfg['multiSites'][$cfg['currentServer']]];
	}

	if( strlen( $_SESSION['ForceCountry'][ 'cn_warning' ] ) > 0 )
		$priceMessage .= $_SESSION['ForceCountry'][ 'cn_warning' ];

	if( $logging )
		ss_log_message( "Price message".$priceMessage );

	if( $local_tax > 0 )
	{
		if( $logging )
			ss_log_message( "adding local tax of %".((float)$local_tax/100) );

		if( IsSet($prices['MaxPrice'] ) ) $prices['MaxPrice'] += ($prices['MaxPrice'] * $local_tax/10000); 
		if( IsSet($prices['MinPrice'] ) ) $prices['MinPrice'] += ($prices['MinPrice'] * $local_tax/10000); 
		if( IsSet($prices['MaxSpecialPrice'] ) ) $prices['MaxSpecialPrice'] += ($prices['MaxSpecialPrice'] * $local_tax/10000); 
		if( IsSet($prices['MinSpecialPrice'] ) ) $prices['MinSpecialPrice'] += ($prices['MinSpecialPrice'] * $local_tax/10000); 
		if( IsSet($prices['MaxMemberPrice'] ) ) $prices['MaxMemberPrice'] += ($prices['MaxMemberPrice'] * $local_tax/10000); 
		if( IsSet($prices['MinMemberPrice'] ) ) $prices['MinMemberPrice'] += ($prices['MinMemberPrice'] * $local_tax/10000); 
		if( IsSet($prices['MaxRRPrice'] ) ) $prices['MaxRRPrice'] += ($prices['MaxRRPrice'] * $local_tax/10000); 
		if( IsSet($prices['MinRRPrice'] ) ) $prices['MinRRPrice'] += ($prices['MinRRPrice'] * $local_tax/10000); 
		if( IsSet($prices['MinSellPrice'] ) ) $prices['MinSellPrice'] += ($prices['MinSellPrice'] * $local_tax/10000); 
		if( IsSet($prices['MaxSellPrice'] ) ) $prices['MaxSellPrice'] += ($prices['MaxSellPrice'] * $local_tax/10000); 

		if( $logging )
		{
			ss_log_message( "price after country tax" );
			ss_log_message_r(  'Log:'.__FILE__.':'.__LINE__, $prices );
		}
	}

	if( $site_discount != 0 )
	{
		if( $logging )
			ss_log_message( "subtracting site discount of %".((float)$site_discount/100 ));

		if( IsSet($prices['MaxPrice'] ) ) $prices['MaxPrice'] -= ($prices['MaxPrice'] * $site_discount/100); 
		if( IsSet($prices['MinPrice'] ) ) $prices['MinPrice'] -= ($prices['MinPrice'] * $site_discount/100); 
		if( IsSet($prices['MaxSpecialPrice'] ) ) $prices['MaxSpecialPrice'] -= ($prices['MaxSpecialPrice'] * $site_discount/100); 
		if( IsSet($prices['MinSpecialPrice'] ) ) $prices['MinSpecialPrice'] -= ($prices['MinSpecialPrice'] * $site_discount/100); 
		if( IsSet($prices['MaxMemberPrice'] ) ) $prices['MaxMemberPrice'] -= ($prices['MaxMemberPrice'] * $site_discount/100); 
		if( IsSet($prices['MinMemberPrice'] ) ) $prices['MinMemberPrice'] -= ($prices['MinMemberPrice'] * $site_discount/100); 
		if( IsSet($prices['MaxRRPrice'] ) ) $prices['MaxRRPrice'] -= ($prices['MaxRRPrice'] * $site_discount/100); 
		if( IsSet($prices['MinRRPrice'] ) ) $prices['MinRRPrice'] -= ($prices['MinRRPrice'] * $site_discount/100); 
		if( IsSet($prices['MinSellPrice'] ) ) $prices['MinSellPrice'] -= ($prices['MinSellPrice'] * $site_discount/100); 
		if( IsSet($prices['MaxSellPrice'] ) ) $prices['MaxSellPrice'] -= ($prices['MaxSellPrice'] * $site_discount/100); 

		if( $logging )
		{
			ss_log_message( "price after site discount" );
			ss_log_message_r(  'Log:'.__FILE__.':'.__LINE__, $prices );
		}
	}

	// Round prices up or down as required
	/*
	if (ss_optionExists('Shop Round Prices') || ($exchangeRate != 1) ) {
		foreach ($prices as $key => $price) {
			$prices[$key] = ss_roundMoney($price);
		}
	}
	*/
		
	// Update the prices so they only have 2 decimal places
	$precision = 2;
	if( array_key_exists( 'Precision', $currency ) )
		$precision =  $currency['Precision'];

	foreach($prices as $key => $price) {
		$prices[$key] = ss_decimalFormat($price, $precision);
	}	

	
	// Perform currency conversion
	$approx = array();
	$currencyConverter = false;
	if (array_key_exists('Shop', $_SESSION) and !array_key_exists('NoApprox',$this->ATTRIBUTES)) {
		if (array_key_exists('CurrencyCountry',$_SESSION['Shop']) and $_SESSION['Shop']['CurrencyCountry'] !== false) {
			$currency = $this->getDisplayCurrency();
			if ($_SESSION['Shop']['CurrencyCountry']['cn_currency_code'] != $currency['CurrencyCode']) {
				$currencyConverter = true;
				$approx = array(
					'RRPApprox'				=>	$this->formatPrice("displayApprox",$prices['MinRRPrice'],$prices['MaxRRPrice'], $currency['CurrencyCode']),
					'SpecialPriceApprox'	=>	$this->formatPrice("displayApprox",$prices['MinSpecialPrice'],$prices['MaxSpecialPrice'], $currency['CurrencyCode']),
					'NormalPriceApprox'		=>	$this->formatPrice("displayApprox",$prices['MinPrice'],$prices['MaxPrice'], $currency['CurrencyCode']),
				);
			}
		}
	}

	// log this $prices['MinSellPrice']

	$ra = escape( $_SERVER['REMOTE_ADDR'] );
	$pr = $prices['MinSellPrice'];
//	if( strlen( $pr ) )
//		Query( "insert into seen_price (cp_pr_id, cp_price, cp_ip_address, cp_country_id) values ($pr_id, $pr, '$ra', {$_SESSION['ForceCountry']['cn_id']} )");

	//ss_log_message( "type = $type" );
	if ($type == 'TableHTML' or $type == 'Complete' or $type == 'SmallHTML') {
		if (array_key_exists("AssetPath", $this->ATTRIBUTES))
				$assetPath = $this->ATTRIBUTES['AssetPath'];
	   	else 						
			$assetPath = ss_withoutPreceedingSlash($this->asset->getPath());
		
		/*
		if (array_search('asset', get_object_vars($this)) !== false) {
			$assetPath = ss_withoutPreceedingSlash($this->asset->getPath());
		} else {
			if (array_key_exists("AssetPath", $this->ATTRIBUTES))
				$assetPath = $this->ATTRIBUTES['AssetPath'];
		}*/
		$data = array_merge($approx,array_merge($prices,array(
			'currency_converter'	=>	$currencyConverter,	
			'RRP'			=>	$this->formatPrice("display",$prices['MinRRPrice'],$prices['MaxRRPrice']),
			'SpecialPrice'	=>	$this->formatPrice("display",$prices['MinSpecialPrice'],$prices['MaxSpecialPrice']),
			'NormalPrice'	=>	$this->formatPrice("display",$prices['MinPrice'],$prices['MaxPrice']),
			'rawRRP'			=>	$prices['MinRRPrice'],
			'rawSpecialPrice'	=>	$prices['MinSpecialPrice'],
			'rawNormalPrice'	=>	$prices['MinPrice'],
			'OnSpecial'		=>	$onSpecial,
			'Tax'			=>	$tax,
			'TaxZone'		=>	$taxZone,
			'TaxCountry'	=>	$taxCountry,
			'TaxRate'		=>	$taxRate,
			'SpecialDescription'	=>	$specialDescription,
			'AssetPath'		=>	ss_EscapeAssetPath($assetPath),
			'BackURL'		=>	getBackURL(),
			'pr_id'			=>	$pr_id,
			'PriceMessage'		=>	$priceMessage,
			'Donation'		=>	$donation,
		)));
		//ss_log_message_r(  'Log:'.__FILE__.':'.__LINE__, $data );

		if ($type == 'TableHTML') {
			$display = $this->processTemplate('Price',$data);
			return $display;
		} else if ($type == 'SmallHTML') {
			$display = $this->processTemplate('SmallPrice',$data);
			return $display;
		} else {
			return $data;	
		}
	} else if ($type == 'PriceOnly') {
		return $prices['MinSellPrice'];
	}

?>
