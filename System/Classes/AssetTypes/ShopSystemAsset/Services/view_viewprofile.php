<?
	$data = array(
		'AssetPath'	    =>	ss_withoutPreceedingSlash($asset->getPath()),
		'Error'		    =>	$error,
		'LastSearch'	=>	array_key_exists('LastSearch',$_SESSION['Shop'])?$_SESSION['Shop']['LastSearch']:null,
        'Q_Users'       =>  $Q_Users,
        'Q_Categories'  =>  $Q_Categories,
        'Q_Products'    =>  $Q_Products,
        'LastCategory'  =>  null,
        'isAdmin'       =>  $isAdmin,
        'us_id'          =>  $usID,
    );

	$asset->display->title = 'Profile';

	// Check for custom layout
	$checkLayout = ss_optionExists('View Profile Layout');
	if ($checkLayout !== false) $asset->display->layout = $checkLayout;

	ss_customStyleSheet($this->styleSheet);
	$this->useTemplate('ViewProfile',$data);
	
?>