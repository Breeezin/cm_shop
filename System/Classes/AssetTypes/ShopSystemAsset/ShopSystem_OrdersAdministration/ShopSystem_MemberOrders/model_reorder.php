<?php

	$this->param("or_id");
	$Q_Order = getRow("SELECT * FROM shopsystem_orders WHERE or_id = {$this->ATTRIBUTES['or_id']}");

	$oldBasket = array();
	if (strlen($Q_Order['or_basket'])) {
		$oldBasket = unserialize($Q_Order['or_basket']);
	}

    $oldProducts = $oldBasket['Basket']['Products'];
    if (count($oldProducts)>0){
        $counter = 0;
        foreach( $oldProducts as $key => $value ) {
            $R_newDetails = getRow("
                Select pr_id, pr_short,pr_name,
                    pro_uuids,pro_price,pro_special_price,
                    pro_member_price
                from shopsystem_products, shopsystem_product_extended_options
                where pr_id = pro_pr_id and pr_id=".$value['Product']['pr_id']."
                and pro_stock_code='".$value['Product']['pro_stock_code']."'
            ");

            // deal to deleted products
            if ($R_newDetails) {
                $oldBasket['Basket']['Products'][$key]['Product'] =
                    array_merge($oldBasket['Basket']['Products'][$key]['Product'], $R_newDetails);
            } else {
                unset($oldBasket['Basket']['Products'][$key]);
            }
        }
    }
    $oldBasket['Basket']['Products'] = array_merge(array(),$oldBasket['Basket']['Products']);

    $oldBasket['Basket']['Total'] = null;
    $oldBasket['Basket']['SubTotal'] = null;

	$theBasket = escape(serialize($oldBasket));
     $_SESSION['Shop'] = $oldBasket;

	if (strlen($Q_Order['or_details'])) {
		$details = unserialize($Q_Order['or_details']);
	}

    $oldProducts = &$details['OrderProducts'];
    // new details
    // $newproducts = array();

    if (count($oldProducts)>0){

        foreach( $oldProducts as $key => $value ) {
            $R_newDetails = getRow("
                Select pr_id, pr_short,pr_name,
                    pro_uuids,pro_stock_code,pro_price,pro_special_price,
                    pro_member_price
                from shopsystem_products, shopsystem_product_extended_options
                where pr_id = pro_pr_id and pr_id=".$value['Product']['pr_id']."
                and pro_stock_code='".$value['Product']['pro_stock_code']."'
            ");

            if ($R_newDetails) {
                $R_newDetails['Price'] = null;
                $details['OrderProducts'][$key]['Product'] =
                    array_merge($details['OrderProducts'][$key]['Product'],  $R_newDetails);
                // array_push($newproducts,$details['OrderProducts'][$key]);
            } else {
                unset($details['OrderProducts'][$key]);
            }

        }
    }


    $details['OrderProducts']=array_merge(array(),$details['OrderProducts']);
    $details['BasketHTML'] = null;
    $details['GiftMessage'] = null;


	$theDetails = escape(serialize($details));

	$result = new Request('Asset.Display',array(
		'NoHusk'	=>	true,
		'as_id'	=>	$Q_Order['or_as_id'],
		'Service'	=>	'UpdateBasket',
        'Mode'      =>  'Refresh',
		'AsService'	=>	true,
	));

    $this->ATTRIBUTES['or_id'] = null;

	$result = new Request('Asset.PathFromID', array('as_id'=> $Q_Order['or_as_id']));

	locationRelative($result->value."/Service/Basket");
?>
