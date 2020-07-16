<?php
	$this->param('CC');
	$this->param('BackURL');

	$_SESSION['CountryAcknowledged'] = true;

	// default it to be the IP based country....
/*
	if( !array_key_exists( 'ForceCountry', $_SESSION )
	 || !is_array( $_SESSION['ForceCountry'] ) )
	{
		$show_intro = true;
		$_SESSION['ForceCountry'] = getRow( "select * from countries where cn_two_code = '".ss_getCountry(NULL, 'cn_two_code')."'");
		ss_log_message( "New country defaulting to ". $_SESSION['ForceCountry'] );
	}
*/

	$Cn = GetRow( "select * from countries where cn_two_code = '".safe( $this->ATTRIBUTES['CC'] )."'" );

	header("Cache-Control: no-cache, must-revalidate");
	if( $Cn )
	{
		$_SESSION['ForceCountry'] = $Cn;
		ss_log_message( "Country is now ".$_SESSION['ForceCountry']['cn_name']." ".$_SESSION['ForceCountry']['cn_currency_code'] );

		checkProductVendors();

		location($this->ATTRIBUTES['BackURL']);
	}
	else
	{
		echo "No";
		die;
	}

?>
