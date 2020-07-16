<?php
	$this->display->title = 'Newsletter Recipients';
	$data = array(
		'Subject'	=>	$Newsletter['nl_subject'],
		'Q_Recipients'	=>	$Q_NewsletterRecipients,
	);
	$this->useTemplate('Recipients',$data);
?>
