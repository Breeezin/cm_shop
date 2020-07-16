<?
	$data = array(
		'AssetPath'	    =>	ss_withoutPreceedingSlash($asset->getPath()),
		'Error'		    =>	$error,
		'LastSearch'	=>	array_key_exists('LastSearch',$_SESSION['Shop'])?$_SESSION['Shop']['LastSearch']:null,
        'Q_Users'       =>  $Q_Users,
        'Q_Categories'  =>  $Q_Categories,
        'isAdmin'       =>  $isAdmin,
    );

    if (array_key_exists('DoAction',$this->ATTRIBUTES) and !strlen($error)) {
		$data['Done'] = true;
		$asset->display->title = 'Complete';
	} else {
		$asset->display->title = 'Profile';
	}
	
	// Check for custom layout
	$checkLayout = ss_optionExists('Add To Profile Layout');
	if ($checkLayout !== false) $asset->display->layout = $checkLayout;

	ss_customStyleSheet($this->styleSheet);
	$this->useTemplate('AddToProfile',$data);	
	
?>