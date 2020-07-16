<?php
requireOnceClass('Administration');
class ShopSystem_DiscountGroupsAdministration extends Administration {

	function exposeServices() {
		return Administration::exposeServicesUsing('ShopSystem_DiscountGroups');
	}

	function __construct() {
		$assetID = null;
		
		if (!strlen($this->assetLink)) {
			if (array_key_exists("as_id", $_REQUEST)) {
				$assetID = $_REQUEST['as_id'];			
			}			
		}
		
		parent::__construct(array(
			'prefix'					=>	'ShopSystem_DiscountGroups',
			'singular'					=>	'Discount Group',
			'plural'					=>	'Discount Groups',
			'tableName'					=>	'shopsystem_discount_groups',
			'tablePrimaryKey'			=>	'dig_id',
			'tableDisplayFields'		=>	array('dig_name'),
			'tableDisplayFieldTitles'	=>	array('Discount Group'),
			'tableOrderBy'				=>	array('dig_sort_order,dig_name' => 'Sort Order','dig_name' => 'Discount Group'),
			'tableAssetLink'			=>	'dig_as_id',
			'assetLink'					=>	$assetID,
			'tableDeleteFlag'			=>	'dig_deleted',
			'tableSortOrderField'		=>	'dig_sort_order',
		));
		
/*		$this->setParent(new ParentTable(array(
			'tableName'					=>	'shopsystem_categories',
			'tablePrimaryKey'			=>	'ca_id',
			'linkField'					=>	'ca_parent_ca_id',
		)));*/
		
		
			
		$this->addField(new TextField (array(
			'name'			=>	'dig_name',
			'displayName'	=>	'Discount Group Name',
			'note'			=>	null,
			'required'		=>	true,
			'verify'		=>	false,
			'unique'		=>	true,
			'size'	=>	'30',	'maxLength'	=>	'255',
			'rows'	=>	'6',	'cols'		=>	'40',
		)));			
		
	}

}
?>
