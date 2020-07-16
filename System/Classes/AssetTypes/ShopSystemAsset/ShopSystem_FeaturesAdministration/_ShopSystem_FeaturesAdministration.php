<?php
requireOnceClass('Administration');
class ShopSystem_FeaturesAdministration extends Administration {

	function exposeServices() {
		return Administration::exposeServicesUsing('ShopSystem_Features');
	}
	
	function __construct() {
		$assetID = null;
		
		if (!strlen($this->assetLink)) {
			if (array_key_exists("as_id", $_REQUEST)) {
				$assetID = $_REQUEST['as_id'];			
			}			
		}
		
		
		parent::__construct(array(
			'prefix'					=>	'ShopSystem_Features',
			'singular'					=>	'Feature',
			'plural'					=>	'Features',
			'tableName'					=>	'shopsystem_features',
			'tablePrimaryKey'			=>	'fe_id',
			'tableDisplayFields'		=>	array('fe_name'),
			'tableDisplayFieldTitles'	=>	array('Feature Name'),
			'tableOrderBy'				=>	array('fe_sort_order, fe_name' => 'Default','fe_name' => 'Feature Name'),
			'tableAssetLink'			=>	'fe_as_id',
			'assetLink'					=>	$assetID,
			'tableSortOrderField'		=>	'fe_sort_order',
		));
		
		/*$this->setParent(new ParentTable(array(
			'tableName'					=>	'shopsystem_categories',
			'tablePrimaryKey'			=>	'ca_id',
			'linkField'					=>	'ca_parent_ca_id',
		)));*/
		
		$imgDir = ss_secretStoreForAsset($assetID,"FeatureImages");
		
			$this->addField(new TextField (array(
				'name'			=>	'fe_name',
				'displayName'	=>	'Name',
				'note'			=>	null,
				'required'		=>	true,
				'verify'		=>	false,
				'unique'		=>	false,
				'size'	=>	'30',	'maxLength'	=>	'255',
				'rows'	=>	'6',	'cols'		=>	'40',
			)));			
			
			if ($assetID !== null) {
				if(ss_optionExists('Shop Feature Images')) {
					$this->addField(new PopupUniqueImageField (array(
						'name'			=>	'fe_image',
						'displayName'	=>	'Image',
						'directory'		=>	$imgDir,
						'preview'	=>	false,
					)));
				}
			}
			
			if(ss_optionExists('Shop Feature Descriptions')) {
				$this->addField(new HTMLMemoField2 (array(
					'name'			=>	'',
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
