<?php
	$data = array(
		'Q_Product'			=>	$Q_Product,
		'LastSearch'		=>	array_key_exists('LastSearch',$_SESSION['Shop'])?$_SESSION['Shop']['LastSearch']:null,
		'AssetPath'			=>	$assetPath,
		'AssetStore'		=>	$assetStore,
		'CurrentServer'		=>	$GLOBALS['cfg']['currentServer'],
		'CategoryBreadCrumbs'	=>	$categoryBreadCrumbs,
		'AttributesHTML'	=>	$this->processTemplate('Attributes', $attributes),
		'OptionsHTML'		=>	$this->getOptions($product,$fieldsArray),
		'ExtraInfo'			=>	'',
		'TaxCountryNoteHTML'	=>	$this->getTaxCountryNote(),
		'CurrencyConverterHTML'	=>	$this->currencyConverter(),
	);

	$useCategoryLayouts = ss_optionExists('Shop Category Layouts');
	if ($useCategoryLayouts !== false) {
		// map a category (and it's sub categories) to a layout
		//	categoryid:layout,categoryid:layout,categoryid:layout
		$categoryLayouts = array();
		foreach(ListToArray($useCategoryLayouts) as $def) {
			$categoryLayouts[ListFirst($def,":")] = ListLast($def,":");
		}

		$row = $Q_Product->fetchRow();
		$currentCategory = $row['pr_ca_id'];
		if (array_key_exists($currentCategory,$categoryLayouts)) $asset->display->layout = $categoryLayouts[$currentCategory];

		$safety = 500;
		while ($currentCategory !== null and !array_key_exists($currentCategory,$categoryLayouts)) {

			$parentCat = getRow("
				SELECT ca_parent_ca_id FROM shopsystem_categories
				WHERE ca_id = ".safe($currentCategory)."
			");
			$currentCategory = $parentCat['ca_parent_ca_id'];

			if (array_key_exists($currentCategory,$categoryLayouts)) $asset->display->layout = $categoryLayouts[$currentCategory];
            //briar - change for Lapco here
            //if its being displayed as a featured product, don't bring in the overall template
            if (array_key_exists('Service', $this->ATTRIBUTES)){
                if ($this->ATTRIBUTES['Service'] == 'FeaturedProduct'){
                    $asset->display->layout = 'None';
                }
            }
			$safety--;
			if ($safety < 0) break;
		}

	}

	if (!array_key_exists('Template', $this->ATTRIBUTES)) {

/*
		$Q_ViewProductStats = query("
			INSERT INTO shopsystem_statistics
			(sst_timestamp, sst_pr_id, sst_ca_id, sst_country)
			VALUES
			(NOW(), '{$product['pr_id']}', '".escape($categoryBreadCrumbsNoHtml)."', '".escape(ss_getCountry(null,'cn_name'))."')
		");
*/

		$asset->display->title = $product['pr_name'];

		// Check for custom layout
		$checkLayout = ss_optionExists('Shop Detail Layout');
		if ($checkLayout !== false) $asset->display->layout = $checkLayout;

		// Always link in the shop style sheet
		ss_customStyleSheet($this->styleSheet);
		$this->useTemplate("Detail",$data);
	} else {
		$this->useTemplate($this->ATTRIBUTES['Template'],$data);
	}

?>
