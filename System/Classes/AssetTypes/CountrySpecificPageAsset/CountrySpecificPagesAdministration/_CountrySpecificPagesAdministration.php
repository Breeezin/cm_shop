<?php
requireOnceClass('Administration');
class CountrySpecificPagesAdministration extends Administration {

	function exposeServices() {		
		return	Administration::exposeServicesUsing('CountrySpecificPages');		
	}
	
	function __construct() {
		$assetID = null;
		
		if (!strlen($this->assetLink)) {
			if (array_key_exists("as_id", $_REQUEST)) {
				$assetID = $_REQUEST['as_id'];			
			} else if (array_key_exists("assetLink", $_REQUEST)) {
				$assetID = $_REQUEST['assetLink'];			
			}			
		}
		
		parent::__construct(array(
			'prefix'					=>	'CountrySpecificPages',
			'singular'					=>	'Country Specific Page',
			'plural'					=>	'Country Specific Pages',
			'tableName'					=>	'CountrySpecificPage_Pages',
			'tablePrimaryKey'			=>	'pag_id',
			'tableDisplayFields'		=>	array('PaCountryCode'),
			'tableDisplayFieldTitles'	=>	array('Country Code'),
			'tableOrderBy'				=>	array('PaCountryCode' => 'Code'),
			'tableAssetLink'			=>	'pag_as_id',
			'assetLink'					=>	$assetID,
		));
		
/*
		$this->setParent(new ParentTable(array(
			'tableName'					=>	'assets',
			'tablePrimaryKey'			=>	'as_id',
			'linkField'					=>	'as_parent_as_id',
		)));
*/
		
		$this->addField(new CountryField (array(
			'name'			=>	'PaCountryCode',
			'displayName'	=>	'Country',
			'note'			=>	'Leave blank to enter default content for countries without any specific content',
			'required'		=>	false,
			'verify'		=>	false,
			'unique'		=>	false,
			'size'	=>	'50',	'maxLength'	=>	'255',
			'rows'	=>	'6',	'cols'		=>	'40',
		)));
		

		$this->addField(new HTMLMemoField2 (array(
			'name'			=>	'pag_content',
			'displayName'	=>	'Content',
			'note'			=>	null,
			'required'		=>	false,
			'verify'		=>	false,
			'unique'		=>	false,
			'default'		=>	null,
			'size'	=>	'50',	'maxLength'	=>	'255',
			'rows'	=>	'6',	'cols'		=>	'40',
			'width'	=>	'document.body.clientWidth-185',
		)));
			

		
/*		$this->addChild(new ChildTable (array(
			'prefix'					=>	'assets',
			'plural'					=>	'Sub assets',
			'singular'					=>	'Sub Asset',
			'tableName'					=>	'assets',
			'tablePrimaryKey'			=>	'as_id',
			'linkField'					=>	'as_parent_as_id'
		)));*/
		
	}

}
?>
