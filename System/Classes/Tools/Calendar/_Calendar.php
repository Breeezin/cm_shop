<?php

class Calendar extends Plugin {


	function exposeServices() {
		return array(
			'Calendar.SelectDate'	=>	array('method'	=>	'selectDate'),
		);
	}


	function selectDate() {
		$this->display->layout = 'calendar';
		require('query_selectDate.php');
		require('view_selectDate.php');
	}

}


?>