<?php


class MDI extends Plugin {

	function __construct() {
		$this->pluginDirectory = dirname(__FILE__);
		parent::__construct();
//		$this->Plugin();
	}
	function tabbedInterface() {
		
		require('view_tabbedInterface.php');
	}
	
	function tabbedInterfaceWelcome() {
		//$this->display->layout = 'None';
		require('query_tabbedInterfaceWelcome.php');
		require('view_tabbedInterfaceWelcome.php');
	}
	function tabbedAssetPanel() {
		//$this->display->layout = 'None';ADMIN_CUSTOMER_ISSUE
		require('view_tabbedAssetPanel.php');
	}
	function tabbedInterfaceConfiguration() {
		require('query_tabbedInterfaceConfiguration.php');
		require('view_tabbedInterfaceConfiguration.php');
	}

	function ping() {
		$this->display->layout = 'none';
		print('pong');
	}
	
	function closeWindow() {
		$this->display->layout = 'none';
		print('<HTML><BODY><SCRIPT LANGUAGE="Javascript">window.close();</SCRIPT></BODY></HMTL>');
	}
	
	function exposeServices() {
		return array(
			"TabbedInterface"				=>	array('method'	=>	'tabbedInterface'),
			"TabbedInterfaceWelcome"		=>	array('method'	=>	'tabbedInterfaceWelcome'),
			"TabbedAssetPanel"				=> 	array('method'	=>	'tabbedAssetPanel'),
			"TabbedInterfaceConfiguration"	=>	array('method'	=>	'tabbedInterfaceConfiguration'),
			"CloseWindow"					=>	array('method'	=>	'closeWindow'),
			"Ping"	=>	array('method'	=>	'ping'),
		);
	}
}

?>
