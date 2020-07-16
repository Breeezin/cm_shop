<?php
requireOnceClass('Administration');
class ShopSystem_SpecialCodesAdministration extends Administration {

	function exposeServices() {
		return Administration::exposeServicesUsing('ShopSystem_SpecialCodes');
	}

	function __construct() {
		$assetID = null;
		
		if (!strlen($this->assetLink)) {
			if (array_key_exists("as_id", $_REQUEST)) {
				$assetID = $_REQUEST['as_id'];			
			}			
		}
		
		parent::__construct(array(
			'prefix'					=>	'ShopSystem_SpecialCodes',
			'singular'					=>	'Special Code',
			'plural'					=>	'Special Codes',
			'tableName'					=>	'ShopSystem_SpecialCodes',
			'tablePrimaryKey'			=>	'SpCoID',
			'tableDisplayFields'		=>	array('SpCoName','SpCoType','SpCoProductNo','SpCoValue'),
			'tableDisplayFieldTitles'	=>	array('Special Code','Type','No./Value of Products','Value'),
			'tableOrderBy'				=>	array('SpCoName' => 'Special Code'),
			'tableAssetLink'			=>	'SpCoAssetLink',
			'assetLink'					=>	$assetID,
		));
		

		$this->addField(new TextField (array(
			'name'			=>	'SpCoName',
			'displayName'	=>	'Special Code',
			'note'			=>	null,
			'required'		=>	true,
			'verify'		=>	false,
			'unique'		=>	true,
			'size'	=>	'30',	'maxLength'	=>	'255',
			'rows'	=>	'6',	'cols'		=>	'40',
		)));

		$this->addField(new SelectField(array(
			'name'			=>	'SpCoType',
			'displayName'	=>	'Type',
			'note'			=>	null,
			'required'		=>	true,
			'verify'		=>	false,
			'unique'		=>	false,
			'size'	=>	'30',	'maxLength'	=>	'255',
			'rows'	=>	'6',	'cols'		=>	'40',
			'linkQueryAction'	=>	'ShopSystem_SpecialTypesAdministration.Query',
			'linkQueryDisplayField'	=>	'SpTyName',
			'linkQueryValueField'	=>	'SpTyID',
		)));

		$this->addField(new FloatField (array(
			'name'			=>	'SpCoProductNo',
			'displayName'	=>	'Number/Value of Products to be purchased (x)',
			'note'			=>	null,
			'required'		=>	true,
			'verify'		=>	false,
			'unique'		=>	false,
			'size'	=>	'30',	'maxLength'	=>	'255',
			'rows'	=>	'6',	'cols'		=>	'40',
		)));


		$this->addField(new FloatField (array(
			'name'			=>	'SpCoValue',
			'displayName'	=>	'Value of special (y)',
			'note'			=>	null,
			'required'		=>	true,
			'verify'		=>	false,
			'unique'		=>	false,
			'size'	=>	'30',	'maxLength'	=>	'255',
			'rows'	=>	'6',	'cols'		=>	'40',
		)));

		$this->addField(new FloatField (array(
			'name'			=>	'SpCoProductZ',
			'displayName'	=>	'Product ID (z)',
			'note'			=>	'Not applicable to all specials',
			'required'		=>	false,
			'verify'		=>	false,
			'unique'		=>	false,
			'size'	=>	'30',	'maxLength'	=>	'255',
			'rows'	=>	'6',	'cols'		=>	'40',
		)));


		$this->addField(new TextField(array(
			'name'			=>	'SpCoMessage',
			'displayName'	=>	'Special Message',
			'note'			=>	'This will be displayed to the customer',
			'required'		=>	true,
			'verify'		=>	false,
			'unique'		=>	true,
			'size'	=>	'50',	'maxLength'	=>	'255',
			'rows'	=>	'6',	'cols'		=>	'40',
		)));
	}


}
?>
