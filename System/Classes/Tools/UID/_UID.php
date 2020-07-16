<?php
class UID extends Plugin {

	function exposeServices() {
		return array(
			'UID.Get'	=>	array('method'	=>	'query'),
		);
	}

	function query() {
		$this->display->layout = 'None';
		return include('QueryQuery.php');
	} 
}
?>
