<?php

	$this->param('na_id');

	$newsletter = getRow("
		SELECT * FROM newsletter_archive
		WHERE na_as_id = ".$asset->getID()."
			AND na_id = {$this->ATTRIBUTES['na_id']}
	");

	$data = array(
		'Greeting'	=>	'Dear Subscribers',
		'NewsletterLink'	=>	'Javascript:alert(\'You are already viewing the newsletter online!\');void(0);',
	);
		
	$htmlContent = $newsletter['na_content'];
	$htmlContent = stri_replace('[Greeting]',$data['Greeting'],$htmlContent);
	$htmlContent = stri_replace('[NewsletterLink]',$data['NewsletterLink'],$htmlContent);
	$htmlContent = stri_replace('[CurrentPage]',$_SERVER['REQUEST_URI'],$htmlContent);

	$asset->display->layout = 'none';
	
	print($htmlContent);
		
?>