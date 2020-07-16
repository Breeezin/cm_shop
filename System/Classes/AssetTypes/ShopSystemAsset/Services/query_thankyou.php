<?php 
	$this->param('Reference','');
	
	
	$data = array(
		'Message' 	=> ss_parseText($this->asset->cereal['AST_SHOPSYSTEM_THANKYOU_CONTENT']),
		'Reference'	=>	$this->ATTRIBUTES['Reference'],
	);
	$secureSite = $GLOBALS['cfg']['secure_server'];
	$secureSite = ss_withTrailingSlash($secureSite);
	
	if (!strlen($this->ATTRIBUTES['Reference'])) {
		if (ss_optionExists('Transaction Fail Continue')) {
			$data['Message'] = "<p>Thank you for ordering. Your transaction is being proccessed.</p>";			
		} else {
			$data['Message'] = "Your payment transaction has failed.<BR>Please click <a href=\"$assetPath/Service/Checkout\">here</a> to try it again.<BR>Thank you.";
		}
	}
	
	ss_customStyleSheet($this->styleSheet);
	
	$usID = ss_getUserID();
	if ($usID > -1) {
		$tempErr =array();
		ss_login($usID, $tempErr);
	}
	
	
	$this->useTemplate('ThankYou', $data);
?>