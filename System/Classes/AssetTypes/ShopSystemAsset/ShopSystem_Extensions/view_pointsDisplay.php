<?

	$data = array(
		'Q_Points'	=>	$Q_Points,
		'UserName'	=>	$User['us_first_name'].' '.$User['us_last_name'],
		'Balance'	=>	$CheckPoints['TotalPoints'],
	);
	
	$this->display->title = 'Points Record';
	
	$this->useTemplate('PointsDisplay',$data);

?>