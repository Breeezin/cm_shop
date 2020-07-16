<?

	$countryThreeLetterCode = safe( $countryThreeLetterCode );

	$field = 'cn_three_code';
	if ($byCurrency) {
		$field = 'cn_currency_code';
	}
	$Q_Country = query("	
		SELECT * FROM countries
		WHERE $field LIKE '".$countryThreeLetterCode."'
	");		
	if ($Q_Country->numRows()) {
		// Get the country
		$_SESSION['Shop']['CurrencyCountry'] = $Q_Country->fetchRow();
		setDefaultCurrency( $_SESSION['Shop']['CurrencyCountry']['cn_currency_code'] );
	} else {
		// Give up
		$_SESSION['Shop']['CurrencyCountry'] = false;
	}

?>
