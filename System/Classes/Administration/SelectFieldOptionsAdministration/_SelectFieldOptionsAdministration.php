<?php
requireOnceClass('Administration');

class SelectFieldOptionsAdministration extends Administration {

	function exposeServices() {
		return Administration::exposeServicesUsing('select_field_options');
	}

	function __construct() {
		parent::__construct(array(
			'prefix'					=>	'select_field_options',
			'singular'					=>	'SelectFieldOption',
			'plural'					=>	'select_field_options',
			'tableName'					=>	'select_field_options',
			'tablePrimaryKey'			=>	'SeOpFiUUID',
			'tableDisplayFields'		=>	array('sfo_uuid', 'sfo_value'),
			'tableOrderBy'				=>	array('sfo_uuid' => 'Default'),
		));		
		
	}

}
?>
