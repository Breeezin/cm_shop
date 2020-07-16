<?php
	$data = array(
		'Q_ChangesProducts'	=>	$Q_ChangesProducts
	);
	
	$this->display->title = 'Recent Product Changes Report';
	
	$this->useTemplate('RecentChangesList',$data);
?>
