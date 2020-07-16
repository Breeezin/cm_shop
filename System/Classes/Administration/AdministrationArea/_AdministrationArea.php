<?php

class AdministrationArea extends Plugin {

	function display() {
		require('view_display.php');		
	}
	
	function exposeServices() {
		return array(
			"Administration"	=>	array('method'	=>	'display'),
		);
	}
	
}


?>
