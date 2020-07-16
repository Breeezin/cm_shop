<?
	$this->param('HideButtons',0);

	$data = array(
		'DistinctCustomers'	=>	$distinctCustomers,		
		'RepeatCustomers'	=>	$repeatCustomers,
		'WishListCustomers'	=>	$wishListCustomers,
		'Debts'	=>	$debts,
		'Values'	=>	$values,
		'Q_People'	=>	$Q_PeopleCounter,
		'Q_Missing'	=>	$Q_Missing,
		'Bank'	=>	$bank['BaBaAmount'],
		'AverageShippingDelay'	=>	$averageShipping,
		'Note'	=>	$note,
		'HideButtons'	=>	$this->atts['HideButtons'],
		'Today'	=>	date('d/m/Y',$today),
		'WarehouseStock'	=>	$WarehouseStock,
	);

	if ($compare) {
		$data['to_start']	= $this->atts['to_start'];
		$data['to_end']	= $this->atts['to_end'];
		$data['from_start'] = $this->atts['from_start'];
		$data['from_end'] = $this->atts['from_end'];

	}
	
	
	$this->display->title = "Automatic Dashboard";
	
	if (array_key_exists('Export',$this->atts)) {

		$output = $this->processTemplate('DashboardExport',$data);
		
		header("Pragma: public");
		header("Expires: 0");
		header("Cache-Control: must-revalidate, post-check=0, pre-check=0"); 
		header("Content-Type: application/force-download");
		header("Content-Type: application/octet-stream");
		header("Content-Type: application/download");
		header("Content-Disposition: attachment; filename=Dashboard.csv;");
		header("Content-Transfer-Encoding: binary");
		header("Content-Length: ".strlen($output));		
		print($output);

		$this->display->layout = 'none';
	} else {
		$this->useTemplate('Dashboard',$data);
	}

?>
