<?
	
	$this->param('Prize','test');
	$this->param('WinnerEmail','im@admin.com');
	$this->param('Winner','Matt Currie');
	
	$data = array(
		'Prize'	=>	$this->atts['Prize'],
		'Winner'	=>	$this->atts['Winner'],
	);
	
	$content = $this->processTemplate('LotteryEmail',$data);
	
	$temp = new Request("Email.Send",array(
		'to'	=>	$this->atts['WinnerEmail'],
		'from'	=>	'admin@acmerockets.com',
		'subject'	=>	'You are a winner at AcmeRockets.com!',
		'html'	=>	$content,
		'useTemplate'	=>	false,
	));

	echo "Sent";
?>
