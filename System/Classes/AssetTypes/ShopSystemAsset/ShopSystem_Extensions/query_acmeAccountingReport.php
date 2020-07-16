<?php
	
	$this->param('StartDate','');
	$this->param('EndDate','');
	$this->param('FilterSup','All,1=1');
	$this->param('ReportType','SupplierOrderSheets');
	$this->param('FilterAb','All,1=1');

	$errors = array();
	
	if (array_key_exists('Submit',$this->ATTRIBUTES)) {
		if (ListLen($this->ATTRIBUTES['StartDate'],'/') == 3) {
			$startDay = ListGetAt($this->ATTRIBUTES['StartDate'],1,'/');
			$startMonth = ListGetAt($this->ATTRIBUTES['StartDate'],2,'/');
			$startYear = ss_AdjustTwoDigitYear(ListGetAt($this->ATTRIBUTES['StartDate'],3,'/'));
			
			if (checkdate($startMonth,$startDay,$startYear)) {						
				$startDate = mktime(0,0,0,$startMonth,$startDay,$startYear);
				$this->ATTRIBUTES['StartDate'] = date('d/m/Y',$startDate);
			} else {
				$errors['Date'] = array('Please ensure start and end dates are in dd/mm/yyyy format or left blank.');
			}
			
		} else if (!strlen($this->ATTRIBUTES['StartDate'])) {
			$startDate = null;			
		} else {
			$errors['Date'] = array('Please ensure start and end dates are in dd/mm/yyyy format or left blank.');
		}
		
		
		if (ListLen($this->ATTRIBUTES['EndDate'],'/') == 3) {
			$endDay = ListGetAt($this->ATTRIBUTES['EndDate'],1,'/');
			$endMonth = ListGetAt($this->ATTRIBUTES['EndDate'],2,'/');
			$endYear = ss_AdjustTwoDigitYear(ListGetAt($this->ATTRIBUTES['EndDate'],3,'/'));
			
			if (checkdate($endMonth,$endDay,$endYear)) {						
				$endDate = mktime(0,0,0,$endMonth,$endDay,$endYear);
				$this->ATTRIBUTES['EndDate'] = date('d/m/Y',$endDate);
			} else {
				$errors['Date'] = array('Please ensure start and end dates are in dd/mm/yyyy format or left blank.');
			}
			
		} else if (!strlen($this->ATTRIBUTES['EndDate'])) {
			$endDate = null;			
		} else {
			$errors['Date'] = array('Please ensure start and end dates are in dd/mm/yyyy format or left blank.');
		}
		
		if (!count($errors)) {
			if ($this->atts['ReportType'] == 'SupplierOrderSheets') {
				$startDateSQL = '1=1';
				if ($startDate !== null) {
					$startDateSQL = "sos_invoice_date >= '".date('Y-m-d',$startDate)."'";
				}
				$endDateSQL = '1=1';
				if ($endDate !== null) {
					$endDateSQL = "sos_invoice_date <= '".date('Y-m-d',$endDate)."'";
				}
				
				$Q_Invoices = query("	
					SELECT * FROM shopsystem_supplier_order_sheets
					WHERE $startDateSQL AND $endDateSQL
						AND ".ListLast($this->ATTRIBUTES['FilterSup'])."
					ORDER BY sos_invoice_number
				");
			} else {
				$startDateSQL = '1=1';
				if ($startDate !== null) {
					$startDateSQL = "in_date >= '".date('Y-m-d',$startDate)."'";
//					$startDateSQL = "CoInDate >= '".date('Y-m-d',$startDate)."'";
				}
				$endDateSQL = '1=1';
				if ($endDate !== null) {
					$endDateSQL = "in_date <= '".date('Y-m-d',$endDate)."'";
//					$endDateSQL = "CoInDate <= '".date('Y-m-d',$endDate)."'";
				}
				
				$Q_Invoices = query("	
					SELECT * FROM ShopSystem_CounterInvoices, shopsystem_invoices
					WHERE $startDateSQL AND $endDateSQL
						AND ".ListLast($this->ATTRIBUTES['FilterAb'])."
						AND CoInOriginalInvoiceLink = inv_id
					ORDER BY CoInID
				");
			}
		}
		
	}

?>
