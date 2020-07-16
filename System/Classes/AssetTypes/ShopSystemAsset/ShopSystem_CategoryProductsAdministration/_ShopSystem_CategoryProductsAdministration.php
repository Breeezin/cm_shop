<?php
requireOnceClass('Administration');
class ShopSystem_CategoryProductsAdministration extends Administration {

	function exposeServices() {
		return Administration::exposeServicesUsing('ShopSystem_CategoryProducts');
	}
	function delete() {
		// Delete the row
		
		if (array_key_exists('pr_id',$this->ATTRIBUTES)) {			
//			$result = query("
//				DELETE FROM shopsystem_product_extended_options 
//				WHERE pro_pr_id IN ( {$this->ATTRIBUTES['pr_id']})
//			");
			$result = query("
				Update shopsystem_product_extended_options 
				set pro_deleted = 1
				WHERE pro_pr_id IN ( {$this->ATTRIBUTES['pr_id']})
			");
		}
		if (array_key_exists('pr_ca_id',$this->ATTRIBUTES)) {			
			
			$result = query("
				SELECT * FROM shopsystem_products 
				WHERE pr_ca_id = {$this->ATTRIBUTES['pr_ca_id']}
			");
			while($product = $result->fetchRow()) {
//				$Q_del = query("
//					DELETE FROM shopsystem_product_extended_options 
//					WHERE pro_pr_id IN ( {$product['pr_id']})
//				");
				$Q_del = query("
					Update shopsystem_product_extended_options 
					set pro_deleted = 1
					WHERE pro_pr_id IN ( {$product['pr_id']})
				");
			}
		}
		
	
			
		parent::delete();
	}
	
	function query($params = array()) {
		ss_paramKey($params,'FilterSQL','');
		if (array_key_exists('ForModifySortOrder',$params)) {
			$params['FilterSQL'] .= ' AND pro_pr_id = pr_id AND pro_is_main = 1';
		} else {
			$params['FilterSQL'] .= ' AND pro_pr_id = pr_id';
		}
		$params['FilterTablesSQL'] = 'shopsystem_product_extended_options';

		$query = parent::query($params);

		// Acme Express customisation
		if (ss_optionExists('Shop Acme Rockets')) {
			if (is_object($query)) {
				$query->addColumn('BackStampCode');
				$counter = 0;
				while ($row = $query->fetchRow()) {
					
					$backStampCode = getRow("
						SELECT soit_date_changed, soit_bs_code
						FROM shopsystem_supplier_order_sheets_items, shopsystem_products, shopsystem_product_extended_options
						WHERE pr_id = pro_pr_id
							AND pr_id = {$row['pr_id']}
							AND pro_stock_code LIKE soit_stock_code
							AND soit_bs_code IS NOT NULL 
							AND soit_date_changed IS NOT NULL
						ORDER BY soit_date_changed DESC
						LIMIT 0,1					
					");				
	
					if ($backStampCode !== null) {
						$query->setCell('BackStampCode',$backStampCode['soit_bs_code'].' - '.date('j M y',ss_SQLtoTimeStamp($backStampCode['soit_date_changed'])),$counter);
					}
					$counter++;
				}
			}
		}
		

		return $query;
	}	

	function entries() {	
		
		if (array_key_exists($this->tablePrimaryKey,$this->ATTRIBUTES)) $this->primaryKey = $this->ATTRIBUTES[$this->tablePrimaryKey];
		require('EntriesQuery.php');		
		require('EntriesDisplay.php');	
	}		
	
	function getCurrencyFromCereal($cereal,$type) {
		return include('System/Classes/AssetTypes/ShopSystemAsset/inc_getCurrencyFromCereal.php');
	}
	
	function __construct($assetID = null,$pr_id = null) {
		$assetID = null;

		if (!strlen($this->assetLink)) {
			if (array_key_exists("as_id", $_REQUEST)) {
				$assetID = $_REQUEST['as_id'];			
			}			
		}

		$tableDisplayFields = array('pr_name','pro_stock_code','pro_price','pro_special_price');
		if (ss_optionExists('Shop Members')) {
			$tableDisplayFields = array('pr_name','pro_stock_code','pro_price','pro_special_price','pro_member_price');
		}
				
		$tableDisplayFieldTitles = array('Name','Stock Code','Price','Special Price');
		if (ss_optionExists('Shop Members')) {
			$tableDisplayFieldTitles = array('Name','Stock Code','Price','Special Price','Member Price');
		}

        if (ss_optionExists('Shop Featured Products')) {
			array_push($tableDisplayFields,'pr_featured');	
			array_push($tableDisplayFieldTitles,'');	
		}
		
		if (ss_optionExists('Shop VIP Products')) {
			array_push($tableDisplayFields,'pr_vip');	
			array_push($tableDisplayFieldTitles,'');	
		}
		
		if (ss_optionExists('Shop Acme Rockets')) {
			array_push($tableDisplayFields,'pr_offline');	
			array_push($tableDisplayFieldTitles,'');	

			array_push($tableDisplayFields,'pr_points');	
			array_push($tableDisplayFieldTitles,'');	
		
		}
		
		parent::__construct(array(
			'prefix'					=>	'ShopSystem_CategoryProducts',
			'singular'					=>	'Category Product',
			'plural'					=>	'Category Products',
			'tableName'					=>	'shopsystem_products',
			'tablePrimaryKey'			=>	'pr_id',
			'tableDisplayFields'		=>	$tableDisplayFields,
			'tableDisplayFieldTitles'	=>	$tableDisplayFieldTitles,
			'tableOrderBy'				=>	array('pr_sort_order' => 'Default','pr_name' => 'Name','pro_stock_code' => 'Stock Code'),
			'tableAssetLink'			=>	'pr_as_id',
			'assetLink'					=>	$assetID,
			'tableDeleteFlag'			=>	'pr_deleted',
			'tableSortOrderField'		=>	'pr_sort_order',
		));

		$this->tableSearchFields = $tableDisplayFields;

		if (ss_optionExists('Shop Acme Rockets')) {
			array_push($this->tableDisplayFields,'BackStampCode');
			array_push($this->tableDisplayFieldTitles,'Back Stamp Code');
			array_push($this->tableDisplayFields,'pr_combo');
			array_push($this->tableDisplayFieldTitles,'');
			$this->tableOrderBy['pr_points'] = 'Loyalty Points';
		}
		
		if (ss_optionExists('Shop Product Out Of Stock') or ss_optionExists('Shop Product Stock Levels')) {
			$this->tableOrderBy['pro_stock_available'] = 'Stock Available';
			array_push($this->tableDisplayFields,'pro_stock_available');
			array_push($this->tableDisplayFieldTitles,'Stock Available');
		}		
		
		if (ss_optionExists('Shop Acme Rockets')) {
			
			$this->addChild(new ChildTable (array(
				'prefix'					=>	'ShopSystem_ComboProducts',
				'plural'					=>	'Combo Products',
				'singular'					=>	'Combo Product',
				'tableName'					=>	'shopsystem_combo_products',
				'tablePrimaryKey'			=>	'cpr_id',
				'linkField'					=>	'cpr_element_pr_id',
				'tableAssetLink'			=>	'cpr_as_id',
			)));
		}		
		
		if ($assetID != null and ss_optionExists('Shop Product Limit')) {
			$productCount = getRow("
				SELECT COUNT(*) AS Total FROM shopsystem_products
				WHERE pr_as_id = $assetID
					AND (pr_deleted IS NULL OR pr_deleted = 0)
			");
			if ($productCount >= ss_optionExists('Shop Product Limit')) {
				$this->hideNewButton = 'You have reached the '.ss_optionExists('Shop Product Limit').' product limit your shopping system allows. If you would like to add more, please contact your website developer';
			}
		}
		
		
		
		$this->setParent(new ParentTable(array(
			'tableName'					=>	'shopsystem_categories',
			'tablePrimaryKey'			=>	'ca_id',
			'linkField'					=>	'pr_ca_id',
		)));

		require('System/Classes/AssetTypes/ShopSystemAsset/inc_productFields.php');
		
	}

   	function create() {
		//ss_DumpVarDie($this, "create");
		if (array_key_exists($this->tablePrimaryKey,$this->ATTRIBUTES)) $this->primaryKey = $this->ATTRIBUTES[$this->tablePrimaryKey];
		if ($this->parentTable !== null and array_key_exists($this->parentTable->linkField,$this->ATTRIBUTES)) $this->parentKey = $this->ATTRIBUTES[$this->parentTable->linkField];
		if (array_key_exists('as_id',$this->ATTRIBUTES)) $this->assetLink = $this->ATTRIBUTES['as_id'];
		require('CreateAction.php');
		require('CreateDisplay.php');
	}


}
?>
