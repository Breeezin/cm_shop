<?php

	$data = array(
		'PageThru'		=>	$pageThru->display,
		'Q_Newsletters'	=>	$Q_Newsletters,
		'as_id'		=>	$this->ATTRIBUTES['as_id'],
		'BackURL'		=>	getBackURL(),
	);
	$this->useTemplate('list',$data);
?>
