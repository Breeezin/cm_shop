<?php

echo "Please use new dashboard";
die;

function getBothRows( $qry, $this_year )
{

		$a = getRow( $qry . " AND or_archive_year IS NULL" );

		if( $this_year > 0 )
		{
			$b = getRow( $qry . " AND or_archive_year = ".$this_year );

			return array( 'TheValue' => ($a['TheValue']+$b['TheValue']) );
		}
		else
			return $a;
}

	// figure out some useful dates

	$this->param('Today',date('d/m/Y',time()),date('d/m/Y',time()));
	
	if (date_error($this->atts['Today']) !== null) {
		$this->atts['Today'] = date('d/m/Y',time());
	}
	$today = mktime(0,0,0,ListGetAt($this->atts['Today'],2,'/'),ListGetAt($this->atts['Today'],1,'/'),ss_AdjustTwoDigitYear(ListGetAt($this->atts['Today'],3,'/')));				
	
	$dates = array();

	$dates['Today'] = array(
		'start'	=> mktime(0,0,0,date('m',$today),date('d',$today),date('Y',$today)),
		'end'	=> mktime(0,0,-1,date('m',$today),date('d',$today)+1,date('Y',$today)),
	);
	
	$dates['This Week'] = array(
		'start'	=> mktime(0,0,0,date('m'),date('d')-date('w'),date('Y')),
		'end'	=> mktime(0,0,-1,date('m'),date('d')-date('w')+7,date('Y')),
	);
	
	$dates['This Month'] = array(
		'start'	=> mktime(0,0,0,date('m'),1,date('Y')),
		'end'	=> mktime(0,0,-1,date('m')+1,1,date('Y')),
	);

	$this_year = date( 'Y' );

