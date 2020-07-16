<?php

	$data = array(	
		'vendors'   =>  query( "select * from vendor" ),
		'Q_Stock'	=>	$Q_Stock,
		'vendor'    =>  $this->ATTRIBUTES['vendor'],
	);
	
	$this->useTemplate('ExternalOrder',$data);
	
?>
