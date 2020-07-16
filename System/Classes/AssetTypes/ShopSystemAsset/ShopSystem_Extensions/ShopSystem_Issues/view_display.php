<?php

	$data = array(
		'Q_New'	=>	$Q_New,
		'Q_Awaiting'	=>	$Q_Awaiting,
		'Q_Administrators'	=>	$Q_Administrators,
		'filters' => $issues_filters
	);
	$this->display->title = 'Issue List';
	
	$this->useTemplate("Display",$data);
?>
