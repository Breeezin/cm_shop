<?php
	$this->display->title = 'Newsletter Recipients';
	$data = array(
		'Q_Recipients'	=>	$Q_Recipients,
		'Subject'	=>	$Newsletter['na_subject'],
		'Sent'		=>	$Newsletter['na_sent'],
		'BackURL'		=>	$this->ATTRIBUTES['BackURL'],
	);
	$this->useTemplate('viewRecipients',$data);
?>