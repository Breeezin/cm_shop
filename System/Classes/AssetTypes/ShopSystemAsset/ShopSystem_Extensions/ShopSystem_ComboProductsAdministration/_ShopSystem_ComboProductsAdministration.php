<?php
requireOnceClass('Administration');
class ShopSystem_ComboProductsAdministration extends Administration {
	var $ProductLink;
		
	function exposeServices() {
		return Administration::exposeServicesUsing('ShopSystem_ComboProducts');
	}

	/*function updateStockAvailability() {
		require('model_updateStockAvailability.php');	
	}*/
	
	function query($params = array()) {		
//		$params['FilterSQL'] = ' AND pro_is_main = 1 AND pro_pr_id = pr_id';
		ss_paramKey($params,'FilterSQL','');
		$params['FilterSQL'] .= ' AND cpr_pr_id = pr_id';
		$params['FilterTablesSQL'] = 'shopsystem_products';

		$query = parent::query($params);
		
		return $query;
	}
	
	function delete() {
		// Delete the row
		
		/*$result = query("
			DELETE FROM shopsystem_product_extended_options 
			WHERE pro_pr_id = {$this->ATTRIBUTES['pr_id']}
		");*/
		parent::delete();
	}
	
	/*function entries() {	
		
		if (array_key_exists($this->tablePrimaryKey,$this->ATTRIBUTES)) $this->primaryKey = $this->ATTRIBUTES[$this->tablePrimaryKey];
		require('EntriesQuery.php');		
		require('EntriesDisplay.php');	
	}*/	
	
	function __construct($assetID = null,$pr_id = null) {
		
		if ($assetID === null || is_array($assetID)) {
			if (!strlen($this->assetLink)) {
				if (array_key_exists("as_id", $_REQUEST)) {
					$assetID = $_REQUEST['as_id'];			
				}			
			}
		}

		// find out which product group we are combo`ing
		if( array_key_exists( 'cpr_element_pr_id', $_REQUEST ) )
			$ProductLink = $_REQUEST['cpr_element_pr_id'];
		else
		{
			if( array_key_exists( 'cpr_id', $_REQUEST ) )
			{
				$row = GetRow( "select * from shopsystem_combo_products where cpr_id = ".$_REQUEST['cpr_id'] );
				$ProductLink = $row['cpr_element_pr_id'];
			}
		}

		$tableDisplayFields = array('pr_name','cpr_qty');
		
		
		$tableDisplayFieldTitles = array('Name','Quantity');
		
		parent::__construct(array(
			'prefix'					=>	'ShopSystem_ComboProducts',
			'singular'					=>	'Combo Product',
			'plural'					=>	'Combo Products',
			'tableName'					=>	'shopsystem_combo_products',
			'tablePrimaryKey'			=>	'cpr_id',
			'tableDisplayFields'		=>	$tableDisplayFields,
			'tableDisplayFieldTitles'	=>	$tableDisplayFieldTitles,
			'tableOrderBy'				=>	array('pr_id' => 'Default','pr_name' => 'Name'),
			'tableAssetLink'			=>	'cpr_as_id',
			'assetLink'					=>	$assetID,
		));
//			'hideNewButton'				=>	'Create new product in: <input type="hidden" name="act" value="ShopSystem_CategoryProductsAdministration.New"><select onchange="if (this.selectedIndex != 0) this.form.submit();" name="pr_ca_id">'.$categoriesOptionsHTML.'</select>',
//			'tableDeleteFlag'			=>	'pr_deleted',


		$this->setParent(new ParentTable(array(
			'tableName'					=>	'shopsystem_products',
			'tablePrimaryKey'			=>	'pr_id',
			'linkField'					=>	'cpr_element_pr_id',
		)));

		$this->tableSearchFields = $tableDisplayFields;
		
		$this->addField(new SelectField (array(
			'name'			=>	'cpr_pr_id',
			'displayName'	=>	'Product',
			'note'			=>	null,
			'required'		=>	true,
			'verify'		=>	false,
			'unique'		=>	false,
			'size'	=>	'30',	'maxLength'	=>	'255',
			'rows'	=>	'6',	'cols'		=>	'40',
			'linkQuery'             =>  'select * from shopsystem_products join shopsystem_product_extended_options on pro_pr_id = pr_id where pr_id in (select pro_pr_id from shopsystem_product_extended_options) order by pr_ve_id, pr_name',
//			'linkQuery'             =>  'select * from shopsystem_products where pr_ve_id <=> (select pr_ve_id from shopsystem_products where pr_id = '.$ProductLink.') and pr_id in (select pro_pr_id from shopsystem_product_extended_options) order by pr_name',
//			'linkQueryAction'	=>	'ShopSystem_ProductsAdministration.Query',
			'linkQueryValueField'	=>	'pr_id',
			'linkQueryDisplayField'	=>	array( "pr_name", "pro_stock_code" ),
//			'linkQueryParameters'	=>	array('as_id'=>$assetID, 'FilterSQL'=>'pr_ve_id = (select pr_ve_id from shopsystem_products where pr_id = cpr_element_pr_id)'),
		)));
		
		$this->addField(new IntegerField (array(
			'name'			=>	'cpr_qty',
			'displayName'	=>	'Quantity',
			'note'			=>	null,
			'required'		=>	true,
			'verify'		=>	false,
			'unique'		=>	false,
			'defaultValue'	=>	1,
			'size'	=>	'10',	'maxLength'	=>	'5',
			'rows'	=>	'6',	'cols'		=>	'40',
		)));
				
			
	}
	
	

}
	
?>
