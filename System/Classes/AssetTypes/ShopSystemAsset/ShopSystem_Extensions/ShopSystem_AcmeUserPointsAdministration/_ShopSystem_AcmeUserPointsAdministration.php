<?php
requireOnceClass('Administration');
class ShopSystem_AcmeUserPointsAdministration extends Administration {
		
	function exposeServices() {
		return Administration::exposeServicesUsing('ShopSystem_AcmeUserPoints');
	}

	/*function updateStockAvailability() {
		require('model_updateStockAvailability.php');	
	}*/
	
	/*function query($params = array()) {		
//		$params['FilterSQL'] = ' AND pro_is_main = 1 AND pro_pr_id = pr_id';
		ss_paramKey($params,'FilterSQL','');
		$params['FilterSQL'] .= ' AND cpr_pr_id = pr_id';
		$params['FilterTablesSQL'] = 'shopsystem_products';

		$query = parent::query($params);
		
		return $query;
	}*/
	
	/*function delete() {
		// Delete the row
		
		$result = query("
			DELETE FROM shopsystem_product_extended_options 
			WHERE pro_pr_id = {$this->ATTRIBUTES['pr_id']}
		");
		parent::delete();
	}*/
	
	/*function entries() {	
		
		if (array_key_exists($this->tablePrimaryKey,$this->ATTRIBUTES)) $this->primaryKey = $this->ATTRIBUTES[$this->tablePrimaryKey];
		require('EntriesQuery.php');		
		require('EntriesDisplay.php');	
	}*/	
	
	function __construct($assetID = null,$pr_id = null) {
		
		/*if ($assetID === null || is_array($assetID)) {
			if (!strlen($this->assetLink)) {
				if (array_key_exists("as_id", $_REQUEST)) {
					$assetID = $_REQUEST['as_id'];			
				}			
			}
		}*/

		$tableDisplayFields = array('up_us_id','up_points');
		
		
		$tableDisplayFieldTitles = array('User Link','Points');
		
		parent::__construct(array(
			'prefix'					=>	'ShopSystem_AcmeUserPoints',
			'singular'					=>	'User Points Record',
			'plural'					=>	'User Points Records',
			'tableName'					=>	'shopsystem_user_points',
			'tablePrimaryKey'			=>	'up_id',
			'tableDisplayFields'		=>	$tableDisplayFields,
			'tableDisplayFieldTitles'	=>	$tableDisplayFieldTitles,
			'tableOrderBy'				=>	array('up_id' => 'Default'),
		));
//			'hideNewButton'				=>	'Create new product in: <input type="hidden" name="act" value="ShopSystem_CategoryProductsAdministration.New"><select onchange="if (this.selectedIndex != 0) this.form.submit();" name="pr_ca_id">'.$categoriesOptionsHTML.'</select>',
//			'tableDeleteFlag'			=>	'pr_deleted',


		/*$this->setParent(new ParentTable(array(
			'tableName'					=>	'shopsystem_products',
			'tablePrimaryKey'			=>	'pr_id',
			'linkField'					=>	'cpr_element_pr_id',
		)));*/

		$this->tableSearchFields = $tableDisplayFields;
		
		$this->addField(new IntegerField (array(
			'name'			=>	'up_points',
			'displayName'	=>	'Points',
			'note'			=>	null,
			'required'		=>	true,
			'verify'		=>	false,
			'unique'		=>	false,
			'size'	=>	'10',	'maxLength'	=>	'255',
			'rows'	=>	'6',	'cols'		=>	'40',
		)));
		
		$this->addField(new DateField (array(
			'name'			=>	'up_expires',
			'displayName'	=>	'Expires',
			'note'			=>	null,
			'required'		=>	true,
			'verify'		=>	false,
			'unique'		=>	false,
			'defaultValue'	=>	1,
			'showCalendar'	=>	true,
			'size'	=>	'10',	'maxLength'	=>	'10',
			'rows'	=>	'6',	'cols'		=>	'40',
		)));
				
			
	}
	
	

}
	
?>
