<?php
	
	$this->param('StartDate','');
	$this->param('EndDate','');

	$errors = array();
	$message = '';
	
	if (array_key_exists('Submit',$this->ATTRIBUTES)) {
		if (ListLen($this->ATTRIBUTES['StartDate'],'/') == 3 and ListLen($this->ATTRIBUTES['EndDate'],'/') == 3) {
			$startDay = ListGetAt($this->ATTRIBUTES['StartDate'],1,'/');
			$startMonth = ListGetAt($this->ATTRIBUTES['StartDate'],2,'/');
			$startYear = ss_AdjustTwoDigitYear(ListGetAt($this->ATTRIBUTES['StartDate'],3,'/'));
			
			$endDay = ListGetAt($this->ATTRIBUTES['EndDate'],1,'/');
			$endMonth = ListGetAt($this->ATTRIBUTES['EndDate'],2,'/');
			$endYear = ss_AdjustTwoDigitYear(ListGetAt($this->ATTRIBUTES['EndDate'],3,'/'));

			if (checkdate($startMonth,$startDay,$startYear) and checkdate($endMonth,$endDay,$endYear)) {						
			
				$startDate = mktime(0,0,0,$startMonth,$startDay,$startYear);
				$endDate = mktime(23,59,59,$endMonth,$endDay,$endYear);
				
				$this->ATTRIBUTES['StartDate'] = date('d/m/Y',$startDate);
				$this->ATTRIBUTES['EndDate'] = date('d/m/Y',$endDate);
				
				$message = "Products shipped during period ".$this->ATTRIBUTES['StartDate']." to ".$this->ATTRIBUTES['EndDate'];
				
				$Q_Products = query("	
					SELECT * FROM shopsystem_shipped_products
					WHERE shp_date BETWEEN '".date('Y-m-d',$startDate)."' AND '".date('Y-m-d',$endDate)."'
					ORDER BY shp_date, shp_or_id, shp_customs_number
				");
			} else {
				$errors['Date'] = array('Please ensure both start and end dates are valid.');
			}
		} else if (!strlen($this->ATTRIBUTES['StartDate']) and !strlen($this->ATTRIBUTES['EndDate'])) {
			$message = "Products shipped on ".date('d.m.Y');
			$today = date('Y-m-d');
			$Q_Products = query("
				SELECT * FROM shopsystem_shipped_products
				WHERE shp_date = '$today'
				ORDER BY shp_date, shp_or_id, shp_customs_number
			");
		} else {
			$errors['Date'] = array('Please ensure start and end dates are in dd/mm/yyyy format.');
		}
		
	}

?>