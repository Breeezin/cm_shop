<?
	if (array_key_exists('TaxRate',$_SESSION['Shop']) and $_SESSION['Shop']['TaxRate'] !== false) {
		$tax = $_SESSION['Shop']['TaxRate']['txc_name'];
		$taxZone = $_SESSION['Shop']['TaxZone']['TaZoName'];
		$taxRate = $_SESSION['Shop']['TaxRate']['Rate'];

		if ($price !== null) {
			$price = $price*($taxRate/100);
			$price = sprintf("%01.2f",$price);
		}
		
		return array(
			'Code'		=>	$tax,
			'Amount'	=>	$price,
		);
	}
	
	return false;
	
?>