<?php
	$data = array(
		'Q_StockS'	=>	$Q_StockS
	);
	
	$this->display->title = 'Broken Box Check List for Geneva';
	$this->display->layout = 'AdministrationPrint';
	
	$this->useTemplate('BrokenBoxCheckList',$data);
?>
