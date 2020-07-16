<?php


	$this->param("or_id");
	$Q_Order = getRow("SELECT * FROM shopsystem_orders WHERE or_id = {$this->ATTRIBUTES['or_id']}");

	$oldBasket = array();
	if (strlen($Q_Order['or_basket'])) {
		$oldBasket = unserialize($Q_Order['or_basket']);
	}

//    ss_DumpVar($oldBasket);
//    ss_DumpVar($details);
//    ss_DumpVar($Q_Order);

//    $oldProducts = &$_SESSION['Shop']['Basket']['Products'];
    $oldProducts = $oldBasket['Basket']['Products'];
    if (count($oldProducts)>0){
        foreach( $oldProducts as $key => $value ) {
            $R_newDetails = getRow("
                Select pr_id, pr_short,pr_name,PrFreightTypeLink,
                    pro_uuids,pro_price,pro_special_price,
                    pro_member_price,PrExOpFreightValue,PrExOpFreightCodeLink
                from shopsystem_products, shopsystem_product_extended_options
                where pr_id = pro_pr_id and pr_id=".$value['Product']['pr_id']."
                and pro_stock_code='".$value['Product']['pro_stock_code']."'
            ");
//            ss_DumpVarDie($R_newDetails);
//            $R_newDetails['pro_price'] = null;
//            $R_newDetails['pro_special_price'] = null;

            $oldBasket['Basket']['Products'][$key]['Product'] =
                array_merge($oldBasket['Basket']['Products'][$key]['Product'],
                            $R_newDetails);

//            ss_DumpVar($Q_Order);
//            ss_DumpVarDie($R_newDetails);

        }
    }

    $oldBasket['Basket']['Total'] = null;
    $oldBasket['Basket']['SubTotal'] = null;
//    calculatePrices($this);

//    ss_DumpVar($oldBasket);
	$theBasket = escape(serialize($oldBasket));
     $_SESSION['Shop'] = $oldBasket;
    // now hit the basket
//    ss_DumpVarDie($_SESSION['Shop']);
//    ss_DumpVarDie($_SESSION);
//    ss_DumpVarDie($_SESSION['Shop']['Basket']);

	if (strlen($Q_Order['or_details'])) {
		$details = unserialize($Q_Order['or_details']);
	}

//    ss_DumpVarDie($details);

//    ss_DumpVar($details);

    $oldProducts = &$details['OrderProducts'];
    if (count($oldProducts)>0){
        foreach( $oldProducts as $key => $value ) {
            $R_newDetails = getRow("
                Select pr_id, pr_short,pr_name,PrFreightTypeLink,
                    pro_uuids,pro_stock_code,pro_price,pro_special_price,
                    pro_member_price,PrExOpFreightValue,PrExOpFreightCodeLink
                from shopsystem_products, shopsystem_product_extended_options
                where pr_id = pro_pr_id and pr_id=".$value['Product']['pr_id']."
                and pro_stock_code='".$value['Product']['pro_stock_code']."'
            ");
            $R_newDetails['Price'] = null;

            $details['OrderProducts'][$key]['Product'] =
                array_merge($details['OrderProducts'][$key]['Product'],
                            $R_newDetails);
        }
    }

   $details['BasketHTML'] = null;
//    ss_DumpVarDie($details);
	$theDetails = escape(serialize($details));

/*
	$Q_UpdateOrder = query("
		Insert into shopsystem_orders  
                (
                or_tr_id,
                or_purchaser_email,
                or_details,
                or_shipping_details,
                or_recorded,
                or_actioned,
                or_shipped,
                or_tracking_code,
                or_paid,
                or_purchaser_firstname,
                or_purchaser_lastname,
                or_as_id,
                or_total,
                or_us_id,
                or_deleted,
                or_user_ipaddress,
                or_tracked_and_traced,
                or_track_and_trace_code,
                or_track_link,
                or_error_codes,
                or_discount_code,
                or_basket )
        select 

                or_tr_id,
                or_purchaser_email,
                '{$theDetails}',
                or_shipping_details,
                or_recorded,
                or_actioned,
                or_shipped,
                or_tracking_code,
                or_paid,
                or_purchaser_firstname,
                or_purchaser_lastname,
                or_as_id,
                or_total,
                or_us_id,
                or_deleted,
                or_user_ipaddress,
                or_tracked_and_traced,
                or_track_and_trace_code,
                or_track_link,
                or_error_codes,
                or_discount_code,
                '{$theBasket}'
        from shopsystem_orders
		WHERE or_id = {$this->ATTRIBUTES['or_id']}
	");

mysql> describe shopsystem_orders;
+----------------------+--------------+------+-----+---------+----------------+
| Field                | Type         | Null | Key | Default | Extra          |
+----------------------+--------------+------+-----+---------+----------------+
| or_id                 | int(11)      |      | PRI | NULL    | auto_increment |
| or_tr_id    | int(11)      | YES  |     | NULL    |                |
| or_purchaser_email     | varchar(127) | YES  |     | NULL    |                |
| or_details            | longtext     | YES  |     | NULL    |                |
| or_shipping_details    | longtext     | YES  |     | NULL    |                |
| or_recorded           | datetime     | YES  |     | NULL    |                |
| or_actioned           | datetime     | YES  |     | NULL    |                |
| or_shipped            | datetime     | YES  |     | NULL    |                |
| or_tracking_code       | varchar(255) | YES  |     | NULL    |                |
| or_paid               | datetime     | YES  |     | NULL    |                |
| or_purchaser_firstname | varchar(127) | YES  |     | NULL    |                |
| or_purchaser_lastname  | varchar(127) | YES  |     | NULL    |                |
| or_as_id          | int(11)      | YES  |     | NULL    |                |
| or_total              | varchar(50)  | YES  |     | NULL    |                |
| or_us_id           | int(11)      | YES  |     | NULL    |                |
| or_deleted            | tinyint(1)   |      |     | 0       |                |
| or_user_ipaddress      | varchar(50)  | YES  |     | NULL    |                |
| or_tracked_and_traced   | datetime     | YES  |     | NULL    |                |
| or_track_and_trace_code  | varchar(255) | YES  |     | NULL    |                |
| or_track_link          | longtext     | YES  |     | NULL    |                |
| or_error_codes         | longtext     | YES  |     | NULL    |                |
| or_discount_code       | varchar(255) | YES  |     | NULL    |                |
| or_basket             | longtext     | YES  |     | NULL    |                |

*/
	$result = new Request('Asset.Display',array(
		'NoHusk'	=>	true,
		'as_id'	=>	$Q_Order['or_as_id'],
		'Service'	=>	'UpdateBasket',
        'Mode'      =>  'Refresh',
		'AsService'	=>	true,
	));

    $this->ATTRIBUTES['or_id'] = null;

  //  ss_DumpVarDie($_SESSION['Shop']['Basket']);
	$result = new Request('Asset.PathFromID', array('as_id'=> $Q_Order['or_as_id']));

//	locationRelative($result->value."/Service/UpdateBasket");
	locationRelative($result->value."/Service/Basket");
?>
