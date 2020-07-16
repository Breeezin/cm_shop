<?php

	// add the product into the wish list

	$UserID = ss_getUserID();


	if( false && $UserID > 0 )
	{
		$commonOrders = query( "select substring(op_stock_code,1,4) as stock_code, count(*) as count from ordered_products, shopsystem_orders where or_us_id = $UserID and op_or_id = or_id GROUP BY substring(op_stock_code,1,4) ORDER BY 2 desc LIMIT 10" );

		$data = array(
			'CommonOrders'	=>	$commonOrders,
			);
		$this->useTemplate('Suggested',$data);

	}


?>
