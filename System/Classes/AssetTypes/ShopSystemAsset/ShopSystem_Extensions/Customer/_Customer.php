<?php
requireOnceClass('Administration');

class Customer extends Administration {

	var $prefix = 'Customer';
	var $singular = 'Customer';
	var $plural = 'Customers';
	var $tableName = 'customer';
	var $tablePrimaryKey = 'cp_id';
	var $parentTable = null;
	var $tableAssetLink = null;
	var $assetLink = null;
	var $tableDisplayFieldTitles = array('Customer Name', 'Default Discount', 'Default Currency', 'Product Category List');
	var $tableDisplayFields = array('cp_name', 'cp_default_discount', 'cp_default_currency', 'cp_category_list');
	var $tableTimeStamp = null;
	var $fields = array();

	function __construct() {		

		//$this->Administration(array(
		parent::__construct( array(
			'prefix'					=>	'Customer',
			'singular'					=>	'Customer',
			'plural'					=>	'Customers',
			'tableName'					=>	'customer',
			'tablePrimaryKey'			=>	'cp_id',
			'tableSearchFields'			=>	array('Customer Name', 'Default Discount', 'Default Currency', 'Product Category List'),
			'tableDisplayFields'        =>  array('cp_name', 'cp_default_discount', 'cp_default_currency', 'cp_category_list'),
			'tableDisplayFieldTitles'	=>	$this->tableDisplayFieldTitles,
			'tableOrderBy'				=>	array('cp_id' => 'Customer Ident' ),			
		));

		$t = new TextField (array(
			'name'			=>	'cp_name',
			'displayName'	=>	'Customer Name',
			'note'			=>	null,
            'required'        =>    false,
			'verify'		=>	false,
			'trim'			=>	true,
			'unique'		=>	false,
			'size'	=>	'40',	'maxLength'	=>	'54',
		));
		$this->addField( $t );

        $t = new FloatField (array(
                'name'            =>    'cp_default_discount',
                'displayName'    =>    'Customer Default Discount',
                'note'            =>    null,
                'required'        =>    false,
                'verify'        =>    false,
                'unique'        =>    false,
            ));
		$this->addField( $t );

		$t = new TextField (array(
			'name'			=>	'cp_default_currency',
			'displayName'	=>	'Customer Default Currency',
			'note'			=>	null,
            'required'        =>    false,
			'verify'		=>	false,
			'trim'			=>	true,
			'unique'		=>	false,
			'size'	=>	'3',	'maxLength'	=>	'3',
		));
		$this->addField( $t );

		$t = new TextField (array(
			'name'			=>	'cp_category_list',
			'displayName'	=>	'Product Cateory List',
			'note'			=>	null,
            'required'        =>    false,
			'verify'		=>	false,
			'trim'			=>	true,
			'unique'		=>	false,
			'size'	=>	'40',	'maxLength'	=>	'54',
		));
		$this->addField( $t );

	}

	function inputFilter() {
		parent::inputFilter();
		$this->param('BreadCrumbs','Administration');
		$this->display->layout = 'Administration';
		// Must be able to Administer something to access these Actions
			
		$result = new Request('Security.Authenticate',array(
				'Permission'	=>	'RestrictedAdmin',
		));
		
	}	

	function exposeServices() {
		return Administration::exposeServicesUsing('Customer');
	}
}

?>
