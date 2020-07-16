<?php
requireOnceClass('Administration');

class ProcessorsAdministration extends Administration {

	function exposeServices() {
		return Administration::exposeServicesUsing('Processor');
	}


	function __construct() {
		parent::__construct(array(
			'prefix'					=>	'Processor',
			'singular'					=>	'Processor',
			'plural'					=>	'Processors',
			'tableName'					=>	'web_pay_processors',
			'tablePrimaryKey'			=>	'wpp_id',
			'tableDisplayFields'		=>	array('wpp_name','wpp_id'),
			'tableOrderBy'				=>	array('wpp_name' => 'Name'),
		));
	}

}
?>
