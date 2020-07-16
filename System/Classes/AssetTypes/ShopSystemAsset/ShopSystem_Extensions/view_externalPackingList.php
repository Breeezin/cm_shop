<?php
	$data = array(
		'Q_Orders'	=>	$Q_Orders
	);
	
	$this->display->title = 'Packing List';
	$this->display->layout = 'AdministrationPrint';
	
	$this->useTemplate('AcmeExternalPackingList',$data);
?>
