<?php

	$this->param('Tx');

	$Order = getRow(" SELECT * FROM shopsystem_orders
							WHERE or_tr_id = ".safe($this->ATTRIBUTES['Tx']));

	$phone = getField( "select us_0_B4C1 from users where us_id = ".$Order['or_us_id'] );

	$Q_Boxes = query( "select * from shopsystem_order_sheets_items where orsi_or_id = ".safe($Order['or_id'])." and orsi_received IS NULL" );
	$Boxes = array();
	while( $row = $Q_Boxes->fetchRow() )
		$Boxes[] = $row;

?>
