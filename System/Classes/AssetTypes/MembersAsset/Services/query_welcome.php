<?php
	// Check if they're a member or not
	
	ss_paramKey($asset->cereal, $this->fieldPrefix.'ALLOWED_GROUPS', array());
	$isMember = 0;
	$groups = array_keys($_SESSION['User']['user_groups']);
	foreach ($asset->cereal[$this->fieldPrefix.'ALLOWED_GROUPS'] as $group) {				
		if (in_array($group, $groups)) {
			$isMember = 1;
			break;
		}
	}
	
	/*if ($isMember) {
		 //us_activated 
		 $type = $asset->cereal['AST_MEMBERS_TYPE'];	
		 if ($type == 'Subscription') {
		 	$userExpirtyDate = ss_getUserExpiryDate();
		 	if ($userExpirtyDate == '') {
		 		$isMember = 0;
		 	} else if ($userExpirtyDate < formatDateTime(now(), "d/m/Y")) {		 		
		 		$isMember = 0;
		 	}		 			 
		 }
	} */
	

?>