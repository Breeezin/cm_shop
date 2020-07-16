<?php

class Email extends Plugin {
	
	function __construct() {
		$this->pluginDirectory = dirname(__FILE__);
		parent::__construct();
	}

	function send() {
		include('model_send.php');
	}
	
	function exposeServices() {
		return array(
			"Email.Send"	=>	array('method'	=>	'send','visibility' => 'private'),
		);
	}
	
	
}


?>
