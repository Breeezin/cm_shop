<?php
requireOnceClass('Administration');

class Supplier extends Administration {

	var $prefix = 'Supplier';
	var $singular = 'Supplier';
	var $plural = 'Suppliers';
	var $tableName = 'supplier';
	var $tablePrimaryKey = 'sp_id';
	var $parentTable = null;
	var $tableAssetLink = null;
	var $assetLink = null;
	var $tableDisplayFieldTitles = array('Supplier Name', 'Default Discount', 'Default Currency', 'Product Category List');
	var $tableDisplayFields = array('sp_name', 'sp_default_discount', 'sp_default_currency', 'sp_category_list');
	var $tableTimeStamp = null;
	var $fields = array();
	
	function __construct() {		

		//$this->Administration(array(
		parent::__construct( array(
			'prefix'					=>	'Supplier',
			'singular'					=>	'Supplier',
			'plural'					=>	'Suppliers',
			'tableName'					=>	'supplier',
			'tablePrimaryKey'			=>	'sp_id',
			'tableSearchFields'			=>	array('Supplier Name', 'Default Discount', 'Default Currency', 'Product Category List'),
			'tableDisplayFields'        =>  array('sp_name', 'sp_default_discount', 'sp_default_currency', 'sp_category_list'),
			'tableDisplayFieldTitles'	=>	$this->tableDisplayFieldTitles,
			'tableOrderBy'				=>	array('sp_id' => 'Supplier Ident' ),			
		));

		$t = new TextField (array(
			'name'			=>	'sp_name',
			'displayName'	=>	'Supplier Name',
			'note'			=>	null,
            'required'        =>    false,
			'verify'		=>	false,
			'trim'			=>	true,
			'unique'		=>	false,
			'size'	=>	'40',	'maxLength'	=>	'54',
		));
		$this->addField( $t );

        $t = new FloatField (array(
                'name'            =>    'sp_default_discount',
                'displayName'    =>    'Supplier Default Discount',
                'note'            =>    null,
                'required'        =>    false,
                'verify'        =>    false,
                'unique'        =>    false,
            ));
		$this->addField( $t );

		$t = new TextField (array(
			'name'			=>	'sp_default_currency',
			'displayName'	=>	'Supplier Default Currency',
			'note'			=>	null,
            'required'        =>    false,
			'verify'		=>	false,
			'trim'			=>	true,
			'unique'		=>	false,
			'size'	=>	'3',	'maxLength'	=>	'3',
		));
		$this->addField( $t );

		$t = new TextField (array(
			'name'			=>	'sp_category_list',
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
		return Administration::exposeServicesUsing('Supplier');
	}
}

?>
