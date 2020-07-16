<?php

class Scheduler extends Plugin {

	function __construct() {
		$this->pluginDirectory = dirname(__FILE__);
		parent::__construct();
	}

	function createJob() {
		 include('model_createJob.php');
	}

	function display() {
		include('query_display.php');	
	}

	function exposeServices() {
		return array(
			"Scheduler.CreateJob"	=>	array('method'	=>	'createJob'),
			"Scheduler.Display"	=>	array('method'	=>	'display'),
		);
	}

}
?>
