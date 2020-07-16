<?
	$data = array(
		'AssetPath'	=>	ss_withoutPreceedingSlash($asset->getPath()),
		'Error'		=>	$error,
		'LastSearch'	=>	array_key_exists('LastSearch',$_SESSION['Shop'])?$_SESSION['Shop']['LastSearch']:null,
	);
	
	$data['Q_Categories'] = $Q_Categories;
	if (array_key_exists('DoAction',$this->ATTRIBUTES) and !strlen($error)) {
		$data['Done'] = true;
		$asset->display->title = 'Complete';
	} else {
		$asset->display->title = 'Personal Stock Alert';
	}
	
	// Check for custom layout
	$checkLayout = ss_optionExists('Shop Add To Wish List Layout');
	if ($checkLayout !== false) $asset->display->layout = $checkLayout;
	
	ss_customStyleSheet($this->styleSheet);
	$this->useTemplate('AddToWishList',$data);	
	
?>