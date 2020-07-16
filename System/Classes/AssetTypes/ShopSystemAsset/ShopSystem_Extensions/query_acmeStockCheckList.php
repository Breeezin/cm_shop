<?php

	if( count($_POST) > 0 )
	{
		if( array_key_exists( 'finalise', $_POST ) )
		{
			// need to record changes to these numbers, so look at what it was, now is, reserve stock as needed.
			// read the whole lot into an array, go from there.
			query( "delete from stock_movement where sm_from_vendor = 2 and sm_to_vendor = 4" );

			$Shop = getRow("SELECT * FROM assets WHERE as_id = 514" );
			$result = new Request("Asset.Display",array(
				'as_id'	=>	$Shop['as_id'],
				'Service'	=>	'UpdateBasket',
				'Mode'	=>	'Empty',
				'AsService'	=>	true,
				'NoHusk'	=>	1,
				));

			foreach( $_POST as $prod=>$qty )
			{
				if( $pos = strpos( $prod, '_' ) )
				{
					$pr_id = substr( $prod, $pos + 1 );
					if( $qty > 0 )
					{
//						echo "Ordering $qty x pr_id:$pr_id<br />";
						ss_log_message( "adding $qty x pr_id:$pr_id to basket" );
						$option = getRow( "select * from shopsystem_product_extended_options where pro_pr_id = ".((int)$pr_id) );
						$result = new Request("Asset.Display",array(
									'as_id'	=>	$Shop['as_id'],
									'Service'	=>	'UpdateBasket',
									'Mode'	=>	'Add',
									'Key'	=>	$pr_id.'_'.$option['pro_id'],
									'Qty'	=>	$qty,
									'AsService'	=>	true,
									'NoHusk'	=>	1,
									'Reship'    =>  1,
								));
					}
				}
			}
			for	($index=0;$index<count($_SESSION['Shop']['Basket']['Products']);$index++)
			{
				$_SESSION['Shop']['Basket']['Products'][$index]['Product']['pro_price'] = 0;
				$_SESSION['Shop']['Basket']['Products'][$index]['Product']['pro_special_price'] = 0;
				$_SESSION['Shop']['Basket']['Products'][$index]['Product']['Price'] = 0;
			}
			$_SESSION['Shop']['Basket']['SubTotal'] = 0;
			$_SESSION['Shop']['Basket']['Total'] = 0;



			// new order as user 19418
			header( "Location: Shop_System/Service/OrderForClient/TransferOrder/1/ExistingClient/19418" );
		}
		else
		{
			// need to record changes to these numbers, so look at what it was, now is, reserve stock as needed.
			query( "delete from stock_movement where sm_from_vendor = 2 and sm_to_vendor = 4" );
			foreach( $_POST as $prod=>$qty )
			{
				if( $pos = strpos( $prod, '_' ) )
				{
					$pr_id = substr( $prod, $pos + 1 );
					if( $qty > 0 )
					{
						echo "saving $qty x pr_id:$pr_id<br />";
						query( "insert into stock_movement (sm_from_vendor, sm_to_vendor, sm_pr_id, sm_qty ) values (2, 4, $pr_id, $qty)" );
					}
				}
			}
		}
	}



/*
MariaDB [pe]> describe shopsystem_order_items;
+----------------------------+--------------+------+-----+---------+-------+
| Field                      | Type         | Null | Key | Default | Extra |
+----------------------------+--------------+------+-----+---------+-------+
| oi_stock_code              | varchar(255) | YES  |     | NULL    |       |
| oi_name                   | longtext     | YES  |     | NULL    |       |
| oi_or_id              | int(11)      | YES  | MUL | NULL    |       |
| oi_qty                    | int(11)      | YES  |     | NULL    |       |
| oi_eos_id | int(11)      | YES  |     | NULL    |       |
| oi_ve_id                 | int(1)       | YES  |     | 1       |       |
| oi_box_number              | int(11)      | YES  |     | NULL    |       |
+----------------------------+--------------+------+-----+---------+-------+
*/

	// stock in the packing system
	query( "create temporary table in_system as select oi_stock_code, count(*) as Qty from shopsystem_order_items where oi_eos_id IS NULL group by oi_stock_code" );

/*
MariaDB [pe]> describe ordered_products;
+-------------+--------------+------+-----+---------+-------+
| Field       | Type         | Null | Key | Default | Extra |
+-------------+--------------+------+-----+---------+-------+
| OrderLink   | int(11)      | NO   | MUL | 0       |       |
| ProductLink | int(11)      | NO   | MUL | 0       |       |
| StockCode   | varchar(255) | NO   |     |         |       |
| Name        | varchar(255) | NO   |     |         |       |
| Price       | int(255)     | YES  |     | NULL    |       |
| Quantity    | int(11)      | YES  |     | NULL    |       |
| SiteFolder  | varchar(20)  | YES  |     | NULL    |       |
+-------------+--------------+------+-----+---------+-------+
*/

	query( "create temporary table on_hold as select or_id from shopsystem_orders where or_archive_year IS NULL and or_standby IS NOT NULL " );
	// remove those that have been through the packing system
	query( "delete from on_hold where or_id in (select oi_or_id from shopsystem_order_items)" );

	query( "create temporary table on_standby as select op_stock_code as StockCode, sum(op_quantity) as SQty from ordered_products join on_hold on op_or_id = or_id group by op_stock_code" );

	$Q_StockS = query("
		SELECT * FROM shopsystem_products join shopsystem_product_extended_options on pro_pr_id = pr_id left join in_system on oi_stock_code = pro_stock_code
		left join on_standby on pro_stock_code = StockCode
		left join stock_movement on sm_pr_id = pr_id
		where pr_ve_id = 2 and pr_is_service = 'false' and pr_combo IS NULL and pr_deleted IS NULL
		order by pro_stock_available desc
	");	

	$Q_StockM = query("
		SELECT * FROM shopsystem_products join shopsystem_product_extended_options on pro_pr_id = pr_id left join in_system on oi_stock_code = pro_stock_code
		left join on_standby on pro_stock_code = StockCode
		where pr_ve_id = 4 and pr_is_service = 'false' and pr_combo IS NULL and pr_deleted IS NULL
		order by pro_stock_available desc
	");	

?>
