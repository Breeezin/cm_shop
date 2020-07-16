<?
	$this->display->title = 'Review Item';
	
	$data = array(
		'Q_Asset'	=>	$Q_Asset,
	);
	
	$this->useTemplate('ReviewAsset',$data);

?>