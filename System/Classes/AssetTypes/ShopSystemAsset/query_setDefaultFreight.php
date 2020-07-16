<?php

	if (!array_key_exists('Freight',$_SESSION['Shop']['Basket'])) {
		
		$_SESSION['Shop']['Basket']['Freight'] = array(
			'Zone'	=>	NULL,
			'ZoneName'	=>	NULL,
			'Amount'	=>	0,
		);

		$totalDiscounts = 0;
		// figure out the new sub total
		if( array_key_exists( 'Discounts', $_SESSION['Shop']['Basket'] ) && is_array( $_SESSION['Shop']['Basket']['Discounts'] ) )
			foreach($_SESSION['Shop']['Basket']['Discounts'] as $discount => $amount)
				$totalDiscounts += $amount;	

		$_SESSION['Shop']['Basket']['Freight']['Amount'] = $this->calculateExtraFreight();
		if (array_key_exists('SubTotal',$_SESSION['Shop']['Basket'])) {
			$_SESSION['Shop']['Basket']['Total'] = $_SESSION['Shop']['Basket']['SubTotal']+$totalDiscounts+$_SESSION['Shop']['Basket']['Freight']['Amount'];
		}
		
	}
	

?>
