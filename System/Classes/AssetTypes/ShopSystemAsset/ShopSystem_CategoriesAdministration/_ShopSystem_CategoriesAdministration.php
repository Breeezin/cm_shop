<?php
requireOnceClass('Administration');
class ShopSystem_CategoriesAdministration extends Administration {

	var $restrictedCategoriesSQL = '';
	
	function exposeServices() {
		return array_merge(array(
			'ShopCategoryMenuUpdate'	=>	array('method'	=>	'updateMeunSet'),
			'shopsystem_categories.QueryAllArray'	=>	array('method'	=>	'queryAllArray'),
			'shopsystem_categories.QueryAll'	=>	array('method'	=>	'queryAll'),
			'shopsystem_categories.Vendors'	=>	array('method'	=>	'queryVendors'),
			'shopsystem_categories.Appears'		=>	array('method'	=>	'updateAppears'),
			'shopsystem_categories.SettingEdit'	=>	array('method'	=>	'settingEdit'),
			'shopsystem_categories.AttributesSetting'	=>	array('method'	=>	'attSetting'),
			),
			Administration::exposeServicesUsing('ShopSystem_Categories')
		);
	}
	function updateAppears() {
		require("model_updateAppears.php");
	}
	function settingEdit() {
		require("query_settingEdit.php");
		require("model_settingEdit.php");
		require("view_settingEdit.php");
	}
	function attSetting() {			
		require("query_attSetting.php");
		require("model_attSetting.php");
		require("view_attSetting.php");
		$this->display->layout = 'Administration';
		
	}
	
	function updateMeunSet() {
		require('model_updateMenuSet.php');
	}
	
	function update() {		

//		if (array_key_exists('cad_language',$this->ATTRIBUTES))
//			ss_DumpVarDie( $this );
//			require('System/Classes/AssetTypes/ShopSystemAsset/inc_categoryLanguageFields.php');

		$result =  parent::update();
		if (!count($result) and ss_optionExists('Shop Category Menu Set')) {
			$this->updateMeunSet();
		}
		return $result;
	}
	function entries() {	
		
		if (array_key_exists($this->tablePrimaryKey,$this->ATTRIBUTES)) $this->primaryKey = $this->ATTRIBUTES[$this->tablePrimaryKey];
		require('EntriesQuery.php');		
		require('EntriesDisplay.php');	
	}	
	
	function insert() {
		
		$result =  parent::insert();
		
		if (!count($result) and ss_optionExists('Shop Category Menu Set')) {
			$this->updateMeunSet();
		}		
		return $result;		
	}
	
