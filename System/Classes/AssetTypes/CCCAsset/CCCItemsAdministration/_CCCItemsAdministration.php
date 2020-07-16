<?php
requireOnceClass('Administration');
class CCCItemsAdministration extends Administration {

	function exposeServices() {		
		return	Administration::exposeServicesUsing('CCCItems');		
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
			'prefix'					=>	'CCCItems',
			'singular'					=>	'Item',
			'plural'					=>	'Items',
			'tableName'					=>	'ccc_items',
			'tablePrimaryKey'			=>	'cit_id',
			'tableDisplayFields'		=>	array('cit_title'),
			'tableDisplayFieldTitles'	=>	array('Title'),
			'tableOrderBy'				=>	array('cit_sort_order,cit_id' => 'Default'),
			'tableAssetLink'			=>	'cit_as_id',
			'assetLink'					=>	$assetID,
			'tableSortOrderField'		=>	'cit_sort_order',
		));
		
/*
		$this->setParent(new ParentTable(array(
			'tableName'					=>	'assets',
			'tablePrimaryKey'			=>	'as_id',
			'linkField'					=>	'as_parent_as_id',
		)));
*/
		
		$this->addField(new TextField (array(
			'name'			=>	'cit_title',
			'displayName'	=>	'Title',
			'note'			=>	NULL,
			'required'		=>	TRUE,
			'verify'		=>	FALSE,
			'unique'		=>	false,
			'size'	=>	'50',	'maxLength'	=>	'255',
			'rows'	=>	'6',	'cols'		=>	'40',
			'linkQueryAction'	=>	NULL,
			'linkQueryValueField'	=>	NULL,
			'linkQueryDisplayField'	=>	NULL,
		)));
		

		$this->addField(new HTMLMemoField2 (array(
			'name'			=>	'cit_content',
			'displayName'	=>	'Content',
			'note'			=>	NULL,
			'required'		=>	TRUE,
			'verify'		=>	FALSE,
			'unique'		=>	false,
			'default'		=>	null,
			'size'	=>	'50',	'maxLength'	=>	'255',
			'rows'	=>	'6',	'cols'		=>	'40',
			'width'	=>	'document.body.clientWidth-35',
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
