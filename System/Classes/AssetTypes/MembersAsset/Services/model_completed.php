<?php 
	$this->param('tr_id');
	$this->param('Token');
	
	//now, the new user complete payment process 
	// so need to update expiry date for the init access to the memeber's area	
	$Q_Transaction = getRow("SELECT * FROM transactions WHERE tr_id = {$this->ATTRIBUTES['tr_id']} AND tr_token LIKE '{$this->ATTRIBUTES['Token']}' AND tr_completed = 1");

	//ss_DumpVarDie($Q_Transaction,'', true);
	if ($Q_Transaction['tr_id'] == $this->ATTRIBUTES['tr_id']) {			
		
		// get order detail
		$order = getRow("SELECT * FROM members_orders 
				WHERE mo_tr_id = {$this->ATTRIBUTES['tr_id']}
					AND mo_token LIKE '{$this->ATTRIBUTES['Token']}'
					AND mo_as_id = {$assetID}
					AND  mo_approved = 0
					
		");
		
		if (strlen($order['mo_sub_id'])) {
			//ss_DumpVarDie($order,'', true);
			// check user's activated date and add initial allowed date				
			$Q_User = getRow("SELECT * FROM users WHERE us_id = {$order['mo_us_id']}");
			
			ss_paramKey($asset->cereal,"AST_MEMBERS_INIT_ALLOWED_DAY", 7);			
			$init = $asset->cereal['AST_MEMBERS_INIT_ALLOWED_DAY'];
			if (!strlen($Q_User['us_activated'])) {			
				$Q_UpdateUser = query("UPDATE users SET us_activated =  Now() + INTERVAL $init DAY WHERE us_id = {$order['mo_us_id']}");
			} else {
				$Q_UpdateUser = query("UPDATE users SET us_activated = us_activated + INTERVAL $init DAY WHERE us_id = {$order['mo_us_id']}");
			}
			
		
		
		

			//us_activated 
			$init = $asset->cereal['AST_MEMBERS_INIT_ALLOWED_DAY'];
			$Q_UpdateUser = query("UPDATE users SET us_activated = Now() + INTERVAL $init DAY WHERE us_id = {$order['mo_us_id']}");
			
			$Q_UpdateOrder = query("
					UPDATE members_orders 
					SET mo_approved = 1 
					WHERE mo_tr_id = {$this->ATTRIBUTES['tr_id']}
						AND mo_token LIKE '{$this->ATTRIBUTES['Token']}'
						AND mo_as_id = {$assetID}
						AND mo_approved = 0
			");					
		}
	}
	//global $cfg;
	//ss_DumpVarDie($assetPath."/Service/ThankYou", $cfg['currentServer'], true); 
	locationRelative($assetPath."/Service/ThankYou", true);
?>
