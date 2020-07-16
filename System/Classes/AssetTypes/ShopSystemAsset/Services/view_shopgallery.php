<?
	$data = array(
		'SearchCategory'	    =>	$searchCategory,
		'SearchCategoryID'	    =>	$searchCategoryID,
		'SearchCategoryAll'	    =>	$searchCategoryAll,
		'Q_Products'		    =>	$Q_Products,
		'Q_Categories'		    =>	$Q_Categories,
		'AssetPath'			    =>	$assetPath,
		'AssetStore'		    =>	$assetStore,
		'CurrentServer'		    =>	$GLOBALS['cfg']['currentServer'],
		'LastCategory'		    =>	null,
		'PageThru'			    =>	$pageThru->display,
		'this'				    =>	$this,
        'CurrentIndex'          =>  0,
        'IMAGES_PER_ROW'        =>  $images_per_row,
        'ROWS_PER_PAGE'         =>  $rowsPerPage,
        'NumberOfProducts'      =>  $Q_Products->numRows(),
        'POPUP_WIDTH'           =>  $popupWidth,
        'POPUP_HEIGHT'          =>  $popupHeight,
        //done for photoyoo forward and back arrows
        'NextPage'              =>  $nextPage,
        'PreviousPage'          =>  $previousPage,
	);

	// Check for custom layout
	$checkLayout = ss_optionExists('Shop Gallery Layout');

    if ($checkLayout !== false) $asset->display->layout = $checkLayout;
		
	$useCategoryLayouts = ss_optionExists('Shop Category Layouts');

    if ($useCategoryLayouts !== false and strlen($this->ATTRIBUTES['pr_ca_id'])) {
		// map a category (and it's sub categories) to a layout
		//	categoryid:layout,categoryid:layout,categoryid:layout
		$categoryLayouts = array();
		foreach(ListToArray($useCategoryLayouts) as $def) {
			$categoryLayouts[ListFirst($def,":")] = ListLast($def,":");
		}

		$currentCategory = $this->ATTRIBUTES['pr_ca_id'];
		if (array_key_exists($currentCategory,$categoryLayouts)) $asset->display->layout = $categoryLayouts[$currentCategory];	

		$safety = 500;
		while ($currentCategory !== null and !array_key_exists($currentCategory,$categoryLayouts)) {

			$parentCat = getRow("
				SELECT ca_parent_ca_id FROM shopsystem_categories
				WHERE ca_id = ".safe($currentCategory)."
			");	
			$currentCategory = $parentCat['ca_parent_ca_id'];
			
			if (array_key_exists($currentCategory,$categoryLayouts)) $asset->display->layout = $categoryLayouts[$currentCategory];	
			
			$safety--;
			if ($safety < 0) break;
		}
		
	}
	
	// Always link in the shop style sheet
	ss_customStyleSheet($this->styleSheet);
	$this->useTemplate($template,$data);
	
	// clean up a little
	$Q_Products->free();
	$Q_Categories->free();
?>
