<?php

	$this->param('ors_id');

	if( count($_POST) > 0 )
	{
		foreach($_POST as $name=>$val )
		{
			if( $pos = strpos( $name, '_' ) )
			{
				if( strlen( $val ) )
				{
					$a = substr( $name, 0, $pos );
					$p = (int)substr( $name, $pos+1 );
					echo "Operation:$a PID:$p=$val<br/>";

					if( !strcmp( $a, "remove" ) )
						query ("update shopsystem_products set pr_location = NULL where pr_id = $p" );

					if( !strcmp( $a, "newLocations" ) )
					{
						$nl = '';
						$ne = array();
						for( $i = 0; $i < strlen( $val ); $i++)
						{
							if( strspn( $val[$i], "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz" ) )
								$nl .= $val[$i];
							else
							{
								if( strlen( $nl ) )
									$ne[] = $nl;
								$nl = '';
							}
						}
						if( strlen( $nl ) )
							$ne[] = $nl;

						if( count( $ne ) )
						{
							$cv = getField( "select pr_location from shopsystem_products where pr_id = $p" );
							if( $cv && strlen( $cv ) )
								$nv = $cv.', '.implode( ',', $ne );
							else
								$nv = implode( ',', $ne );
							query( "update shopsystem_products set pr_location = '$nv' where pr_id = $p" );
						}
					}
				}
			}
		}
	}

	$sheet = (int) $this->ATTRIBUTES['ors_id'];
	if( $sheet > 0 )
	{
		$Q_OrderSheetItems = query("
			SELECT DISTINCT pr_id, orsi_stock_code, orsi_pr_name, 
				pr_location
			FROM shopsystem_order_sheets_items, shopsystem_orders, shopsystem_product_extended_options, shopsystem_products
			WHERE orsi_ors_id = ".safe($this->ATTRIBUTES['ors_id'])."
			  AND orsi_or_id = or_id
			  and pr_id = pro_pr_id
			  and pro_stock_code = orsi_stock_code
			  and or_cancelled IS NULL
			  and or_standby IS NULL
			ORDER BY pr_location, orsi_stock_code
		");
	}
	else
	{
		$Q_OrderSheetItems = query("
			SELECT pr_id, pro_stock_code as orsi_stock_code, pr_name as orsi_pr_name, 
				pr_location
			FROM shopsystem_product_extended_options, shopsystem_products
			WHERE pr_id = pro_pr_id
			  and pr_ve_id = 2
			  and pr_offline IS NULL
			  and pr_deleted IS NULL
			ORDER BY pr_location, pro_stock_code
		");
	}
?>
