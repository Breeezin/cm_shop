<?php 
	$this->param("pr_id",'');
	if (strlen($this->ATTRIBUTES['pr_id'])) {
		$this->ATTRIBUTES['Template'] = 'FeatureProductDetail';
		$this->ATTRIBUTES['Product'] = $this->ATTRIBUTES['pr_id'];	
	} else {
		if (!array_key_exists('FeatureProductsUsed', $GLOBALS)) {		
			$GLOBALS['FeatureProductsUsed'] = array();
		}
		$used = ArrayToList($GLOBALS['FeatureProductsUsed']);
		$whereSQL = "";
		if (strlen($used)) {
			$whereSQL = " AND pr_id NOT IN ($used)";
		}
		$Q_Featured = query("
				SELECT pr_id FROM shopsystem_products 
				WHERE 
					pr_featured = 1 
					AND pr_deleted IS NULL
					AND pr_as_id = ".$asset->getID()."
					$whereSQL
		");
		if ($Q_Featured->numRows() == 0) {
			$Q_Featured = query("
				SELECT pr_id FROM shopsystem_products 
				WHERE 
					pr_featured IS NULL 
					AND pr_deleted IS NULL
					AND pr_as_id = ".$asset->getID()."
					$whereSQL
			");
		} 
		$allFeaturedPrIDs = $Q_Featured->columnValuesArray('pr_id');
		$howMany = count($allFeaturedPrIDs);
		if ($howMany == 0) {
		} else if (count($allFeaturedPrIDs) > 1) {
			$randIndex = rand(0, ($howMany-1));
		} else {
			$randIndex = 0;
		}		
		if ($howMany != 0) {
			$this->ATTRIBUTES['Template'] = 'FeatureProductDetail';		
			$this->ATTRIBUTES['Product'] = $allFeaturedPrIDs[$randIndex];
			array_push($GLOBALS['FeatureProductsUsed'],$allFeaturedPrIDs[$randIndex]);
		}
	}
	
	
	if (array_key_exists('Product', $this->ATTRIBUTES) and strlen($this->ATTRIBUTES['Product'])) {		
		require('query_detail.php');
		$currentRow=0;	
		// Get the thumbsize
		$thumbSize = "120x120";
		if (strlen(ss_optionExists('Shop Engine Image Size'))) {
			$thumbSize = ss_optionExists('Shop Engine Image Size');
		}
		if (strlen(ss_optionExists('Shop Featured Product Image Size'))) {
			$thumbSize = ss_optionExists('Shop Featured Product Image Size');
		}
		$pricesType = 'TableHTML';
		while ($row = $Q_Product->fetchRow()) {
			// This must be done at the start of this loop for every product
			$niceProductName = ss_alphaNumeric($row['pr_name'],'_');
			$niceCategoryName = ss_alphaNumeric($row['ca_name'],'_');
			$Q_Product->setCell('NiceProductName',$niceProductName,$currentRow);
			$Q_Product->setCell('NiceCategoryName',$niceCategoryName,$currentRow);
			$Q_Product->setCell('ProductDetailLink',$assetPath."/Service/Detail/Product/{$row['pr_id']}/{$niceCategoryName}/{$niceProductName}.html",$currentRow);
						
			// Figure out the image
			if (strlen($row['pr_image1_thumb'])) {
				$image = $assetStore."/ProductImages/".$row['pr_image1_thumb'];
			} elseif (strlen($row['pr_image1_normal'])) {
				$image = 'index.php?act=ImageManager.get&Size='.$thumbSize.'&Image='.ss_URLEncodedFormat($assetStore."/ProductImages/".$row['pr_image1_normal']);
			} elseif (strlen($row['pr_image1_large'])) {
				$image = 'index.php?act=ImageManager.get&Size='.$thumbSize.'&Image='.ss_URLEncodedFormat($assetStore."/ProductImages/".$row['pr_image1_large']);
			} else {
				$image = null;	
			}
			$Q_Product->setCell('Image',$image,$currentRow);
	
			// Figure out the prices table		
			
			$currentRow++;
		}
		require('view_detail.php');
	}
?>