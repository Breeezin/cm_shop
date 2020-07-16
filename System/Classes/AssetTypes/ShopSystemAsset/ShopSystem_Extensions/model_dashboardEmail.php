<?
	$result = new Request("ShopSystem.AcmeAutoDashboard",array('HideButtons'=>1,'MainLayout'=>'none'));
	$emailResult = new Request("Email.Send",array(
		'to'	=>	'macbjorck@mac.com',
		'from'	=>	'admin@acmerockets.com',
		'html'	=>	$result->display,
		'subject'	=>	'AcmeRockets.com Daily Report',
	));
	$emailResult = new Request("Email.Send",array(
		'to'	=>	'im@admin.com',
		'from'	=>	'im@admin.com',
		'html'	=>	$result->display,
		'subject'	=>	'AcmeRockets.com Daily Report',
	));
		
?>
<p>
Email sent. You may now <a href="Javascript:window.close();void(0);">close this window</a>.
</p>
