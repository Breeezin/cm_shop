<?php

	$this->param('Password','');
	if ($this->ATTRIBUTES['Password'] != '45kgidy5') die('.');
	//die('testing');

	exec("sh /www/htdocs/acmeexpresscom/Custom/sendNewsletter");
	
	print("Done");
	
?>