	function getChildCategories($asset,$parentCategory,$bread,&$result,$isReturnAll, $forAdmin = false) {

		$this->restrictedCategoriesSQL = '';
		if (!$forAdmin) {
			$this->restrictedCategoriesSQL = ss_shopRestrictedCategoriesSQL();
		}

		$whereSQL = '';
		if (0 and count($GLOBALS['cfg']['multiSites']) and strlen($GLOBALS['cfg']['folder_name']) and $forAdmin) {
			$Q_SiteCats = query("SELECT * FROM multisite_categories WHERE msc_site_folder LIKE '{$GLOBALS['cfg']['folder_name']}'");
			$catIDs = $Q_SiteCats->columnValuesList('msc_ca_id');
			if (strlen($catIDs)) {
				$whereSQL = " AND ca_id IN ($catIDs)";
			} else {
				$whereSQL = " AND ca_id IS NULL";
			}
		}
	
		$Q_Categories = query("
			SELECT * FROM shopsystem_categories
			WHERE ca_as_id = ".safe($asset)."
				AND ca_parent_ca_id ".$parentCategory."
				$whereSQL	
				{$this->restrictedCategoriesSQL}		
			ORDER BY ca_sort_order, ca_name
		");		
		ss_comma($bread,' > ');
		while ($row = $Q_Categories->fetchRow()) {
			$catFullName = $bread.$row['ca_name'];
//			ss_log_message( "Category Q returned {$row['ca_name']} now $catFullName" );
			if ($isReturnAll) {
				$result[$catFullName] = $row;
			} else {
				$result[$catFullName] = $row['ca_id'];
			}
			$this->getChildCategories($asset,'= '.$row['ca_id'],$catFullName,$result,$isReturnAll);
		}
		$Q_Categories->free();
	}
	
	function queryAllArray($isReturnAll = false) {
		return require('query_allArray.php');
	}
	
	function queryVendors() {
		$Q_Categories = new FakeQuery(array('VeName','VeID'));
		$Q_Categories->addRow(array('VeName' => 'Las Palmas', 'VeID' => 'null') );
		$vendorsQ = query( "select * from vendor where ve_id IS NOT NULL" );
		while($row = $vendorsQ->fetchRow()) {
			$Q_Categories->addRow(array('VeName' => $row['ve_name'], 'VeID' => $row['ve_id']) );
		}
		return $Q_Categories;
	}

	function queryAll() {
		$categories = $this->queryAllArray();
		$Q_Categories = new FakeQuery(array('ca_id','ca_name'));
		$row = array();
/*		foreach($categories as $row['ca_name'] => $row['ca_id']) {	*/
		foreach($categories as $row['ca_id'] => $row['ca_name']) {
			$Q_Categories->addRow($row);
		}
		return $Q_Categories;
	}
	
	function __construct() {
		$assetID = null;
		
		if (!strlen($this->assetLink)) {
			if (array_key_exists("as_id", $_REQUEST)) {
				$assetID = $_REQUEST['as_id'];			
			}			
		}
		if (ss_optionExists('Show Shop Category Menu')) {
			$displayFields = array('ca_id', 'ca_name','ca_appears_in_menu');
			$displayFieldTitles = array('Category ID', 'Category Name', 'Appears In Menu');

		} else {
			$displayFields = array('ca_id','ca_name',);
			$displayFieldTitles = array('Category ID','Category Name',);
		}

		$options = array("Product Settings" => "index.php?act=shopsystem_categories.SettingEdit&BreadCrumbs=[BreadCrumbs]&ca_id=[ca_id]&BackURL=[BackURL]&as_id=[as_id]",);

		$Q_Langs = query( "Select * from languages where lg_id > 0" );
		while ($row = $Q_Langs->fetchRow())
			$options["Edit in ".$row['lg_name']] = "index.php?act=ShopSystem_CategoriesAdministration.Edit&BreadCrumbs=[BreadCrumbs]&ca_id=[ca_id]&cad_language={$row['lg_id']}&BackURL=[BackURL]&as_id=[as_id]";

		//$this->Administration(array(
		parent::__construct(array(
			'prefix'					=>	'ShopSystem_Categories',
			'singular'					=>	'Category',
			'plural'					=>	'Categories',
			'tableName'					=>	'shopsystem_categories',
			'tablePrimaryKey'			=>	'ca_id',
			'tableDisplayFields'		=>	$displayFields,
			'tableDisplayFieldTitles'	=>	$displayFieldTitles ,
			'tableOrderBy'				=>	array('ca_sort_order, ca_name' => 'Default','ca_name' => 'Name'),
			'tableAssetLink'			=>	'ca_as_id',
			'assetLink'					=>	$assetID,
			'listManageOptions'			=>	$options,
			'tableSortOrderField'		=>	'ca_sort_order',
		));
		
		$this->setParent(new ParentTable(array(
			'tableName'					=>	'shopsystem_categories',
			'tablePrimaryKey'			=>	'ca_id',
			'linkField'					=>	'ca_parent_ca_id',
		)));
		
		$imgDir = ss_secretStoreForAsset($assetID,"CategoryImages");

		$this->addField(new SelectField (array(
                'name'            =>    'ca_origin_cn_id',
                'displayName'    =>    'Origin',
                'note'            =>    null,
                'required'        =>    true,
                'verify'        =>    false,
                'unique'        =>    false,
                'size'    =>    '30',    'maxLength'    =>    '255',
                'rows'    =>    '3',    'cols'        =>    '40',
                'linkQueryAction'    =>    'CountryAdministration.Query',
                'linkQueryValueField'    =>    'cn_id',
                'linkQueryDisplayField'    =>    'cn_name',
            )));


			$this->addField(new TextField (array(
				'name'			=>	'ca_name',
				'displayName'	=>	'Name',
				'note'			=>	null,
				'required'		=>	true,
				'verify'		=>	false,
				'unique'		=>	false,
				'size'	=>	'30',	'maxLength'	=>	'255',
				'rows'	=>	'6',	'cols'		=>	'40',
			)));			

			$this->addField(new TextField (array(
				'name'			=>	'ca_banner',
				'displayName'	=>	'Banner image path',
				'note'			=>	null,
				'required'		=>	true,
				'verify'		=>	false,
				'unique'		=>	false,
				'size'	=>	'40',	'maxLength'	=>	'255',
				'rows'	=>	'6',	'cols'		=>	'40',
			)));			

			$this->addField(new SelectField (array(
				'name'			=>	'ca_nav_id',
				'displayName'	=>	'Navigation Group',
				'note'			=>	null,
				'required'		=>	true,
				'verify'		=>	false,
				'unique'		=>	false,
				'size'	=>	'30',	'maxLength'	=>	'255',
				'rows'	=>	'6',	'cols'		=>	'40',
				'linkQuery'             =>  'select * from category_navigation',
				'linkQueryValueField'	=>	'cnv_id',
				'linkQueryDisplayField'	=>	'cnv_name',
			)));

			$this->addField(new IntegerField (array(
				'name'            =>    'ca_sort_order',
				'displayName'    =>    'Numeric Sort Order under nav group',
				'note'            =>    null,
				'required'        =>    false,
				'verify'        =>    false,
				'unique'        =>    false,
			)));

			$this->addField(new SelectField (array(
				'name'			=>	'ca_dig_id',
				'displayName'	=>	'Discount Group',
				'note'			=>	null,
				'required'		=>	false,
				'verify'		=>	false,
				'unique'		=>	false,
				'size'	=>	'30',	'maxLength'	=>	'255',
				'rows'	=>	'6',	'cols'		=>	'40',
				'linkQuery'             =>  'select * from shopsystem_discount_groups',
				'linkQueryValueField'	=>	'dig_id',
				'linkQueryDisplayField'	=>	'dig_name',
			)));

			
			$this->addField(new CheckboxField (array(
				'name'			=>	'ca_appears_in_menu',
				'displayName'	=>	'Appears In Menu',
				'note'			=>	null,
				'required'		=>	false,
				'verify'		=>	false,
				'unique'		=>	false,				
			)));

			$this->addField(new TextField (array(
				'name'			=>	'ca_window_title',
				'displayName'	=>	'Window Title',
				'note'			=>	null,
				'required'		=>	false,
				'verify'		=>	false,
				'unique'		=>	false,
				'size'	=>	'60',	'maxLength'	=>	'255',
				'rows'	=>	'6',	'cols'		=>	'40',
			)));

			$this->addField(new TextField (array(
				'name'			=>	'ca_metadata_keywords',
				'displayName'	=>	'Metadata Keywords',
				'note'			=>	null,
				'required'		=>	false,
				'verify'		=>	false,
				'unique'		=>	false,
				'size'	=>	'155',	'maxLength'	=>	'1024',
				'rows'	=>	'6',	'cols'		=>	'40',
			)));

			$this->addField(new TextField (array(
				'name'			=>	'ca_metadata_description',
				'displayName'	=>	'Metadata Description',
				'note'			=>	null,
				'required'		=>	false,
				'verify'		=>	false,
				'unique'		=>	false,
				'size'	=>	'155',	'maxLength'	=>	'512',
				'rows'	=>	'6',	'cols'		=>	'40',
			)));

			if (ss_optionExists('Shop Category Restricted')) {

				array_push($this->tableDisplayFields,'ca_password');
				array_push($this->tableDisplayFieldTitles,'Password');
				
				$shopPath = 'Shop';
				if ($assetID !== null) {
					$shopPath = new Request('Asset.PathFromID',array('as_id'=>$assetID));
					$shopPath = $shopPath->value;
				}
				$primaryKey = '[CategoryID]';
				if (array_key_exists('ca_id',$_REQUEST)) {
					$primaryKey = $_REQUEST['ca_id'];	
				}
				
				
				$this->addField(new TextField (array(
					'name'			=>	'ca_password',
					'displayName'	=>	'Password',
					'note'			=>	'If a password is entered, the category will not display on the website until it has been accessed through the following URL: '.$GLOBALS['cfg']['currentServer'].ss_withoutPreceedingSlash(ss_EscapeAssetPath($shopPath)).'/Service/Engine/pr_ca_id/'.$primaryKey.'/AccessCode/[PasswordValue]',
					'required'		=>	false,
					'verify'		=>	false,
					'unique'		=>	false,
					'size'	=>	'30',	'maxLength'	=>	'255',
					'rows'	=>	'6',	'cols'		=>	'40',
				)));
			}


		
			if ($assetID !== null) {
				if(ss_optionExists('Shop Category Images')) {
					$this->addField(new PopupUniqueImageField (array(
						'name'			=>	'ca_image',
						'displayName'	=>	'Image',
						'directory'		=>	$imgDir,
						'preview'	=>	false,
					)));
				}
			}
			
			if(ss_optionExists('Shop Category Descriptions')) {
				$this->addField(new HTMLMemoField2 (array(
					'name'			=>	'ca_description_html',
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
			if (ss_optionExists('Shop Category Menu Set')) {
				$this->addField(new TextField (array(
					'name'			=>	'ca_menu_set',
					'displayName'	=>	'Menu Set',
					'note'			=>	'e.g)A-L,M-Z:190<BR>Leave blank if the sub categories are displayed in one list.<BR>Define the menu width like ":177".<BR>Default width is 190.',
					'required'		=>	false,
					'verify'		=>	false,
					'unique'		=>	false,
					'size'	=>	'30',	'maxLength'	=>	'600',
					'rows'	=>	'6',	'cols'		=>	'40',
				)));				
			}
			if (ss_optionExists('Duty Free Affiliate Programme')) {
				$options = array(
					'Duty Free'=>'DutyFree',
					'Tax Free'=>'TaxFree',
					'Tobacco'=>'Tobacco',
				);
				$this->addField(new SelectFromArrayField(array(
					'name'			=>	'CaAffiliateRate',
					'displayName'	=>	'Affiliate Rate Category',
					'note'			=>	null,
					'required'		=>	false,
					'verify'		=>	false,
					'unique'		=>	false,
					'options'		=> $options,
				)));
			}

            $this->addChild(new ChildTable (array(
				'prefix'					=>	'ShopSystem_Categories',
				'plural'					=>	'Sub Categories',
				'singular'					=>	'Sub Category',
				'tableName'					=>	'shopsystem_categories',
				'tablePrimaryKey'			=>	'ca_id',
				'linkField'					=>	'ca_parent_ca_id',
				'tableAssetLink'			=>	'ca_as_id',
			)));

			$this->addChild(new ChildTable (array(
				'prefix'					=>	'ShopSystem_CategoryProducts',
				'plural'					=>	'Category Products',
				'singular'					=>	'Category Product',
				'tableName'					=>	'shopsystem_products',
				'tablePrimaryKey'			=>	'pr_id',
				'linkField'					=>	'pr_ca_id',
				'tableAssetLink'			=>	'pr_as_id',
			)));

            //briar added this 3.11.05
            //include some customised category fields
            $rootFolder = str_replace('index.php','',$_SERVER['SCRIPT_FILENAME']);
			$customFolder = $rootFolder.'Custom/Classes/ShopSystemAdministration';
			$name = 'inc_extraCatFields.php';
			if (file_exists($customFolder.'/'.$name)) {
				include($customFolder."/".$name);
			}

	}

	function edit( )
	{
		if (array_key_exists('cad_language',$this->ATTRIBUTES))
			require('System/Classes/AssetTypes/ShopSystemAsset/inc_categoryLanguageFields.php');

		parent::edit();
	}

}
?>
