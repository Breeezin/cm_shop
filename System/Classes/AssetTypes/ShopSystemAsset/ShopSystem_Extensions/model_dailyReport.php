<?
	$errors = array();
	$done = false;
	$this->param('BankAmount','');
	$this->param('Amount','');
	$this->param('Ref','');
	$this->param('Stock','');
	$this->param('Note','');
	$this->param('ShipDate',date('d/m/Y'));
		
	if (array_key_exists('Submit',$this->atts)) {
		
		if (strlen($this->atts['BankAmount']) and !is_numeric($this->atts['BankAmount'])) {
			$errors['Bank'] = array('Bank balance must be a number');
		}
		
		if (strlen($this->atts['Stock']) and !is_numeric($this->atts['Stock'])) {
			$errors['Stock'] = array('Stock level must be a number');
		}		
		
		if (strlen($this->atts['Ref']) and strlen($this->atts['Amount'])) {
			if (date_error($this->atts['ShipDate']) !== null) {
				$errors['ShipDate'] = array(date_error($this->atts['ShipDate']));
			}
			if (!is_numeric($this->atts['Amount'])) {
				$errors['Amount'] = array('Total cost must be a number');				
			}		
		} else if (strlen($this->atts['Ref'])) {
			$errors['Amount'] = array('Please enter a total cost');				
		} else if (strlen($this->atts['Amount'])) {
			$errors['Ref'] = array('Please enter a reference number');				
		}
		
		if (count($errors) == 0) {
			startTransaction();
			
			if (strlen($this->atts['BankAmount'])) {
				$Q_Insert = query("
					INSERT INTO ShopSystem_BankBalances
						(BaBaDate, BaBaAmount)
					VALUES 
						(NOW(), ".safe($this->atts['BankAmount']).")
				");
			}

			if (strlen($this->atts['Stock'])) {
				
				// calculate stock value based on average value of products in last 20 supplier order sheets
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
			//	ss_DumpVar($averageSupplierOrderSheetStockPrice,'average suppliers price');
				
				// calculate the average stock value based on the sales price last 25 orders
				$Q_Last25Orders = query("
					SELECT or_id, SUM(op_price_paid*op_quantity) AS OrderTotal, SUM(op_quantity) AS Qty FROM ordered_products, shopsystem_orders
					WHERE or_id = op_or_id
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
				//ss_DumpVar($averageOrdersSalePrice,'average orders sale price');
					
				$Q_Insert = query("
					INSERT INTO ShopSystem_WarehouseStock
						(WaStDate, WaStStock, WaStOrdersAveragePrice, WaStSupplierAveragePrice)
					VALUES 
						(NOW(), ".safe($this->atts['Stock']).", $averageOrdersSalePrice, $averageSupplierOrderSheetStockPrice)
				");
				
			}
			
			if (!array_key_exists('ShippingIgnored',$this->atts) and (strlen($this->atts['Ref']) and strlen($this->atts['Amount'])) ) {
			
				$newID = newPrimaryKey('shopsystem_shipping_charges','ssc_id');
				
				$paid = 'NULL';
				if (array_key_exists('Paid',$this->atts)) {
					$paid = 'NOW()';	
				}
				
				$shipDate = mktime(0,0,0,ListGetAt($this->atts['ShipDate'],2,'/'),ListGetAt($this->atts['ShipDate'],1,'/'),ss_AdjustTwoDigitYear(ListGetAt($this->atts['ShipDate'],3,'/')));			
				
				$Q_Insert = query("
					INSERT INTO shopsystem_shipping_charges
						(ssc_id, ssc_amount, ssc_date, ssc_reference, ssc_paid)
					VALUES 
						($newID, ".safe($this->atts['Amount']).", ".ss_TimeStampToSQL($shipDate).", '".escape($this->atts['Ref'])."', $paid)
				");
				
				$Q_Allocate = query("
					UPDATE shopsystem_shipped_products
					SET shp_ssc_id = $newID
					WHERE shp_date = ".ss_TimeStampToSQL($shipDate)."
				");
				
			}			
			
			if (strlen(trim($this->atts['Note']))) {
				$newID = newPrimaryKey('ShopSystem_DailyReportNotes','DaReNoID');
				$Q_NoteInsert = query("
					INSERT INTO ShopSystem_DailyReportNotes
						(DaReNoID, DaReNoDate, DaReNoNote)
					VALUES
						($newID, NOW(), '".escape($this->atts['Note'])."')
				");	
				
			}
			
			commit();

			/*$result = new Request("ShopSystem.AcmeAutoDashboard",array('HideButtons'=>1,'MainLayout'=>'none'));
			$emailResult = new Request("Email.Send",array(
				'to'	=>	'matt@innovativemedia.co.nz',
				'from'	=>	'matt@innovativemedia.co.nz',
				'html'	=>	$result->display,
				'subject'	=>	'AcmeRockets.com Daily Report',
			));*/
			
			$done = true;
			
		}
		
	}

?>
