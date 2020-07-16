<?php 
	
	$this->param('PopupLayout','popup');
	
	$asset->display->layout = $this->ATTRIBUTES['PopupLayout'];
	
	$this->param('Image');
	$data = array();
	$current = $this->ATTRIBUTES['Image'];
	$image = $images[$this->ATTRIBUTES['Image']];
	$image['Directory'] = $directory;
	
	$previous = $current -1;
	if ($previous < 0) {
		$previous = count($images) - 1;
	} 
	$image['Previous'] = $previous;
	
	$next = $current + 1;
	if ($next >= count($images)) {
		$next = 0;
	} 
	$image['Next'] = $next;
	$image['AssetPath'] = $assetPath;
	
	$this->useTemplate("ImagePopup", $image);
	
?>