<?

	//$this->param('Email');
	
	$input = new Request("Asset.Embed",array(
		'as_id'	=>	'514',
        'Service'	=>	'Engine',
		'Specials'	=>	1,
		'Template'	=>	'SpecialsText',
		'NoApprox'	=>	1,
	));

	$sent = false;
	if (array_key_exists('Email1',$this->ATTRIBUTES)) {
		$temp = new Request("Email.Send",array(
			'from'	=>	'admin@acmerockets.com',
			'to'	=>	$this->ATTRIBUTES['Email1'],
			'subject'	=>	'Acme Express Specials',
			'text'	=>	strip_tags($input->display),
		));
		$sent = true;
	}
	if (array_key_exists('Email2',$this->ATTRIBUTES)) {
		$temp = new Request("Email.Send",array(
			'from'	=>	'admin@acmerockets.com',
			'to'	=>	$this->ATTRIBUTES['Email2'],
			'subject'	=>	'Acme Express Specials',
			'text'	=>	strip_tags($input->display),
		));
		$sent = true;
	}
	if (array_key_exists('Email3',$this->ATTRIBUTES)) {
		$temp = new Request("Email.Send",array(
			'from'	=>	'admin@acmerockets.com',
			'to'	=>	$this->ATTRIBUTES['Email3'],
			'subject'	=>	'Acme Express Specials',
			'text'	=>	strip_tags($input->display),
		));
		$sent = true;
	}

	$this->display->title = 'Specials Text Email';
	if ($sent) {
		echo "Sent";
	} else {
		echo "Please select an email address to send to.";	
	}
?>