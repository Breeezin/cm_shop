<?php

	function savePriceAlterations( $alterations, $key )
	{
		return;
		ss_log_message( "Product Price Alterations" );
		ss_log_message_r(  'Log:'.__FILE__.':'.__LINE__, $alterations );
		foreach( $alterations as $pr_id => $SupplierPrice )
		{
			if( strlen( $SupplierPrice ) )
			{
				$pr_row = getRow( "select * from shopsystem_product_extended_options where pro_pr_id = ".((int)$pr_id) );
				$pr_id = $pr_row['pro_pr_id'];
				$in_stock = $pr_row['pro_stock_available'];

				if( !strlen( $in_stock ) || ($in_stock < 0) )
					$in_stock = 0;

				query( "insert into price_changes (pc_us_id, pc_pr_id, pc_field_name, pc_notes, pc_amount, pc_in_stock )"
					." values (".ss_getUserID().", $pr_id, 'prexopsupplierprice', 'edit of invoice $key changes supplier price to $supplierprice, stock level $in_stock', $supplierprice, $in_stock )" );

				query( "update shopsystem_product_extended_options set pro_supplier_price = $SupplierPrice where pro_pr_id = $pr_id" );

				$in_stock = getField( "select pro_stock_available from shopsystem_product_extended_options where  pro_pr_id = $pr_id" );
				ss_audit( 'update', 'Products', $pr_id, "Edit of invoice $key alters supplier price to  $SupplierPrice, in stock is $in_stock" );
			}
		}
	}

	function saveQtyAlterations( $alterations, $key )
	{
		// if it's less than zero, start from zero
		// audits....
		// 
//		return;
		ss_log_message( "Product Qty Alterations" );
		ss_log_message_r(  'Log:'.__FILE__.':'.__LINE__, $alterations );
		foreach( $alterations as $pr_id => $Qty )
		{
			if( strlen( $Qty ) )
			{
				$pr_row = getRow( "select * from shopsystem_product_extended_options join shopsystem_products on pro_pr_id = pr_id where pro_pr_id = ".((int)$pr_id) );
				$pr_id = $pr_row['pro_pr_id'];
				$in_stock = $pr_row['pro_stock_available'];

				if( !strlen( $in_stock ) || ($in_stock < 0) )
					$in_stock = 0;

				if( $in_stock )
					query( "update shopsystem_product_extended_options set pro_stock_available = pro_stock_available + ($Qty) where pro_pr_id = $pr_id" );
				else
				{
					query( "update shopsystem_product_extended_options set pro_stock_available = $Qty where pro_pr_id = $pr_id" );
					@query( "insert into lastest_product_additions (la_pr_id, la_pr_sales_zone) values ({$pr_row['pr_id']}, {$pr_row['pr_sales_zone']})" );
				}

				ss_audit( 'update', 'Products', $pr_id, "Edit of invoice $key adds $Qty to stock, from $in_stock" );
			}
		}
	}

	function saveUnavailableAlterations( $alterations, $key )
	{
//		return;
		ss_log_message( "Unavailable Product Qty Alterations" );
		ss_log_message_r(  'Log:'.__FILE__.':'.__LINE__, $alterations );
		foreach( $alterations as $pr_id => $Qty )
		{
			if( strlen( $Qty ) )
			{
				query( "update shopsystem_product_extended_options set pro_stock_unavailable = pro_stock_unavailable + ($Qty) where pro_pr_id = $pr_id" );
				$in_stock = getField( "select pro_stock_unavailable from shopsystem_product_extended_options where  pro_pr_id = $pr_id" );
				ss_audit( 'update', 'Products', $pr_id, "Edit of invoice $key adds $Qty to unavailable stock, from $in_stock" );
			}
		}
	}

	function saveSKU( $skus, $sp_id )
	{
		ss_log_message( "Product SKU Saves" );
		ss_log_message_r(  'Log:'.__FILE__.':'.__LINE__, $skus );
		foreach( $skus as $sku => $pr_id )
		{
			query( "delete from supplier_sku_lookup where skl_pr_id = $pr_id and skl_sp_id = $sp_id" );
			query( "insert into supplier_sku_lookup (skl_sp_id, skl_sku, skl_pr_id) values ($sp_id, '".escape( $sku )."', $pr_id)" );
		}
	}

	$errors = array();
	if (array_key_exists('DoAction',$this->ATTRIBUTES))
	{
		// We're writing to the database, so must load each field
		// with the value receieved from the form

		$this->loadFieldValuesFromForm($this->ATTRIBUTES);

		ss_log_message( "Edit/save this struct" );
		ss_log_message_r(  'Log:'.__FILE__.':'.__LINE__, $this );

		// Write to the database
		$errors = $this->update();

		// Return if no error messages were returned
		if (count($errors) == 0) {
			// Return (to the list of records hopefully)
			//rfaReturn();
			//location($this->ATTRIBUTES['RFA']);			
			// save linked table edits

			// $this->
			//    [linkedTables] => Array //(
           	//	[0] => LinkedTable Object
             //   (
             //       [tableName] => supplier_invoice_line
            //        [ourKey] => sil_sin_id
             //   ) //		)
			//    [ATTRIBUTES] => Array
			//        (
			//            [act] => SupplierInvoicesAdministration.Edit
			//            [sin_sp_id] => 1
			//            [sin_invoice_number] => Initial population
			//            [sin_date] => 2017-09-25
			//            [sin_currency] => EUR
			//            [sin_discount] => 0.00
			//            [sil_box_code0] => 
			//            [sil_supplier_sku0] => 
			//            [sil_description0] => Davidoff Millennium Blend Petit Corona (5)
			//            [sil_pr_id0] => 5598
			//            [sil_qty0] => 9
			//            [sil_raw_line_cost0] => 152.28
			//            [sil_computed_cost0] => 16.92
			//            [sil_box_code1] => 
			//            [sil_supplier_sku1] => 
			//            [sil_description1] => Davidoff Millennium Blend Robusto Tubos (20)
			//            [sil_pr_id1] => 5772
			//            [sil_qty1] => 49
			//            [sil_raw_line_cost1] => 5603.15
			//            [sil_computed_cost1] => 114.35
			//            [sil_box_code2] => 
			//            [sil_supplier_sku2] => 
			//            [sil_description2] => Davidoff Escurio Gran Perfecto (12)
			//            [sil_pr_id2] => 6258
			//            [sil_qty2] => 54
			//            [sil_raw_line_cost2] => 2997.00
			//            [sil_computed_cost2] => 55.50
			// 

			foreach( $this->linkedTables as $linkedTable )
			{
				$productQtyAlterations = array();
				$productUnavailableAlterations = array();
				$productSKU = array();
				$sp_id = $this->fields['sin_sp_id']->value;

				$columns = array();
				if( $result = query("SHOW COLUMNS FROM {$linkedTable->tableName}") )
					while( $row = $result->fetchRow() )
						$columns[] = $row;
				// for i = 1 until !array_key_exists( $this->ATTRIBUTES[$colname.$i] )
				// iterate over column names
				for( $i = 0; $i < 500; $i++ )
				{
					$saveRow = array();

					if( !array_key_exists( $linkedTable->uniqueID.$i, $this->ATTRIBUTES ) )
					{
							saveSKU( $productSKU, $sp_id );
							saveQtyAlterations( $productQtyAlterations, $this->primaryKey );
							saveUnavailableAlterations( $productUnavailableAlterations, $this->primaryKey );
							location($this->ATTRIBUTES['BackURL']);
					}

					foreach( $columns as $column )
						if( array_key_exists( $column['Field'].$i, $this->ATTRIBUTES ) )
							$saveRow[$column['Field']] = $this->ATTRIBUTES[$column['Field'].$i];
						else
							ss_log_message( "ATTRIBUTE {$column['Field']}$i doesn't exist, skipping" );

					if( strlen( $saveRow['sil_supplier_sku'] ) && ($saveRow['sil_pr_id'] > 0) )
						$productSKU[$saveRow['sil_supplier_sku']] = $saveRow['sil_pr_id'];

					if( $saveRow[$linkedTable->uniqueID] )
					{
						$oldValues = GetRow( "select sil_pr_id, sil_qty_received, sil_qty_put_in_stock from  {$linkedTable->tableName}  where {$linkedTable->uniqueID} = '{$saveRow[$linkedTable->uniqueID]}'" );

						if( $oldValues['sil_pr_id'] != $saveRow['sil_pr_id'] )
						{
							// swapped out the product

//							ss_log_message( "ID alteration 1 {$oldValues['sil_pr_id']} != {$saveRow['sil_pr_id']}" );

							if( array_key_exists( $oldValues['sil_pr_id'], $productQtyAlterations ) )
								$productQtyAlterations[$oldValues['sil_pr_id']] -= $oldValues['sil_qty_put_in_stock'];
							else
								$productQtyAlterations[$oldValues['sil_pr_id']] = -$oldValues['sil_qty_put_in_stock'];

							if( array_key_exists( $saveRow['sil_pr_id'], $productQtyAlterations ) )
								$productQtyAlterations[$saveRow['sil_pr_id']] += $saveRow['sil_qty_put_in_stock'];
							else
								$productQtyAlterations[$saveRow['sil_pr_id']] = $saveRow['sil_qty_put_in_stock'];

							if( array_key_exists( $oldValues['sil_pr_id'], $productUnavailableAlterations ) )
								$productUnavailableAlterations[$oldValues['sil_pr_id']] -= ($oldValues['sil_qty_received'] - $oldValues['sil_qty_put_in_stock']);
							else
								$productUnavailableAlterations[$oldValues['sil_pr_id']] = -($oldValues['sil_qty_received'] - $oldValues['sil_qty_put_in_stock']);

							if( array_key_exists( $saveRow['sil_pr_id'], $productUnavailableAlterations ) )
								$productUnavailableAlterations[$saveRow['sil_pr_id']] += ($saveRow['sil_qty_received'] - $saveRow['sil_qty_put_in_stock']);
							else
								$productUnavailableAlterations[$saveRow['sil_pr_id']] = ($saveRow['sil_qty_received'] - $saveRow['sil_qty_put_in_stock']);

						}
						else
						{

							if( $oldValues['sil_qty_put_in_stock'] != $saveRow['sil_qty_put_in_stock'] )
							{
								// altered the qty of the same product
								if( array_key_exists( $saveRow['sil_pr_id'], $productQtyAlterations ) )
									$productQtyAlterations[$saveRow['sil_pr_id']] += $saveRow['sil_qty_put_in_stock'] - $oldValues['sil_qty_put_in_stock'];
								else
									$productQtyAlterations[$saveRow['sil_pr_id']] = $saveRow['sil_qty_put_in_stock'] - $oldValues['sil_qty_put_in_stock'];
							}

							if( ($oldValues['sil_qty_received'] - $oldValues['sil_qty_put_in_stock']) != ($saveRow['sil_qty_received'] - $saveRow['sil_qty_put_in_stock']) )
							{
								// altered the qty of the same product
								if( array_key_exists( $saveRow['sil_pr_id'], $productUnavailableAlterations ) )
									$productUnavailableAlterations[$saveRow['sil_pr_id']] += ($saveRow['sil_qty_received'] - $saveRow['sil_qty_put_in_stock']) - ($oldValues['sil_qty_received'] - $oldValues['sil_qty_put_in_stock']);
								else
									$productUnavailableAlterations[$saveRow['sil_pr_id']] = ($saveRow['sil_qty_received'] - $saveRow['sil_qty_put_in_stock']) - ($oldValues['sil_qty_received'] - $oldValues['sil_qty_put_in_stock']);
							}

						}

						// update row
//						ss_log_message( "Update Row" );
//						ss_log_message_r(  'Log:'.__FILE__.':'.__LINE__, $saveRow );
						$qry = "update {$linkedTable->tableName} set ";
						$ac = false;
						foreach( $saveRow as $col=>$val )
							if( $col != $linkedTable->uniqueID )
							{
								if( $ac )
									$qry .= ', ';
								else
									$ac = true;
								$qry .= "$col = '$val'";
							}
						$qry .= " where {$linkedTable->uniqueID} = '{$saveRow[$linkedTable->uniqueID]}'";

					}
					else
					{

						if( array_key_exists( $saveRow['sil_pr_id'], $productQtyAlterations ) )
							$productQtyAlterations[$saveRow['sil_pr_id']] += $saveRow['sil_qty_put_in_stock'];
						else
							$productQtyAlterations[$saveRow['sil_pr_id']] = $saveRow['sil_qty_put_in_stock'];

						if( array_key_exists( $saveRow['sil_pr_id'], $productUnavailableAlterations ) )
							$productUnavailableAlterations[$saveRow['sil_pr_id']] += ($saveRow['sil_qty_received'] - $saveRow['sil_qty_put_in_stock']);
						else
							$productUnavailableAlterations[$saveRow['sil_pr_id']] = ($saveRow['sil_qty_received'] - $saveRow['sil_qty_put_in_stock']);

						// insert new row
//						ss_log_message( "Insert Row" );
//						ss_log_message_r(  'Log:'.__FILE__.':'.__LINE__, $saveRow );
						$qry = "insert into {$linkedTable->tableName} (";

						$ac = false;
						foreach( $saveRow as $col=>$val )
							if( $col != $linkedTable->uniqueID )
							{
								if( $ac )
									$qry .= ', ';
								else
									$ac = true;
								$qry .= "$col";
							}

						$qry .= " ) values ( ";

						$ac = false;
						foreach( $saveRow as $col=>$val )
							if( $col != $linkedTable->uniqueID )
							{
								if( $ac )
									$qry .= ', ';
								else
									$ac = true;

								if( $col == $linkedTable->ourKey )
									$qry .= "'{$this->primaryKey}'";
								else
									$qry .= "'$val'";
							}

						$qry .= " )";

					}
					ss_log_message( "SQL $qry" );					
					query( $qry );
				}
			}

			saveSKU( $productSKU, $sp_id );
			saveQtyAlterations( $productQtyAlterations, $this->primaryKey );
			saveUnavailableAlterations( $productUnavailableAlterations, $this->primaryKey );
			location($this->ATTRIBUTES['BackURL']);
		}
	}
?>
