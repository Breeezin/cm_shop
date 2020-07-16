<?php
requireOnceClass("Administration");

class statistics extends Administration {
	var $showAllParameters = "";
	
	function exposeServices() {
		$prefix = 'statistics';
		return array(
		 	"{$prefix}.Display"		=>	array('method'	=>	'display'),
		 	"{$prefix}.Reset"		=>	array('method'	=>	'reset'),
		 	"{$prefix}.Search"		=>	array('method'	=>	'search'),
		 	"{$prefix}.DailySum"	=>	array('method'	=>	'dailySum'),
		 );		
	}
	function dailySum() {
		require("query_dailySum.php");
	}
	function search() {
		
	}
	
	function reset() {
		require('model_reset.php');
	}
	function display() {
		$this->display->layout = "Administration2";
		$this->display->title = "";	
		
		require('query_stats.php');
		require('view_stats.php');
			
		
	}
 	
	function inputFilter() {
		parent::inputFilter();
		//$this->param('BreadCrumbs','configuration');
		//$this->display->title = 'Confituration Administration';
		//$this->display->layout = 'Administration';
	}

}
?>
