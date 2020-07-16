<?

	function local_log( $message )
	{
		ss_log_message( $message );
		print( $message."\r\n<br />" );
	}


	// fix up where someone cancels an order that is already shipped out (not really)...  WTF?

	if( $q = query( "select * from shopsystem_order_sheets_items join shopsystem_orders on or_id = orsi_or_id where or_cancelled IS NOT NULL and orsi_sil_id IS NOT NULL" ) )
	{
		while( $r = $q->fetchRow() )
		{
			local_log( "Removing cancelled order ID:{$r['or_tr_id']} product {$r['orsi_stock_code']} / {$r['orsi_pr_name']} / box {$r['orsi_box_number']}  from supplier sheet allocations" );

			// these need this
			query( "update shopsystem_order_sheets_items set orsi_sil_id = NULL where orsi_id = {$r['orsi_id']}" );

			// and the other end

			$rsil_id = $r['orsi_sil_id'];

			$sold = getField( "select count(*) from shopsystem_order_sheets_items where orsi_sil_id = $rsil_id" )
				  + getField( "select count(*) from customer_invoice_shipment where cis_sil_id = $rsil_id" );

			query( "update supplier_invoice_line set sil_shipped_count = $sold where sil_id = $rsil_id" );
		}
	}

	// fix up where some idiot has reducted the amount put in stock below that which has already been shipped out

	query( "create temporary table reduce as select sil_id as rsil_id, sil_sin_id as inu_id, sil_pr_id as r_prid, sil_qty_put_in_stock as r_added, sil_shipped_count as r_shipped from supplier_invoice_line where sil_shipped_count > sil_qty_put_in_stock" );

	if( $q = query( "select * from reduce order by rsil_id desc" ) )
	{
		while( $r = $q->fetchRow() )
		{
			$rsil_id = $r['rsil_id'];
			$prod = getRow( "select * from shopsystem_products join shopsystem_product_extended_options on pr_id = pro_pr_id where pr_id = {$r['r_prid']}" );

			local_log( "reducing id:$rsil_id on supplier invoice {$r['inu_id']} of pr_id:{$r['r_prid']} ({$prod['pro_stock_code']} / {$prod['pr_name']}) to {$r['r_added']} from {$r['r_shipped']}" );

			if( $q2 = query( "select * from shopsystem_order_sheets_items where orsi_sil_id = $rsil_id order by orsi_id desc" ) )
			{
				for( $i = 0; $r2 = $q2->fetchRow(); $i++ )
				{
					if( $i < $r['r_added'] )
						continue;			// this one can stay

					// set orsi_sil_id to be something else
					$sil_id = (int)getField( "select min(sil_id) as thisBox from supplier_invoice_line where sil_pr_id = {$r2['orsi_pr_id']} and sil_shipped_count < sil_qty_put_in_stock" );

					if( $sil_id )
					{
						$si = getField( "select sil_sin_id from supplier_invoice_line where sil_id = $sil_id" );

						local_log( "This box goes to id:$sil_id from supplier invoice:$si" );

						query( "update shopsystem_order_sheets_items
								  set orsi_sil_id = $sil_id
									where orsi_id = ".(int)($r2['orsi_id']) );

						$sold = getField( "select count(*) from shopsystem_order_sheets_items where orsi_sil_id = $sil_id" )
							  + getField( "select count(*) from customer_invoice_shipment where cis_sil_id = $sil_id" );

						query( "update supplier_invoice_line set sil_shipped_count = $sold where sil_id = $sil_id" );
					}
					else
					{
						local_log( "This box appeared by magic, it will not appear on any customs report." );

						query( "update shopsystem_order_sheets_items
								  set orsi_sil_id = NULL
									where orsi_id = ".(int)($r2['orsi_id']) );
					}
				}
			}

			$sold = getField( "select count(*) from shopsystem_order_sheets_items where orsi_sil_id = $rsil_id" )
				  + getField( "select count(*) from customer_invoice_shipment where cis_sil_id = $rsil_id" );

			query( "update supplier_invoice_line set sil_shipped_count = $sold where sil_id = $rsil_id" );
		}
	}

?>
