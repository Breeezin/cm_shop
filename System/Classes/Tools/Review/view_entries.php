<?php
	$this->display->title = 'Review List';

	$data = array(
		'Q_ReviewAssets'	=>	$Q_ReviewAssets,
		'ReloadTree'	=>	array_key_exists('ReloadTree',$this->ATTRIBUTES),
	);
	
	$this->useTemplate('Entries',$data);
?>