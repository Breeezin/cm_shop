<?php

	$email = '';
	if (ss_loggedInUsersID() !== false) {
		$user = ss_getUser();
		$email = $user['us_email'];
	}

	$data = array(
		'Product'	=>	$this->ATTRIBUTES['Product'],
		'ProductName'	=>	$product['pr_name'],
		'Error'		=>	$error,
		'AssetPath'	=>	ss_withoutPreceedingSlash($asset->getPath()),
		'Email'	=>	$email,
		'LastSearch'	=>	array_key_exists('LastSearch',$_SESSION['Shop'])?$_SESSION['Shop']['LastSearch']:null,
	);
	
	if (array_key_exists('DoAction',$this->ATTRIBUTES) and !strlen($error)) {
		$data['Done'] = true;
		$asset->display->title = 'Complete';
	} else {
		$asset->display->title = 'Rate this Product';
	}	
	
	if (array_key_exists('DoAction',$this->ATTRIBUTES) and !strlen($error)) {
		$data['Done'] = true;
	}	
	
	$this->useTemplate('AddCustomerRating',$data);
	
	
?>