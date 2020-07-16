<?php
if (ss_optionExists('Shop Gallery')) {
	$_SESSION['Shop']['LastSearch'] = getBackURL();
	
	$this->param('pr_ca_id','');

	$this->param('RowsPerPage', '10');
	$this->param('CurrentPage','1');
	$this->param('PagesPerBlock','10');

    ss_paramKey($asset->cereal,'AST_SHOPSYSTEM_GALLERY_THUMBNAIL_WIDTH','100');
    ss_paramKey($asset->cereal,'AST_SHOPSYSTEM_GALLERY_THUMBNAIL_HEIGHT','100');
    ss_paramKey($asset->cereal,'AST_SHOPSYSTEM_GALLERY_IMAGES_PER_ROW','4');
    ss_paramKey($asset->cereal,'AST_SHOPSYSTEM_GALLERY_ROWS_PER_PAGE','3');
    ss_paramKey($asset->cereal,'AST_SHOPSYSTEM_GALLERY_POPUP_WIDTH','700');
    ss_paramKey($asset->cereal,'AST_SHOPSYSTEM_GALLERY_POPUP_HEIGHT','600');
    ss_paramKey($asset->cereal,'AST_SHOPSYSTEM_GALLERY_PAGE_TITLE',$asset->display->title);

    $thumbSize          = $asset->cereal['AST_SHOPSYSTEM_GALLERY_THUMBNAIL_WIDTH'] . 'x' . $asset->cereal['AST_SHOPSYSTEM_GALLERY_THUMBNAIL_HEIGHT'];
    $images_per_row     = $asset->cereal['AST_SHOPSYSTEM_GALLERY_IMAGES_PER_ROW'];
    $rowsPerPage        = $asset->cereal['AST_SHOPSYSTEM_GALLERY_ROWS_PER_PAGE'];
    $popupWidth         = $asset->cereal['AST_SHOPSYSTEM_GALLERY_POPUP_WIDTH'];
    $popupHeight        = $asset->cereal['AST_SHOPSYSTEM_GALLERY_POPUP_WIDTH'];

    $asset->display->title = $asset->cereal['AST_SHOPSYSTEM_GALLERY_PAGE_TITLE'];

	$this->param('GroupBy','Category');
	$this->param('OrderBy','Default');
	
	$this->param('Template','ShopGallery');

	// Default to the engine template
	$template = $this->ATTRIBUTES['Template'];
	$pricesType = 'TableHTML';

	// For displaying a select list later
	$result = new Request("Security.Sudo",array('Action'=>'Start'));
	$allCategoriesResult = new Request("shopsystem_categories.QueryAll",array('as_id'	=>	$asset->getID()));
	$Q_Categories = $allCategoriesResult->value;
	//ss_DumpVarDie($Q_Categories);
	
	
	// Figure out what categories to search 
	if (strlen($this->ATTRIBUTES['pr_ca_id'])) {
		if ($this->ATTRIBUTES['pr_ca_id'] == '..') die('Invalid pr_ca_id');
		$subCategoriesResult = new Request("shopsystem_categories.QueryAll",array(
			'as_id'		=>	$asset->getID(),
			'ca_id'			=>	$this->ATTRIBUTES['pr_ca_id'],
		));
		$categoriesSQL = "pr_ca_id IN (".$subCategoriesResult->value->columnValuesList('ca_id',',','').")";
	} else {
		$categoriesSQL = "1=1";
	}

	// Group products by category
	$orderBySQL = 'ORDER BY 1=1';
	if ($this->ATTRIBUTES['GroupBy'] == 'Category') {
		$cats = '';
		if (strlen($this->ATTRIBUTES['pr_ca_id'])) {
			$catsArray = $subCategoriesResult->value->columnValuesArray('ca_id');
		} else {
			$catsArray = $allCategoriesResult->value->columnValuesArray('ca_id');
		}
		foreach($catsArray as $caID) {
			$cats .= ",ca_id!=$caID";
		}
		$orderBySQL .= $cats;
	} else if ($this->ATTRIBUTES['GroupBy'] == 'QuickOrderCategory') {
		if (ListLen($this->ATTRIBUTES['pr_qoc_id']) > 1) {
			foreach (ArrayToList($this->ATTRIBUTES['PrQuickOrderCategory']) as $quickOrderCategory) {
				$orderBySQL .= ',ca_id!='.safe($quickOrderCategory);
			}
		} else {
			$orderBySQL .= ',ca_id!='.safe($this->ATTRIBUTES['pr_qoc_id']);
		}
	}

	// Order products by...
	switch ($this->ATTRIBUTES['OrderBy']) {
		case 'Default'	:	
			$orderBySQL .= ",pr_sort_order,pr_name";
			break;
		case 'ProductName' :
			$orderBySQL .= ",pr_name";
			break;
		case 'Price' :
			$orderBySQL .= "";
			break;
		case 'Random' :
			$orderBySQL = "ORDER BY Rand(Now())";
	}
	
	// Search for the products
	$maxRows = $rowsPerPage * $images_per_row;
	$startRow = ($this->ATTRIBUTES['CurrentPage']-1)*$maxRows;

	$Q_Products = query("
		SELECT * FROM shopsystem_products, shopsystem_categories
		WHERE pr_ca_id = ca_id
			AND pr_deleted IS NULL
			".(ss_optionExists('Shop Products Offline')?'AND pr_offline IS NULL':'')."
			AND PrGallery = 1
            AND $categoriesSQL
		$orderBySQL
		LIMIT $startRow,$maxRows
	");

	// Count how many products in total for this search query 
	$productCount = getRow("
		SELECT COUNT(*) AS Total FROM shopsystem_products, shopsystem_categories
		WHERE pr_ca_id = ca_id
			AND pr_deleted IS NULL
			".(ss_optionExists('Shop Products Offline')?'AND pr_offline IS NULL':'')."
            AND PrGallery = 1
            AND $categoriesSQL
	");

	// Genereate a page thru
	$backURL = $_SESSION['BackStack']->getURL();
	$pageThru = new Request('PageThru.Display',array(
		'ItemCount'		=>	$productCount['Total'],
		'ItemsPerPage'	=>	$maxRows,
		'CurrentPage'	=>	$this->ATTRIBUTES['CurrentPage'],
		'PagesPerBlock'	=>	$this->ATTRIBUTES['PagesPerBlock'],
		'URL'			=>	$backURL."&NoStats=Yes",
	));
    //done for photoyoo forward and back arrows
    $numberOfPages  = $productCount['Total']/$maxRows;
    $changePage     = $backURL."&NoStats=Yes&CurrentPage=";
    $nextPage       = ($this->ATTRIBUTES['CurrentPage'] < $numberOfPages) ? $changePage . ($this->ATTRIBUTES['CurrentPage'] + 1) : null;
    $previousPage   = (($this->ATTRIBUTES['CurrentPage'] - 1) == 0) ? null : $changePage . ($this->ATTRIBUTES['CurrentPage'] - 1);

	// Add some extra columns :-)
	$Q_Products->addColumn('NiceProductName');
	$Q_Products->addColumn('NiceCategoryName');
	$Q_Products->addColumn('ProductDetailLink');
	$Q_Products->addColumn('Image');
	$Q_Products->addColumn('ActualPrice');
	$Q_Products->addColumn('PricesHTML');	
	$Q_Products->addColumn('OptionsHTML');
	$Q_Products->addColumn('AttributesHTML');


	// Insert the data 
	$currentRow = 0;
	$assetPath = ss_EscapeAssetPath(ss_withoutPreceedingSlash($asset->getPath()));
	$assetStore = ss_EscapeAssetPath(ss_storeForAsset($asset->getID()));
	
	// Check for discount codes
	$discountCodes = ss_optionExists('Shop Discount Codes');
	
	while ($row = $Q_Products->fetchRow()) {
		if (array_key_exists('ShowFirstProduct',$this->ATTRIBUTES)) {
			locationRelative($assetPath."/Service/Detail/Product/{$row['pr_id']}");
		}
		// This must be done at the start of this loop for every product
		$niceProductName = ss_alphaNumeric($row['pr_name'],'_');
		$niceCategoryName = ss_alphaNumeric($row['ca_name'],'_');
		$Q_Products->setCell('NiceProductName',$niceProductName,$currentRow);
		$Q_Products->setCell('NiceCategoryName',$niceCategoryName,$currentRow);
		$Q_Products->setCell('ProductDetailLink',$assetPath."/Service/Detail/Product/{$row['pr_id']}/{$niceCategoryName}/{$niceProductName}.html",$currentRow);

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
		$Q_Products->setCell('Image',$image,$currentRow);

		if (!$discountCodes) $row['pr_dig_id'] = null;
		
		// Figure out the prices table
		$pricesHTML = $this->getPrice($row['pr_id'],$row['pr_dig_id'],null,$pricesType);
		$Q_Products->setCell('PricesHTML',$pricesHTML,$currentRow);
		$Q_Products->setCell('ActualPrice',$this->getPrice($row['pr_id'],$row['pr_dig_id'],null,'PriceOnly'),$currentRow);
		
		$currentRow++;
	}
	$searchCategoryAll = null;

	$setWindowTitle = false;
	$windowTitle = "";
	
	$assetTitle = "";

    $searchCategory = "Search Results";
    $searchCategoryID = null;
	if (strlen($this->ATTRIBUTES['pr_ca_id'])) {
		$row = getRow("SELECT * FROM shopsystem_categories WHERE ca_id = ".safe($this->ATTRIBUTES['pr_ca_id']));
		//$row = $Q_Categories->getRow($Q_Categories->getRowWithValue('ca_id',$this->ATTRIBUTES['pr_ca_id']));
		$searchCategory = $row['ca_name'];
		if (strlen($row['ca_window_title'])) {
			$asset->layout['LYT_WINDOWTITLE'] = $row['ca_window_title'];
			$setWindowTitle = true;
		}
		$searchCategoryAll = $row;
		$searchCategoryID = $this->ATTRIBUTES['pr_ca_id'];
	}
	
	$result = new Request("Security.Sudo",array('Action'=>'Finish'));
} else {
    if (ss_isItUs()){
        die ("Put 'Shop Gallery' in the configuration and add a field called PrGallery (tinyint) to the Product table");
    } else {
        die ("Sorry, the Shop Gallery is not available");
    }
}
?>
