<?php

	// Only run if this is not a reshipment
	if (0) {
//	if ($Q_Order['or_reshipment'] == null and $Q_Order['or_lottery'] == null) { 

/*
		$amount = 0;
		foreach($basket['OrderProducts'] as $aProduct) {
			$name = escape("{$aProduct['Product']['pr_name']} ({$aProduct['Product']['Options']})");
			$amount += $aProduct['Qty'] * $aProduct['Product']['Price'];
		}
*/
		
		// figure out the amount for the order
		$amount = $Q_Transaction['tr_total'];
		$orderDetails = unserialize($Q_Order['or_basket']);
		if (array_key_exists('Basket',$orderDetails) and array_key_exists('Freight',$orderDetails['Basket']) and array_key_exists('Amount',$orderDetails['Basket']['Freight'])) {
			
			$amount -= $orderDetails['Basket']['Freight']['Amount'];
		}
		
		$order = $this->ATTRIBUTES['or_id'];
	
		// calculate expiry
		$counter = 6;
		$date = time();
		while ($counter > 0) {
			$yearMonths = array(31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31);	
			$leapYearMonths = array(31, 29, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31);	
			
			$month = date('m',$date)-1;
			if (isLeapYear($date)) {
				$days = $leapYearMonths[$month];
			} else {
				$days = $yearMonths[$month];
			}
			
			$date += $days*60*60*24;
			$counter--;
		}
		$expires = ss_TimeStampToSQL($date);

		$shop = getRow("
			SELECT as_serialized From assets
			WHERE as_id = {$Q_Order['or_as_id']}
		");
		$shopCereal = unserialize($shop['as_serialized']);
		
		startTransaction();
	
		// Check for any existing points for this order 
		$Q_CheckExisting = query("
			SELECT * FROM shopsystem_user_points
			WHERE up_or_id = ".safe($order)."
			AND up_expires > CURDATE()
		");
	
		if ($Q_CheckExisting->numRows() == 0) {

			$currentUser = $Q_Order['or_us_id'];
			$user = getRow("
				SELECT us_id, us_user_name, us_first_name, us_last_name, us_email, us_referral_user_name FROM users
				WHERE us_id = $currentUser
			");
			// figure out points for everyone
			
			$currentLevel = 0;
			$done = false;
			$earned = array();
			
			// loop until one of these:
			// 		1) there are no referrals
			//		2) we reach 5 deep, 
			//		3) or we hit some 'problem' referrals.. e.g. A refers B who refers A.
			while (!$done) {

				// stop processing if this user has already earned points in this loop
				if (array_key_exists($currentUser,$earned)) break;

				// get the percentage for this level
				ss_paramKey($shopCereal,'AST_SHOPSYSTEM_LEVEL'.$currentLevel.'_PERCENTAGE',0);
				$percentage = $shopCereal['AST_SHOPSYSTEM_LEVEL'.$currentLevel.'_PERCENTAGE'];
					
				// Add points for the loyalty program customer
				$points = floor($amount*$percentage/100);
				if ($currentLevel > 0) $points = $percentage;
				
				// check if he has too many points already
				$CheckPoints = getRow("
					SELECT SUM(up_points) AS TotalPoints FROM shopsystem_user_points
					WHERE up_us_id = $currentUser AND up_used IS NULL
					AND up_expires > CURDATE()
				");
				
				if ($CheckPoints['TotalPoints'] > 0 and $CheckPoints['TotalPoints'] < 4000 and $points > 0) {
						
					if ($currentLevel == 0) {
						// if this is the person who actually ordered, then see if they are due for their 
						// free 20 points, for the "60 day gift email"
						$Q_20PointCheck = query("
							SELECT or_id FROM shopsystem_orders
							WHERE or_us_id = $currentUser
								AND or_follow_up_status LIKE 'Sent Email'
						");	
						if ($Q_20PointCheck->numRows() > 0) {
							// yep, they've been sent an email... so give them extra 300 points then.
							$points += 300;
							// now flag them as having used the points
							$freePointsOrder = $Q_20PointCheck->fetchRow();
							$Q_Use20Points = query("
								UPDATE shopsystem_orders
								SET or_follow_up_status = 'Got Points'
								WHERE or_id = {$freePointsOrder['or_id']}
							");
						}
					}
					
					$earned[$currentUser] = true;
					
					// calculate the points
					$newID = newPrimaryKey('shopsystem_user_points','up_id');
					
					// insert the points
					$Q_AddPoints = query("
						INSERT INTO shopsystem_user_points
							(up_id, up_or_id, up_points, up_expires, up_us_id)
						VALUES
							($newID, $order, $points, $expires, $currentUser)
					");
					
					$data = array(
						'first_name'	=>	$user['us_first_name'],
						'last_name'	=>	$user['us_last_name'],
						'Points'	=>	$points,
						'Total'		=>	$points+$CheckPoints['TotalPoints'],
						'CurrentLevel'	=>	$currentLevel,
					);

					if ($currentLevel == 0) {
						$subject = 'Your Frequent Buyer Program points have been awarded!';
					} else {
						$subject = 'One of your referrals has made a purchase!';
					}
					$result = new Request("Email.Send",array(
						'from'	=>	$GLOBALS['cfg']['EmailAddress'],
						'to'	=>	$user['us_email'],
						'subject'	=>	$subject,
						'templateFolder'	=>	$Q_Order['or_site_folder'],
						'html'	=>	$this->processTemplate('AcmePointsAwarded',$data),
					));

//					$subject = "Email sent to ".$user['us_email']." about more than 4000 user points";
//
//					$result = new Request("Email.Send",array(
//						'from'	=>	$GLOBALS['cfg']['EmailAddress'],
//						'to'	=>	'pepa@bjorckbros.intranets.com',
//						'subject'	=>	$subject,
//						'templateFolder'	=>	$Q_Order['or_site_folder'],
//						'html'	=>	$this->processTemplate('AcmePointsAwarded',$data),
//					));

					
				}				

				if ($user['us_referral_user_name'] === null or strlen($user['us_referral_user_name']) == 0) {
					$done = true;
				} else {
					$user = getRow("
						SELECT us_id, us_user_name, us_last_name, us_email, us_referral_user_name FROM users
						WHERE us_user_name LIKE '".escapeLike($user['us_referral_user_name'])."'
					");
					if ($user === null) {
						// can't find the referral
						$done = true;						
					} else {
						$currentUser = $user['us_id'];	
					}
				}
				if ($currentLevel > 5) $done = true;
				
				$currentLevel++;	
			}
			
		}
		
		commit();
	}
?>
