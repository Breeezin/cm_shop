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
	
	ss_customStyleSheet($this->styleSheet);
	$this->useTemplate('RemoveFromProfile',$data);	
	
?>