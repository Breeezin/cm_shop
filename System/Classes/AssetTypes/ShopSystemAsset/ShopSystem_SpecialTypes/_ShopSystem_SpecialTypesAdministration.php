<?php
requireOnceClass('Administration');
class ShopSystem_SpecialTypesAdministration extends Administration {

	function exposeServices() {
		return Administration::exposeServicesUsing('ShopSystem_SpecialTypes');
	}

	function __construct() {
		$assetID = null;
		
		if (!strlen($this->assetLink)) {
			if (array_key_exists("as_id", $_REQUEST)) {
				$assetID = $_REQUEST['as_id'];			
			}			
		}
		
		parent::__construct(array(
			'prefix'					=>	'ShopSystem_SpecialTypes',
			'singular'					=>	'Special Type',
			'plural'					=>	'Special Types',
			'tableName'					=>	'ShopSystem_SpecialTypes',
			'tablePrimaryKey'			=>	'SpTyID',
			'tableDisplayFields'		=>	array('SpTyID', 'SpTyName'),
			'tableDisplayFieldTitles'	=>	array('Special ID', 'Special Type'),
			'tableOrderBy'				=>	array('SpTySortOrder,SpTyName' => 'Sort Order','SpTyName' => 'Special Type'),
			'tableAssetLink'			=>	'SpTyAssetLink',
			'assetLink'					=>	$assetID,
			'tableDeleteFlag'			=>	'SpTyDeleted',
			'tableSortOrderField'		=>	'SpTySortOrder',
		));
		

			
		$this->addField(new TextField (array(
			'name'			=>	'SpTyName',
			'displayName'	=>	'Special Type Name',
			'note'			=>	'Changing this will only change the name - functionality can only be adjusted by the deployer',
			'required'		=>	true,
			'verify'		=>	false,
			'unique'		=>	true,
			'size'	=>	'30',	'maxLength'	=>	'255',
			'rows'	=>	'6',	'cols'		=>	'40',
		)));			
		

	}

}
?>
