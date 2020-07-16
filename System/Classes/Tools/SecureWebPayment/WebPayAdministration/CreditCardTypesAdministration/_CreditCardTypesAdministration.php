<?php
requireOnceClass('Administration');

class CreditCardTypesAdministration extends Administration {

	function exposeServices() {
		
		return array_merge(Administration::exposeServicesUsing('CreditCardType'),
			array(
				'CreditCardType.WebPayConfig'	=>	array('method'	=>	'customQuery'),			
			)
		);
	}
	function customQuery() {
		return require("query_customQuery.php");
	}
	function __construct() {
		parent::__construct(array(
			'prefix'					=>	'CreditCardType',
			'singular'					=>	'CreditCardType',
			'plural'					=>	'credit_card_types',
			'tableName'					=>	'credit_card_types',
			'tablePrimaryKey'			=>	'cct_id',
			'tableDisplayFields'		=>	array('cct_name','cct_id'),
			'tableOrderBy'				=>	array('cct_name' => 'Name'),
		));
	}

}
?>
