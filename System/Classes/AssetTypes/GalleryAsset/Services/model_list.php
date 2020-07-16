<?php

	
	$imagesHTML = array();
	
	$imageOrder = 0;
	foreach ($images as $image) {
		ss_paramKey($image,'title','Unknown');		
		ss_paramKey($image,'url','');
		ss_paramKey($image,'description','');
		ss_paramKey($image,'link','');	
		ss_paramKey($image,'uuid','');	
		$data['ImageName'] = $image['title'];
        $data['description'] = $image['description'];
		$data['Order'] = $imageOrder;
		$data['ImageLocation'] = $directory.$image['url'];
		$data['AssetPath'] = $assetPath;
		$imageOrder++;
		
		array_push($imagesHTML, $this->processTemplate("List_ImageDetail", $data));		
	}
	
	$images_num_per_page = $data['IMAGES_PER_ROW'] * $data['ROWS_PER_PAGE'];	
	
	// Default some values	
	$this->param('CurrentPage','1');	
	// display a page thru
	$backURL = $_SESSION['BackStack']->getURL();
	$pageThru = new Request('PageThru.Display',array(
		'ItemCount'		=>	count($imagesHTML),	
		'ItemsPerPage'	=>	$images_num_per_page,
		'CurrentPage'	=>	$this->ATTRIBUTES['CurrentPage'],
		'PagesPerBlock'	=>	100,
		'URL'			=>	$backURL,
	));	

	
	$startIndex = ($this->ATTRIBUTES['CurrentPage']-1) * $images_num_per_page;
	$endIndex = $startIndex + $images_num_per_page;
	$currentIndex = $startIndex;
	
	
	$table = array();
	
	$table['PageThru'] = $pageThru->display;
	$table['ImagesHTML'] = $imagesHTML;
	$table['ROWS_PER_PAGE'] = $data['ROWS_PER_PAGE'];
	$table['IMAGES_PER_ROW'] = $data['IMAGES_PER_ROW'];
	$table['CurrentIndex'] = $currentIndex;
	
	
	$this->useTemplate("Table", $table);
?>
