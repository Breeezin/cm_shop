<?php

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
					query( "update shopsystem_product_extended_options set pro_stock_available = pro_stock_available - ($Qty) where pro_pr_id = $pr_id" );

				// need to allocate this stock to a supplier invoice

				ss_audit( 'update', 'Products', $pr_id, "Edit of customer invoice $key removes $Qty to stock, from $in_stock" );


				for( $i = 0; $i < $Qty; $i++ )
				{
					// find out which supplier invoice this is from  // really we'd like to pick up the barcode here.
					$sil_id = (int)getField( "select min(sil_id) as thisBox from supplier_invoice_line where sil_pr_id = $pr_id and sil_shipped_count < sil_qty" );

					if( $sil_id )
					{
						ss_log_message( "Assigning this box (pr_id:$pr_id) to invoice item line $sil_id" );

						query( "replace into customer_invoice_shipment
								set cis_cin_id = $key, cis_sil_id = $sil_id" );

						$sold = getField( "select count(*) from shopsystem_order_sheets_items where orsi_sil_id = $sil_id" )
							  + getField( "select count(*) from customer_invoice_shipment where cis_sil_id = $sil_id" );

						ss_log_message( "update supplier_invoice_line set sil_shipped_count = $sold where sil_id = $sil_id" );
						query( "update supplier_invoice_line set sil_shipped_count = $sold where sil_id = $sil_id" );
					}
					else
						ss_log_message( "No invoice item line found (pr_id:$pr_id).  Where did this box come from?" );
				}
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
				query( "update shopsystem_product_extended_options set pro_stock_unavailable = pro_stock_unavailable - ($Qty) where pro_pr_id = $pr_id" );
				$in_stock = getField( "select pro_stock_unavailable from shopsystem_product_extended_options where  pro_pr_id = $pr_id" );
				ss_audit( 'update', 'Products', $pr_id, "Edit of customer invoice $key removes $Qty to UNAVAILABLE stock, from $in_stock" );
			}
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


			foreach( $this->linkedTables as $linkedTable )
			{
				$productQtyAlterations = array();
				$productUnavailableAlterations = array();
				$sp_id = $this->fields['cin_cp_id']->value;

				$columns = array();
				if( $result = query("SHOW COLUMNS FROM {$linkedTable->tableName}") )
					while( $row = $result->fetchRow() )
						$columns[] = $row;
				// for i = 1 until !array_key_exists( $this->ATTRIBUTES[$colname.$i] )
				// iterate over column names
				for( $i = 0; $i < 500; $i++ )
				{
					$saveRow = array();

					// run off the end of the array, save and terminate loop.
					if( !array_key_exists( $linkedTable->uniqueID.$i, $this->ATTRIBUTES ) )
					{
						if( $this->fields['cin_invoice_finished']->value == "'true'" )
						{
							ss_log_message( "save qtys" );
							saveQtyAlterations( $productQtyAlterations, $this->primaryKey );
							saveUnavailableAlterations( $productUnavailableAlterations, $this->primaryKey );
						}
						else
							ss_log_message( "cust inv not completed, not altering qtys ".$this->fields['cin_invoice_finished']->value );

						location($this->ATTRIBUTES['BackURL']);
					}

					foreach( $columns as $column )
						if( array_key_exists( $column['Field'].$i, $this->ATTRIBUTES ) )
							$saveRow[$column['Field']] = $this->ATTRIBUTES[$column['Field'].$i];
						else
							ss_log_message( "ATTRIBUTE {$column['Field']}$i doesn't exist, skipping" );

					if( $saveRow[$linkedTable->uniqueID] )
					{
						if( $saveRow['cil_qty_from_available'] > 0 )
						{
							// altered the qty of the same product
							if( array_key_exists( $saveRow['cil_pr_id'], $productQtyAlterations ) )
								$productQtyAlterations[$saveRow['cil_pr_id']] += $saveRow['cil_qty_from_available'];
							else
								$productQtyAlterations[$saveRow['cil_pr_id']] = $saveRow['cil_qty_from_available'];
						}

						if( $saveRow['cil_qty_from_unavailable'] > 0 )
						{
							// altered the qty of the same product
							if( array_key_exists( $saveRow['cil_pr_id'], $productUnavailableAlterations ) )
								$productUnavailableAlterations[$saveRow['cil_pr_id']] += $saveRow['cil_qty_from_unavailable'];
							else
								$productUnavailableAlterations[$saveRow['cil_pr_id']] = $saveRow['cil_qty_from_unavailable'];
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

						if( array_key_exists( $saveRow['cil_pr_id'], $productQtyAlterations ) )
							$productQtyAlterations[$saveRow['cil_pr_id']] += $saveRow['cil_qty_from_available'];
						else
							$productQtyAlterations[$saveRow['cil_pr_id']] = $saveRow['cil_qty_from_available'];

						if( array_key_exists( $saveRow['cil_pr_id'], $productUnavailableAlterations ) )
							$productUnavailableAlterations[$saveRow['cil_pr_id']] += $saveRow['cil_qty_from_unavailable'];
						else
							$productUnavailableAlterations[$saveRow['cil_pr_id']] = $saveRow['cil_qty_from_unavailable'];

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

			if( $this->fields['cin_invoice_finished']->value == "'true'" )
			{
				saveQtyAlterations( $productQtyAlterations, $this->primaryKey );
				saveUnavailableAlterations( $productUnavailableAlterations, $this->primaryKey );
			}
			else
				ss_log_message( "cust inv not completed, not altering qtys ".$this->fields['cin_invoice_finished']->value );
			location($this->ATTRIBUTES['BackURL']);
		}
	}
?>
