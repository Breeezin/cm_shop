<?

	$data = array(
		'Q_Points'	=>	$Q_Points,
		'Balance'	=>	$CheckPoints['TotalPoints'],
		'tr_id'		=>	$order['or_tr_id'],
	);
	
	$this->display->title = 'Points Record';
	
	$this->useTemplate('PointsDisplayOrder',$data);

?>