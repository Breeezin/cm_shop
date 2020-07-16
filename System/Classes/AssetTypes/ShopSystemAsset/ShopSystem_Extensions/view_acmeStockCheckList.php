<?php
	$data = array(
		'Q_StockM'	=>	$Q_StockM,
		'Q_StockS'	=>	$Q_StockS
	);
	
	$this->display->title = 'Stock Check List for Geneva & Marbella';
	$this->display->layout = 'AdministrationPrint';
	
	$this->useTemplate('AcmeStockCheckList',$data);
?>
