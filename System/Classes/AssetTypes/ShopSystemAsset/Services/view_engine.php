<? 
	$data = array(
		'SearchCategory'	=>	$searchCategory,
		'SearchCategoryID'	=>	$searchCategoryID,
		'SearchCategoryAll'	=>	$searchCategoryAll,	
		'Q_Products'		=>	$Q_Products,
		'Q_Taglist'			=>	$Q_Taglist,
		'Q_Categories'		=>	$Q_Categories,
		'show_categories'   =>  $show_categories,
		'AssetPath'			=>	$assetPath,
		'AssetStore'		=>	$assetStore,
		'CurrentServer'		=>	$GLOBALS['cfg']['currentServer'],
		'LastCategory'		=>	null,
		'PageThru'			=>	$pageThru->display,
		'OptionFieldsArray'	=>	$optionFieldsArray,
		'this'				=>	$this,
		'TaxCountryNoteHTML'	=>	$this->getTaxCountryNote(),
		'CurrencyConverterHTML'	=>	$this->currencyConverter(),	
	);
	
	// Check for custom layout
	$checkLayout = ss_optionExists('Shop Engine Layout');
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

		$data['ca_id'] = $currentCategory;
	}
	
	// Always link in the shop style sheet
	// You dumbass, you can only do this in the 'head' section of the document
//	ss_customStyleSheet($this->styleSheet);
	$this->useTemplate($template,$data);
//	ss_log_message( "Using template $template with data..." );
//	ss_log_message_r(  'Log:'.__FILE__.':'.__LINE__, $data );
	
	// clean up a little
	$Q_Products->free();
	$Q_Categories->free();
?>