/*
	$dates['This Year'] = array(
		'start'	=> mktime(0,0,0,1,1,date('Y')),
		'end'	=> mktime(0,0,-1,1,1,date('Y')+1),
	);
*/

	$compare = false;
	if (array_key_exists('from_start',$this->atts)) {
		
		if (date_error($this->atts['from_start']) or date_error($this->atts['from_end'])
				or date_error($this->atts['to_start']) or date_error($this->atts['to_end'])) {
					
			$error = "Please check all comparison dates are valid.";			
					
				
		} else {

			$from = $this->atts['from_start'].' - '.$this->atts['from_end'];
			$to = $this->atts['to_start'].' - '.$this->atts['to_end'];
			
			$compare = true;
			$dates[$from] = array(
				'start'	=> mktime(0,0,0,ListGetAt($this->atts['from_start'],2,'/'),ListGetAt($this->atts['from_start'],1,'/'),ss_AdjustTwoDigitYear(ListGetAt($this->atts['from_start'],3,'/'))),
				'end'	=> mktime(23,59,59,ListGetAt($this->atts['from_end'],2,'/'),ListGetAt($this->atts['from_end'],1,'/'),ss_AdjustTwoDigitYear(ListGetAt($this->atts['from_end'],3,'/'))),
			);
			
			$dates[$to] = array(
				'start'	=> mktime(0,0,0,ListGetAt($this->atts['to_start'],2,'/'),ListGetAt($this->atts['to_start'],1,'/'),ss_AdjustTwoDigitYear(ListGetAt($this->atts['to_start'],3,'/'))),
				'end'	=> mktime(23,59,59,ListGetAt($this->atts['to_end'],2,'/'),ListGetAt($this->atts['to_end'],1,'/'),ss_AdjustTwoDigitYear(ListGetAt($this->atts['to_end'],3,'/'))),
			);
			$this_year = -1;
		}
	}

	
	$MaxWarehouse = getField("
		SELECT MAX(WaStID) AS TheMax FROM ShopSystem_WarehouseStock
	");
	$WarehouseStock = getRow("
		SELECT * FROM ShopSystem_WarehouseStock WHERE WaStID = $MaxWarehouse
	");
	
	/*// calculate stock value based on average value of products in last 20 supplier order sheets
	$Q_Last20SupplierSheets = query("
		SELECT sos_id, sos_total, SUM(soit_qty) AS Qty FROM shopsystem_supplier_order_sheets, shopsystem_supplier_order_sheets_items
		WHERE sos_id = soit_sos_id
		GROUP BY sos_id, sos_total
		ORDER BY sos_id DESC
		LIMIT 20
	");
	$total = 0;
	while ($row = $Q_Last20SupplierSheets->fetchRow()) {
		if ($row['Qty'] > 0) {
			$total += $row['sos_total']/$row['Qty'];
		}
	}
	$averageSupplierOrderSheetStockPrice = $total/$Q_Last20SupplierSheets->numRows();
	ss_DumpVar($averageSupplierOrderSheetStockPrice,'average suppliers price');
	
	// calculate the average stock value based on the sales price last 25 orders
	$Q_Last25Orders = query("
		SELECT or_id, SUM(Price*Quantity) AS OrderTotal, SUM(Quantity) AS Qty FROM ShopSystem_AcmeOrderProducts, shopsystem_orders
		WHERE or_id = OrderLink
		GROUP BY or_id
		LIMIT 25
	");
	$total = 0;
	while ($row = $Q_Last25Orders->fetchRow()) {
		if ($row['Qty'] > 0) {
			$total += $row['OrderTotal']/$row['Qty'];
		}
	}
	$averageOrdersSalePrice = $total/$Q_Last25Orders->numRows();
	ss_DumpVar($averageOrdersSalePrice,'average orders sale price');
	*/
	
	
	$Q_Missing = query("
		SELECT pr_id, pr_name, pro_stock_code, pr_stock_warning_level, pro_stock_available, 
			pro_stock_available-pr_stock_warning_level as ShortFall 
		FROM shopsystem_products LEFT JOIN shopsystem_product_extended_options on pr_id = pro_pr_id
		WHERE pr_offline IS NULL
		  and pr_stock_warning_level IS NOT NULL
		ORDER by 5 asc 
		LIMIT 100
	");
	
	
	// number of people in newsletter list, VIPs, wish list, auto stock update
	$Q_PeopleCounter = query("
		SELECT ug_name, COUNT(*) AS People FROM user_user_groups, users, user_groups
		WHERE us_id = uug_us_id
			AND ug_id = uug_ug_id
			AND ug_id NOT IN (1,2)
		GROUP BY uug_ug_id
	");
	
	// number of clients.
	$res = getRow("
		SELECT COUNT(DISTINCT or_us_id) AS DistinctCustomers FROM shopsystem_orders
	");
	$distinctCustomers = $res['DistinctCustomers'];
	
	$wishListCustomers = getRow("
		SELECT COUNT(DISTINCT StNouug_us_id) AS TheValue FROM shopsystem_stock_notifications
	");
	$wishListCustomers = $wishListCustomers['TheValue'];
	
	// % who are repetitive
	$Q_CustomerOrderCounts = query("
		SELECT or_us_id, COUNT(*) AS OrderCount FROM shopsystem_orders
		GROUP BY or_us_id
	");
	$repeatCustomers = 0;
	while ($row = $Q_CustomerOrderCounts->fetchRow()) {
		if ($row['OrderCount'] > 1) $repeatCustomers++;
	}
	
	
	$averageShipping = getField("
		SELECT AVG(shp_days_since_ordered) AS AverageDays FROM shopsystem_shipped_products
		WHERE shp_days_since_ordered IS NOT NULL
	");
	
	$debts = array('Sotabac'=>array(),'Correos'=>array());

	$bank = getRow("
		SELECT BaBaAmount FROM ShopSystem_BankBalances
		ORDER BY BaBaID DESC
		LIMIT 1
	");
		
	
	// debt. how much we owe suppliers
	$res = getRow("
		SELECT SUM(sos_total) AS Owe FROM shopsystem_supplier_order_sheets
		WHERE sos_paid IS NULL 
	");
	$debts['Sotabac']['Total Debt'] = $res['Owe'];
	$res = getRow("
		SELECT COUNT(*) AS CountOwe FROM shopsystem_supplier_order_sheets
		WHERE sos_paid IS NULL 
	");
	$debts['Sotabac']['Number of Unpaid Orders'] = $res['CountOwe'];
	$res = getRow("
		SELECT MIN(sos_date) AS OweOld FROM shopsystem_supplier_order_sheets
		WHERE sos_paid IS NULL 
	");
	if ($res['OweOld'] !== null) {
		$debts['Sotabac']['Age of Oldest Unpaid Order'] = round((time()-ss_sqlToTimeStamp($res['OweOld']))/(60*60*24));
	} else {
		$debts['Sotabac']['Age of Oldest Unpaid Order'] = 0;	
	}


	
	// debt. how much we ow postal
	$res = getRow("
		SELECT SUM(ssc_amount) AS Owe FROM shopsystem_shipping_charges
		WHERE ssc_paid IS NULL 
	");
	$debts['Correos']['Total Debt'] = $res['Owe'];
	$res = getRow("
		SELECT COUNT(*) AS CountOwe FROM shopsystem_shipping_charges
		WHERE ssc_paid IS NULL 
	");
	$debts['Correos']['Number of Unpaid Orders'] = $res['CountOwe'];
	$res = getRow("
		SELECT MIN(ssc_date) AS OweOld FROM shopsystem_shipping_charges
		WHERE ssc_paid IS NULL 
	");
	if ($res['OweOld'] !== null) {
		$debts['Correos']['Age of Oldest Unpaid Order'] = round((time()-ss_sqlToTimeStamp($res['OweOld']))/(60*60*24));
	} else {
		$debts['Correos']['Age of Oldest Unpaid Order'] = 0;	
	}

	
	
	
	
	// number of boxes shipped - year, month, week, today
	$values = array(
		'Number of Boxes Shipped' => array(),
		'Average Shipment Value' => array(),
		'Total Purchases' => array(),
		'Total Revenue' => array(),
		'Product Revenue' => array(),
		'CM' => array(),
		'CM%' => array(),
		'Shipping' => array(),
		'Number of Reshipment Boxes'	=>	array(),
		'Reshipment Values'	=>	array(),
		'Refunds'	=>	array(),
	);
	
	foreach($dates as $key => $range) {
		$values['Number of Boxes Shipped'][$key] = getRow("
			SELECT COUNT(*) AS TheValue FROM shopsystem_shipped_products
			WHERE shp_date BETWEEN ".ss_TimeStampToSQL($range['start'])." AND ".ss_TimeStampToSQL($range['end'])
			);
		$values['Average Shipment Value'][$key] = getRow("
			SELECT AVG(in_total_value) AS TheValue FROM shopsystem_transit_documents, shopsystem_invoices
			WHERE TrDoLasPalmasdeGCFecha BETWEEN ".ss_TimeStampToSQL($range['start'])." AND ".ss_TimeStampToSQL($range['end'])."
				AND TrDoInvoiceLink = inv_id
		");
		$values['Total Purchases'][$key] = getRow("
			SELECT SUM(sos_total) AS TheValue FROM shopsystem_supplier_order_sheets
			WHERE sos_date BETWEEN ".ss_TimeStampToSQL($range['start'])." AND ".ss_TimeStampToSQL($range['end'])."
		");
//		$values['Profit'][$key] = getRow("
//			SELECT SUM( or_profit ) AS TheValue FROM `shopsystem_orders`
//			WHERE or_recorded  BETWEEN ".ss_TimeStampToSQL($range['start'])." AND ".ss_TimeStampToSQL($range['end'])."
//		");
		if( $this_year > 0 )
		{

			$count = getRow( "select count(*) as rows from shopsystem_orders join transactions on or_tr_id = tr_id where or_archive_year = $this_year"
					. " AND or_card_denied IS NULL AND or_cancelled IS NULL"
					. " AND tr_timestamp BETWEEN ".ss_TimeStampToSQL($range['start'])." AND ".ss_TimeStampToSQL($range['end']) );

			if( $count['rows'] == 0 )
				$cyear = -1;
			else
				$cyear = $this_year;

			$values['CM'][$key] = getBothRows("
				SELECT SUM( or_profit )*0.99 AS TheValue FROM shopsystem_orders join transactions on or_tr_id = tr_id
				WHERE tr_charge_total IS NOT NULL
					AND or_card_denied IS NULL AND or_cancelled IS NULL
					AND tr_completed = 1
					AND tr_status_link < 3
					AND tr_timestamp BETWEEN ".ss_TimeStampToSQL($range['start'])." AND ".ss_TimeStampToSQL($range['end']),
				$cyear
			);
			$values['Shipping'][$key] = getBothRows("select sum( tr_incl_shipping + tr_excl_shipping ) as TheValue
				FROM shopsystem_orders
				JOIN transactions on or_tr_id = tr_id 
				WHERE tr_completed = 1
					AND or_card_denied IS NULL AND or_cancelled IS NULL
					AND tr_status_link < 3
					AND tr_timestamp BETWEEN ".ss_TimeStampToSQL($range['start'])." AND ".ss_TimeStampToSQL($range['end']),
				$cyear
			);

/*
			$values['Shipping'][$key] = getBothRows("select sum(orpr_price) - sum(orpr_qty*IF( pro_special_price, pro_special_price, pro_price) ) as TheValue
				FROM shopsystem_orders
				JOIN transactions on or_tr_id = tr_id 
				JOIN shopsystem_order_products ON or_id = orpr_or_id 
				JOIN shopsystem_products ON pr_id = orpr_pr_id 
				JOIN shopsystem_product_extended_options ON pr_id = pro_pr_id
				WHERE tr_completed = 1
					AND tr_status_link < 3
					AND tr_timestamp BETWEEN ".ss_TimeStampToSQL($range['start'])." AND ".ss_TimeStampToSQL($range['end']),
				$cyear
			);
*/
			$values['Total Revenue'][$key] = getBothRows("
				SELECT SUM( tr_total ) AS TheValue FROM shopsystem_orders join transactions on or_tr_id = tr_id
				WHERE tr_charge_total IS NOT NULL
					AND tr_completed = 1
					AND or_card_denied IS NULL AND or_cancelled IS NULL
					AND tr_status_link < 3
					AND tr_timestamp BETWEEN ".ss_TimeStampToSQL($range['start'])." AND ".ss_TimeStampToSQL($range['end']),
				$cyear
			);

			$values['Number of Reshipment Boxes'][$key] = getBothRows("
				SELECT COUNT(*) AS TheValue FROM shopsystem_orders join shopsystem_shipped_products on shp_or_id = or_id
				WHERE shp_date BETWEEN ".ss_TimeStampToSQL($range['start'])." AND ".ss_TimeStampToSQL($range['end'])."
					AND or_card_denied IS NULL AND or_cancelled IS NULL
					AND or_reshipment IS NOT NULL",
				$this_year
			);
			$values['Reshipment Values'][$key] = getBothRows("
				SELECT SUM(in_total_value) AS TheValue FROM shopsystem_orders join shopsystem_invoices on in_or_id = or_id join shopsystem_transit_documents on TrDoInvoiceLink = inv_id
				WHERE TrDoLasPalmasdeGCFecha BETWEEN ".ss_TimeStampToSQL($range['start'])." AND ".ss_TimeStampToSQL($range['end'])."
					AND or_reshipment IS NOT NULL",
				$this_year
			);		

			$values['Refunds'][$key] = getBothRows("
				SELECT SUM(rfd_amount) AS TheValue FROM shopsystem_orders join shopsystem_refunds on rfd_or_id = or_id
				WHERE rfd_timestamp BETWEEN ".ss_TimeStampToSQL($range['start'])
				." AND ".ss_TimeStampToSQL($range['end']),
				$this_year
			);		

		}
		else
		{
//				    AND (or_paid IS NOT NULL OR or_paid_not_shipped IS NOT NULL)

			$values['CM'][$key] = getRow("
				SELECT SUM( or_profit )*0.99 AS TheValue FROM shopsystem_orders join transactions on or_tr_id = tr_id
				WHERE tr_charge_total IS NOT NULL
					AND or_card_denied IS NULL AND or_cancelled IS NULL
					AND tr_completed = 1
					AND tr_status_link < 3
					AND tr_timestamp BETWEEN ".ss_TimeStampToSQL($range['start'])." AND ".ss_TimeStampToSQL($range['end'])
			);
			$values['Shipping'][$key] = getRow("select sum( tr_incl_shipping + tr_excl_shipping ) as TheValue
			FROM shopsystem_orders
			JOIN transactions on or_tr_id = tr_id 
			WHERE tr_completed = 1
				AND or_card_denied IS NULL AND or_cancelled IS NULL
				AND tr_status_link < 3
				AND tr_timestamp BETWEEN ".ss_TimeStampToSQL($range['start'])." AND ".ss_TimeStampToSQL($range['end'])
			.(($this_year > 0) ? " AND (or_archive_year IS NULL OR or_archive_year = ".$this_year." )":"")
			);
/*
			$values['Shipping'][$key] = getRow("select sum(orpr_price) - sum(orpr_qty*IF( pro_special_price, pro_special_price, pro_price) ) as TheValue
				FROM shopsystem_orders
				JOIN transactions on or_tr_id = tr_id 
				JOIN shopsystem_order_products ON or_id = orpr_or_id 
				JOIN shopsystem_products ON pr_id = orpr_pr_id 
				JOIN shopsystem_product_extended_options ON pr_id = pro_pr_id
				WHERE tr_completed = 1
					AND tr_status_link < 3
					AND tr_timestamp BETWEEN ".ss_TimeStampToSQL($range['start'])." AND ".ss_TimeStampToSQL($range['end'])
			);
*/
			$values['Total Revenue'][$key] = getRow("
				SELECT SUM( tr_total ) AS TheValue FROM shopsystem_orders join transactions on or_tr_id = tr_id
				WHERE tr_charge_total IS NOT NULL
					AND tr_completed = 1
					AND or_card_denied IS NULL AND or_cancelled IS NULL
					AND tr_status_link < 3
					AND tr_timestamp BETWEEN ".ss_TimeStampToSQL($range['start'])." AND ".ss_TimeStampToSQL($range['end'])
			);

			$values['Number of Reshipment Boxes'][$key] = getRow("
				SELECT COUNT(*) AS TheValue FROM shopsystem_orders join shopsystem_shipped_products on shp_or_id = or_id
				WHERE shp_date BETWEEN ".ss_TimeStampToSQL($range['start'])." AND ".ss_TimeStampToSQL($range['end'])."
					AND or_card_denied IS NULL AND or_cancelled IS NULL
					AND or_reshipment IS NOT NULL"
			);
			$values['Reshipment Values'][$key] = getRow("
				SELECT SUM(in_total_value) AS TheValue FROM shopsystem_orders join shopsystem_invoices on in_or_id = or_id join shopsystem_transit_documents on TrDoInvoiceLink = inv_id
				WHERE TrDoLasPalmasdeGCFecha BETWEEN ".ss_TimeStampToSQL($range['start'])." AND ".ss_TimeStampToSQL($range['end'])."
					AND or_card_denied IS NULL AND or_cancelled IS NULL
					AND or_reshipment IS NOT NULL"
			);		

			$values['Refunds'][$key] = getRow("
				SELECT SUM(rfd_amount) AS TheValue FROM shopsystem_orders join shopsystem_refunds on rfd_or_id = or_id
				WHERE rfd_timestamp BETWEEN ".ss_TimeStampToSQL($range['start'])
				." AND ".ss_TimeStampToSQL($range['end'])
			);		

		}

		$values['Product Revenue'][$key]['TheValue'] = $values['Total Revenue'][$key]['TheValue'] - $values['Shipping'][$key]['TheValue'];
		if( $values['Product Revenue'][$key]['TheValue'] > 0 )
			$values['CM%'][$key]['TheValue'] = 100.0 * $values['CM'][$key]['TheValue'] / $values['Product Revenue'][$key]['TheValue'];
		else
			$values['CM%'][$key]['TheValue'] = 0;

//		$values['Refunds'][$key] = getRow("
//			SELECT SUM(rfd_amount) AS TheValue FROM shopsystem_refunds, shopsystem_orders 
//			WHERE rfd_timestamp BETWEEN ".ss_TimeStampToSQL($range['start'])
//			." AND ".ss_TimeStampToSQL($range['end'])."
//				AND rfd_or_id = or_id
//		");		

	}
	
	if ($compare) {
		foreach ($values as $key => $valueArray) {
			if ($values[$key][$from]['TheValue'] == 0) {
				$values[$key]['% Difference'] = array('TheValue'=>'-');
			} else {
				$values[$key]['% Difference'] = array('TheValue'=>ss_decimalFormat(($values[$key][$to]['TheValue']/$values[$key][$from]['TheValue']*100)-100,1).'%');
			}
		}
	}
	
	$note = getField("
		SELECT DaReNoNote FROM ShopSystem_DailyReportNotes
		WHERE DaReNoDate = NOW()
		ORDER BY DaReNoID DESC
		LIMIT 1		
	");
	
?>
