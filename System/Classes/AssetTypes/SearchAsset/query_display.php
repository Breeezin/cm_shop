<?php 
	$this->param("SearchType", "");
	$this->param("AST_SEARCH_KEYWORDS", "");
		
	$assetID = $asset->getID();
	$assetPath = ss_EscapeAssetPath(ss_withoutPreceedingSlash($asset->getPath()));

	// get the number of items to display per page
	ss_paramKey($asset->cereal,$this->fieldPrefix.'ITEMSPERDISPLAY',100000);
	$perDisplay = $asset->cereal[$this->fieldPrefix.'ITEMSPERDISPLAY'];
	// check whether the search displays the type filter
	ss_paramKey($asset->cereal,$this->fieldPrefix.'SHOWTYPEFILTER',0);
	$hasTypeFilter = $asset->cereal[$this->fieldPrefix.'SHOWTYPEFILTER']==1 ? true:false;
	
	// get the types that are used for search
	ss_paramKey($asset->cereal,$this->fieldPrefix.'TYPES',array());
	$types = $asset->cereal[$this->fieldPrefix.'TYPES']; // used to display asset type can be searchable
	$astyIDs = ArrayToList($types); // used to get as_type name	
	
	
	// check whether the asset has permisstion to search the online shop products/data collection asset
	$searchShopProduct = false;	
	$searchDataCollection = false;	
	
	$Q_OnlineShopType = getRow("SELECT * FROM asset_types WHERE at_name LIKE 'ShopSystem'");
	if (array_search($Q_OnlineShopType['at_id'], $types) !== false) {	
		if (!strlen($this->ATTRIBUTES['SearchType'])) {			
			$searchShopProduct = true;
		} else if ($this->ATTRIBUTES['SearchType'] == $Q_OnlineShopType['at_id']) {
			$searchShopProduct = true;
		}
				
	}

	$Q_DataCollectionType = getRow("SELECT * FROM asset_types WHERE at_name LIKE 'DataCollection'");
	if (array_search($Q_DataCollectionType['at_id'], $types) !== false) {	
		
		if (!strlen($this->ATTRIBUTES['SearchType'])) {
			$searchDataCollection = true;
		} else if ($this->ATTRIBUTES['SearchType'] == $Q_DataCollectionType['at_id']) {
			$searchDataCollection = true;
		}			
	}
	
	
	$Q_SearchTypes = query("SELECT * FROM asset_types WHERE at_id IN ({$astyIDs})");
	$showTypes = array(); 
	$assetTypes = array();
	$assetTypeNames= array();
	while($aType = $Q_SearchTypes->fetchRow()) {
		$showTypes["{$aType['at_display']}"] = $aType['at_id'];
		$assetTypeNames["{$aType['at_name']}"] = $aType['at_display'];
		$assetTypes["{$aType['at_id']}"] = $aType['at_name'];
	}
	
	
	// user can only search the assets that he have persmission to access/view
	// now collect all assets that user can access	
	$Q_Assets = query("
			SELECT aug_as_id, MAX(aug_can_use) AS HasAccess 
			FROM asset_user_groups
			WHERE aug_ug_id IN (".ArrayKeysToList($_SESSION['User']['user_groups']).")							
			GROUP BY AssetLink
	");
	$shopAssetIDs = '';
	$Q_ShopAssets = query("SELECT as_id FROM assets WHERE as_type LIKE 'ShopSystem' AND (as_deleted IS NULL OR as_deleted = 0)");
	
	$dataAssetIDs = array();
	$Q_DataAssets = query("SELECT as_id FROM assets WHERE as_type LIKE 'DataCollection' AND (as_deleted IS NULL OR as_deleted = 0)");
	
	
	$searchAssets = array();
	while($aAsset = $Q_Assets->fetchRow()) {
		if ($aAsset['HasAccess']) {
			
			if ($searchShopProduct || $searchDataCollection) {			
				
				$isShopAsset = false;
				while($shopAsset = $Q_ShopAssets->fetchRow()) {
					if ($shopAsset['as_id'] == $aAsset['AssetLink']) {
						$isShopAsset = true;						
						break;
					}
				}
				if ($isShopAsset) {
					ss_comma($shopAssetIDs);
					$shopAssetIDs .= $aAsset['AssetLink'];
				} else {
					$isDataAsset = false;
					while($dataAsset = $Q_DataAssets->fetchRow()) {
						if ($dataAsset['as_id'] == $aAsset['AssetLink']) {
							$isDataAsset = true;
							break;
						}
					}
					if ($isDataAsset) {
						array_push($dataAssetIDs,$aAsset['AssetLink']);
					} else {
						
						$searchAssets[$aAsset['AssetLink']] = 1;
					}								
				}
			} else {				
				$searchAssets[$aAsset['AssetLink']] = 1;
				
			}
		}
	}
	//
	ss_paramKey($asset->cereal,$this->fieldPrefix.'ENABLE_ITEMS','');
	ss_paramKey($asset->cereal,$this->fieldPrefix.'ASSETS','');
	
	if ($asset->cereal[$this->fieldPrefix.'ENABLE_ITEMS'] == '1' and strlen($asset->cereal[$this->fieldPrefix.'ASSETS'])) {
		$enabledAssets = unserialize($asset->cereal[$this->fieldPrefix.'ASSETS']);
		$tempSearchAssets = array();
		$tempAssetIDs = array_keys($searchAssets);
		foreach($enabledAssets as $aAsset) {
			$temp = ListToArray($aAsset, '|');
			if ($temp[2] == 1) {
				$subAssets = ss_GetBranchAssetsArray($temp[0],true);
				foreach ($subAssets as $subAsset) {
					if (array_search($subAsset,$tempAssetIDs) !== false) {
						$tempSearchAssets[$subAsset] = 1;
					}
				}
				
			} 
			if (array_search($temp[0],$tempAssetIDs) !== false) {
				$tempSearchAssets[$temp[0]] = 1;
			}
		}
		$searchAssets = $tempSearchAssets;
	}
	
	// where sql 
	// searchkeywods
	
	$keywords = ListToArray(ss_fixKeywords($this->ATTRIBUTES['AST_SEARCH_KEYWORDS']),' ');	
	$whereSQL =  " AND  ( 1 ";		
	foreach($keywords as $word) {
		$word = escape($word);
		$firstChrar = substr($word,0,1);
		$restWord = substr($word,1);
		if ($firstChrar == '-' and strlen($restWord) and ss_optionExists('Enable Search Excluding')) {
			$whereSQL .=  "AND ( (as_search_keywords NOT LIKE '%{$restWord}%' OR as_search_keywords IS NULL) 
								AND (as_search_description NOT LIKE '%{$restWord}%' OR as_search_description IS NULL) 
								AND (as_search_content NOT LIKE '%{$restWord}%' OR as_search_content IS NULL)		
								AND (as_name NOT LIKE '%{$restWord}%' OR as_name IS NULL) 		
								)";
 		} else {
			$whereSQL .=  "AND (as_search_keywords LIKE '%{$word}%' 
								OR as_search_description LIKE '%{$word}%' 
								OR as_search_content LIKE '%{$word}%' 		
								OR as_name LIKE '%{$word}%' 		
								)";
 		}
	}
	$whereSQL .=  ")";
		
	// if search has filter then search are base on the selected asset type
	if ($hasTypeFilter AND strlen($this->ATTRIBUTES['SearchType'])) {		
		$searchType = $assetTypes[$this->ATTRIBUTES['SearchType']];
		$whereSQL .= " AND ( 1 AND as_type LIKE '{$searchType}')";
	} else {
		$whereSQL .= " AND ( 1 AND as_type IN (";
		$temp = '';
		foreach ($assetTypes as $aType) {
			ss_comma($temp);
			$temp .= "'$aType'";
		}
		$whereSQL .= $temp."))";
	}
	$allList = null;
	if (array_key_exists('Stats', $this->ATTRIBUTES) OR array_key_exists('CurrentPage', $this->ATTRIBUTES)) {
		
		$assetIDs = ArrayKeysToList($searchAssets);		
		$allList = array();
		$Q_ListProductQuery = null;
		// get where statement for product search if the shop asset is searchable
		
		if ($searchShopProduct and strlen($shopAssetIDs)) {
			
			$productkeywordsSQL = '1=1';
			$productCatkeywordsSQL = '1=1';
			if (strlen($this->ATTRIBUTES['AST_SEARCH_KEYWORDS'])) {
				$productkeywordsSQL = "(1=1";
				$productCatkeywordsSQL = "(1=1";
				$keywords = ListToArray(ss_fixKeywords($this->ATTRIBUTES['AST_SEARCH_KEYWORDS']),' ');
				foreach($keywords as $keyword) {
					$keyword = escape($keyword);
					$firstChrar = substr($keyword,0,1);
					$restWord = substr($keyword,1);					
					if ($firstChrar == '-' and strlen($restWord) and ss_optionExists('Enable Search Excluding')) {
						$productkeywordsSQL .= " AND (1=1";
						$productCatkeywordsSQL .= " AND (1=1";
						foreach (array('pr_name','pr_keywords','pr_short','pr_long') as $field) {
							$productkeywordsSQL .= " AND ($field NOT LIKE '%".escape($restWord)."%' OR $field IS NULL)";							
						}	
						$productCatkeywordsSQL .= " AND (ca_name NOT LIKE '%".escape($restWord)."%' OR ca_name IS NULL)";		
					} else {
						$productkeywordsSQL .= " AND (1=0";
						$productCatkeywordsSQL .= " AND (1=0";
						foreach (array('pr_name','pr_keywords','pr_short','pr_long') as $field) {
							$productkeywordsSQL .= " OR $field LIKE '%".escape($keyword)."%'";							
						}		
						$productCatkeywordsSQL .= " OR ca_name LIKE '%".escape($keyword)."%'";	
					}			
											
					$productkeywordsSQL .= ")";
					$productCatkeywordsSQL .= ")";
				}
				$productkeywordsSQL .= ")";
				$productCatkeywordsSQL .= ")";
			}
			
			if( ss_AuthdCustomer( ) )
				$zonefield = 'pr_authd_sales_zone';
			else
				$zonefield = 'pr_sales_zone';

			if( strlen($_SESSION['ForceCountry']['cn_sales_zones']) )
				$external = "and $zonefield in ({$_SESSION['ForceCountry']['cn_sales_zones']})";
			else
				$external = '';

			$Q_ListProductQuery = query("
				SELECT pr_id, pr_name, pr_short, pr_long, pr_as_id, as_name 
				FROM shopsystem_products, shopsystem_product_extended_options, assets, shopsystem_categories
				WHERE pr_deleted IS NULL	
					AND pro_pr_id = pr_id
					AND pr_ca_id = ca_id
					".ss_shopRestrictedCategoriesSQL()."
					AND pr_offline IS NULL
					AND pr_is_service = 'false'
					AND $productkeywordsSQL
					$external
					AND as_id = pr_as_id
					AND pr_offline IS NULL and pr_is_service = 'false'
				ORDER BY (pro_stock_available > 0) DESC, pro_price DESC, pr_name
			");
				// ORDER BY pr_name
			if ($Q_ListProductQuery->numRows()) {
				while($temp = $Q_ListProductQuery->fetchRow()) {
					$shortDesc = strip_tags($temp['pr_short']);
					$longDesc = strip_tags($temp['pr_long']);
					$desc = $shortDesc;
					if (strlen($longDesc) > strlen($shortDesc) ) {
						$desc = $longDesc;	
					}
					array_push($allList, array('as_name' => $temp['pr_name'], 'as_id' => $temp['pr_id'],  'as_type' =>'ShopSystem','as_search_description'=>$desc, 'as_search_content'=>$desc, 'AssetLink' => $temp['pr_as_id'], 'AssetParentName' => $temp['as_name']));
				}			
			}
			$Q_ListCatQuery = query("
				SELECT ca_id, ca_name, ca_as_id, as_name 
				FROM shopsystem_categories, assets
				WHERE $productCatkeywordsSQL
					".ss_shopRestrictedCategoriesSQL()."
					AND as_id = ca_as_id
				ORDER BY ca_name
			");
			if ($Q_ListCatQuery->numRows()) {
				while($temp = $Q_ListCatQuery->fetchRow()) {					
					$desc = $temp['ca_name'];										
					array_push($allList, array('as_name' => $temp['ca_name'], 'as_id' => $temp['ca_id'],  'as_type' =>'ShopSystemCat','as_search_description'=>$desc, 'as_search_content'=>$desc, 'AssetLink' => $temp['ca_as_id'], 'AssetParentName' => $temp['as_name']));
				}			
			}
			
		}
		
		
		if (ss_optionExists("Schedule assets")) {
			$whereSQL .= " AND (AssetOnline IS NULL OR AssetOnline = '' OR (AssetOnline = 'Date' AND AssetOnlineDate < NOW()) )";
			$whereSQL .= " AND (AssetOffline IS NULL OR AssetOffline = '' OR (AssetOffline = 'Date' AND AssetOfflineDate > NOW()) )";
		}
		
		$Q_ListAssetQuery = query("
				SELECT  as_name,as_id, as_type,as_search_content, as_search_description, as_id AS AssetLink, as_name AS AssetParentName 
				FROM assets
				WHERE 1
				$whereSQL
				AND as_id IN ($assetIDs)
				AND as_id != ".ss_systemAsset("404 Error")."
				AND (as_deleted IS NULL OR as_deleted = 0)
				AND (as_not_allowed_search != 1)
				ORDER BY as_name ASC
		");	
		/*
		ss_DumpVar("
				SELECT  as_name,as_id, as_type,as_search_content, as_search_description, as_id AS AssetLink, as_name AS AssetParentName 
				FROM assets
				WHERE 1
				$whereSQL
				AND as_id IN ($assetIDs)
				AND as_id != ".ss_systemAsset("404 Error")."
				AND (as_deleted IS NULL OR as_deleted = 0)
				AND (as_not_allowed_search != 1)
				ORDER BY as_name ASC
		", $Q_ListAssetQuery->numRows(), true);
		*/
		while($temp = $Q_ListAssetQuery->fetchRow()) {
			array_push($allList, $temp);
		}
		
		
		//ss_DumpVarDie($dataAssetIDs, $searchDataCollection);
		if ($searchDataCollection and count($dataAssetIDs)) {			
			$keywords = ListToArray(ss_fixKeywords($this->ATTRIBUTES['AST_SEARCH_KEYWORDS']),' ');
			$datakeywordsSQL = '1=1';
			if (strlen($this->ATTRIBUTES['AST_SEARCH_KEYWORDS'])) {
				$datakeywordsSQL = "(1=1";
				
				foreach($keywords as $keyword) {					
					$firstChrar = substr($keyword,0,1);
					$restWord = substr($keyword,1);
					if ($firstChrar == '-' and strlen($restWord) and ss_optionExists('Enable Search Excluding')) {
						$datakeywordsSQL .= " AND (1=1";
						foreach (array('DaCoSearch') as $field) {
							$datakeywordsSQL .= " AND ($field NOT LIKE '%".escape($restWord)."%' OR $field IS NULL)";	
						}	
						$datakeywordsSQL .= ")";
					} else {
						$datakeywordsSQL .= " AND (1=0";
						foreach (array('DaCoSearch') as $field) {
							$datakeywordsSQL .= " OR $field LIKE '%".escape($keyword)."%'";	
						}	
						$datakeywordsSQL .= ")";
					}
				}
				$datakeywordsSQL .= ")";
			}
			foreach($dataAssetIDs as $dataID) {
				$Q_Asset = getRow("SELECT as_name FROM assets WHERE as_id = $dataID");
				$Q_ListDataQuery = query("
					SELECT DaCoID, DaCoSearch FROM DataCollection_$dataID
					WHERE $datakeywordsSQL
					ORDER BY DaCoSearch
				");
				if ($Q_ListDataQuery->numRows()) {
					while($temp = $Q_ListDataQuery->fetchRow()) {												
						$desc = strip_tags($temp['DaCoSearch']);	
						$name = '';
						if(strlen($desc)) {																	
							$name = substr($desc,0, 10)."...";						
						}
						array_push($allList, array('as_name' => $name, 'as_id' => $temp['DaCoID'],  'as_type' =>'DataCollection','as_search_description'=>$desc, 'as_search_content'=>$desc, 'AssetLink' => $dataID, 'AssetParentName' => $Q_Asset['as_name']));
					}
					
				}
			}
		}
	}
	
	if ($allList != null and ($searchShopProduct || $searchDataCollection)) {
		asort ($allList);
		reset ($allList);		
	}	
?>
