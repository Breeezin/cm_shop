<?php
	$errors = array();
	if (array_key_exists('DoAction',$this->ATTRIBUTES)) {
	
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
				$columns = array();
				if( $result = query("SHOW COLUMNS FROM {$linkedTable->tableName}") )
					while( $row = $result->fetchRow() )
						$columns[] = $row;
				// for i = 1 until !array_key_exists( $this->ATTRIBUTES[$colname.$i] )
				// iterate over column names
				for( $i = 0; $i < 1000; $i++ )
				{
					$saveRow = array();

					foreach( $columns as $column )
					{
						if( !array_key_exists( $column['Field'].$i, $this->ATTRIBUTES ) )
							location($this->ATTRIBUTES['BackURL']);

						$saveRow[$column['Field']] = $this->ATTRIBUTES[$column['Field'].$i];
					}

					if( $saveRow[$linkedTable->uniqueID] )
					{
						// update row
						ss_log_message( "Update Row" );
						ss_log_message_r(  'Log:'.__FILE__.':'.__LINE__, $saveRow );
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
						// insert new row
						ss_log_message( "Insert Row" );
						ss_log_message_r(  'Log:'.__FILE__.':'.__LINE__, $saveRow );
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
								$qry .= "'$val'";
							}

						$qry .= " )";

					}
					ss_log_message( "SQL $qry" );					
					query( $qry );
				}
			}

			location($this->ATTRIBUTES['BackURL']);
		}
	}
?>
