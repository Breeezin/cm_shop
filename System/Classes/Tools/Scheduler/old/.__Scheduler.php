<?php

class Scheduler extends Plugin {
	
	function Scheduler() {
		$this->pluginDirectory = dirname(__FILE__);
		$this->Plugin();
	}

	function runJobs() {
		require('query_runJobs.php');	
	}
	
	function createJob() {
		include('model_createJob.php');
	}
	
	function exposeServices() {
		return array(
			"Scheduler.RunJobs"	=>	array('method'	=>	'runJobs'),
			"Scheduler.CreateJob"	=>	array('method'	=>	'createJob'),
			"Scheduler.DelayTest"	=>	array('method'	=>	'delayTest'),
		);
	}
	
	function delayTest() {
		include('query_delayTest.php');	
	}
	
}


?>