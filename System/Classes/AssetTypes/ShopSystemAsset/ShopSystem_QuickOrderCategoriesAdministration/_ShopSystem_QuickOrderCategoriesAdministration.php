<?php
requireOnceClass('Administration');
class ShopSystem_QuickOrderCategoriesAdministration extends Administration {

	function exposeServices() {
		return Administration::exposeServicesUsing('ShopSystem_QuickOrderCategories');
	}
	
	function __construct() {
		$assetID = null;
		
		if (!strlen($this->assetLink)) {
			if (array_key_exists("as_id", $_REQUEST)) {
				$assetID = $_REQUEST['as_id'];			
			}			
		}
		
		
		parent::__construct(array(
			'prefix'					=>	'ShopSystem_QuickOrderCategories',
			'singular'					=>	'Quick Order Category',
			'plural'					=>	'Quick Order Categories',
			'tableName'					=>	'shopsystem_quick_categories',
			'tablePrimaryKey'			=>	'qoc_id',
			'tableDisplayFields'		=>	array('qoc_name'),
			'tableDisplayFieldTitles'	=>	array('Quick Order Category Name'),
			'tableOrderBy'				=>	array('qoc_sort_order, qoc_name' => 'Default','qoc_name' => 'Name'),
			'tableAssetLink'			=>	'qoc_as_id',
			'assetLink'					=>	$assetID,
			'tableSortOrderField'		=>	'qoc_sort_order',
		));
		//	'listManageOptions'			=>	array("Product Settings" => "index.php?act=shopsystem_categories.SettingEdit&BreadCrumbs=[BreadCrumbs]&ca_id=[ca_id]&BackURL=[BackURL]&as_id=[as_id]",),			
		
/*		$this->setParent(new ParentTable(array(
			'tableName'					=>	'shopsystem_categories',
			'tablePrimaryKey'			=>	'ca_id',
			'linkField'					=>	'ca_parent_ca_id',
		)));*/
		
		$imgDir = ss_secretStoreForAsset($assetID,"Images");
		
			$this->addField(new TextField (array(
				'name'			=>	'qoc_name',
				'displayName'	=>	'Name',
				'note'			=>	null,
				'required'		=>	true,
				'verify'		=>	false,
				'unique'		=>	false,
				'size'	=>	'30',	'maxLength'	=>	'255',
				'rows'	=>	'6',	'cols'		=>	'40',
			)));			
			
			if ($assetID !== null) {
				if(ss_optionExists('Shop Quick Order Category Images')) {
					$this->addField(new PopupUniqueImageField (array(
						'name'			=>	'qoc_image',
						'displayName'	=>	'Image',
						'directory'		=>	$imgDir,
						'preview'	=>	false,
					)));
				}
			}
			
			if(ss_optionExists('Shop Quick Order Category Descriptions')) {
				$this->addField(new HTMLMemoField2 (array(
					'name'			=>	'qoc_description_html',
					'displayName'	=>	'Description',
					'note'			=>	null,
					'required'		=>	false,
					'verify'		=>	false,
					'unique'		=>	false,
					'default'		=>	null,
					'size'	=>	'50',	'maxLength'	=>	'255',
					'rows'	=>	'6',	'cols'		=>	'40',
					'width'	=>	'document.body.clientWidth-150',
				)));
			}
					
	}

}
?>
