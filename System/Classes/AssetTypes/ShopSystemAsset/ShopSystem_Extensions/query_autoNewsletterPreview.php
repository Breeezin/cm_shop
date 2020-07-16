<?php
	
	//die('test');

	// we have all the time in the world ;)
	set_time_limit(0);

	$this->display->layout = 'none';

	// build the $data structure
	require('inc_autoNewsletter.php');

	$data['GotPrize'] = true;
	$data['Winner'] = 'Matt';
	$data['WinnerState'] = 'Christchurch';
	$data['WinnerCountry'] = 'New Zealand';
	$thisWeekBox = getRow("
		SELECT pr_name, pr_image1_normal FROM shopsystem_products
		WHERE pr_image1_normal IS NOT NULL
		LIMIT 10,1
	");	
	$data['ThisWeekBox'] = $thisWeekBox['pr_name'];
	$data['CigarImage'] = '';
	if ($thisWeekBox['pr_image1_normal'] !== null) {
		$data['CigarImage'] = '<img src="index.php?act=ImageManager.get&Image=Custom/ContentStore/Assets/5/14/ProductImages/'.$thisWeekBox['pr_image1_normal'].'&Size=160x160&Rotate=270">';			
	}
		
	$data['NextWeekBox'] = 'Carabineros No. 2';
			
	
	$this->useTemplate('AutoNewsletter',$data);
	
?>