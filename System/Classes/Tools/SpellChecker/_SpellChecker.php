<?php
class SpellChecker extends Plugin {

	function exposeServices() {
		return array(
			'SpellChecker.Check'	=>	array('method'	=>	'check'),
		);
	}

	
	function check() {

		$this->display->layout = 'None';
		$GLOBALS['cfg']['debugMode'] = false;

		require('spell-check-logic.php');
		
	}
}


?>
