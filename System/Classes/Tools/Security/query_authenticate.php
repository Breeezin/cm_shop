<?php
    //die('still getting here');
    global $sudo;
	if ($sudo === NULL) $sudo = 0;
	
	$this->param('LoginOnFail','Yes');
	$this->param('Permission');

// 	ss_log_message( "Authenicating..." );
 
	// First things first.. check if they're logged in at all
	if (ss_loggedInUsersID() === false) {
		// Try and log the user in with a cookie
		if (array_key_exists('keepMeLoggedInCookie',$_COOKIE) and !array_key_exists('DontTryCookieLogin',$_REQUEST)) {
			ss_log_message( "keepMeLoggedIn..." );
			if ($_COOKIE['keepMeLoggedInCookie'] != 'logout') {
				$cookieSettings = unserialize($_COOKIE['keepMeLoggedInCookie']);
				if (array_key_exists('UserID',$cookieSettings) and array_key_exists('Auth',$cookieSettings)) {
					// Grab the user based on their user id
					$Q_User = query("
						SELECT * FROM users
						WHERE us_id = ".safe($cookieSettings['UserID'])."
					");
					if ($Q_User->numRows()) {
						$user = $Q_User->fetchRow();
						// If the auth code is good then pre fill some values to log them in
						if (md5($user['us_id'].$user['us_password'].'salt') == $cookieSettings['Auth']) {
							// login as the user
							$errors = null;
							ss_login($user['us_id'],$errors);
						}
					}
				}
			}
			// If we're not logged in, then drop the cookie cos it's no good
			if (ss_loggedInUsersID() === false) {
				setcookie('keepMeLoggedInCookie','logout',10,str_replace('index.php','',$_SERVER['SCRIPT_NAME']),str_replace('www','',$_SERVER['HTTP_HOST']));
			}
		}	
	}
	
	// Possibly log the user in based on values from the URL
	if (array_key_exists('HashMeIn',$_REQUEST)) {
		ss_log_message( "HashMeIn" );
		if (ListLen($_REQUEST['HashMeIn'],'_') == 2) {
			$userID = ListFirst($_REQUEST['HashMeIn'],'_');
			$hash = ListLast($_REQUEST['HashMeIn'],'_');
			ss_log_message( "
				SELECT * FROM users
				WHERE us_id = ".safe($userID) );
			$Q_User = query("
				SELECT * FROM users
				WHERE us_id = ".safe($userID));
			if ($Q_User->numRows()) {
				$user = $Q_User->fetchRow();
				// If the hash matches the one for the user, log them in
				ss_log_message( "$hash == ".ss_generateUserHash($user) );
				if ($hash == ss_generateUserHash($user)) {
					$errors = null;
					ss_login($user['us_id'],$errors);
				}
				else
					ss_log_message( "BOOM" );
			}
		}
	}
	
	// By default they are not authenticated
	$authenticated = false;

//	ss_DumpVar( $this );
//    echo "sudo = ".$sudo."\n";

	if ($sudo or $_SESSION['User']['us_id'] == 0) {
		$authenticated = true;
	} else {
//		ss_log_message( "looking for permission ".$this->ATTRIBUTES['Permission'] );
		switch ($this->ATTRIBUTES['Permission']) {
			case 'IsSuperUser' :
				if ($sudo or $_SESSION['User']['us_id'] == 0) $authenticated = true;
				break;
				
			case 'IsDeployer' :		
				$this->param('CurrentSite', '');
				
				if ((is_array($this->ATTRIBUTES['CurrentSite']) and count($this->ATTRIBUTES['CurrentSite'])) or strlen($this->ATTRIBUTES['CurrentSite'])) {
					global $cfg;
					
					if (is_array($this->ATTRIBUTES['CurrentSite'])) {
						
						foreach ($this->ATTRIBUTES['CurrentSite'] as $aSite) {
							echo $cfg['currentSite']." vs ".$aSite."<BR>";
							if ($cfg['currentSite'] == $aSite and ($_SESSION['User']['us_id'] == 0 or $_SESSION['User']['us_id'] == 1)) {
									$authenticated = true;															
									break;
							}
						}
					} else {
						if ($cfg['currentSite'] == $this->ATTRIBUTES['CurrentSite']) {						
							if ($_SESSION['User']['us_id'] == 0 or $_SESSION['User']['us_id'] == 1) $authenticated = true;							
						}
					}
				} else if ($_SESSION['User']['us_id'] == 0 or $_SESSION['User']['us_id'] == 1) $authenticated = true;
				/*
				if ($authenticated) {					
					ss_DumpVarDie($this->ATTRIBUTES, $authenticated."ok $authenticated".$cfg['currentSite'], true);
				} else {
					ss_DumpVarDie($_SESSION, $authenticated."ok $authenticated".$cfg['currentSite'], true);
				}*/
				break;
			case 'IsInAllTheseGroups' :
//                ss_DumpVarDie($_SESSION['User']['user_groups']);
                $this->param('Groups');
				$authenticated = true;
				foreach($this->ATTRIBUTES['Groups'] as $group) {
					if (!array_key_exists($group,$_SESSION['User']['user_groups'])) {
						$authenticated = false;
						break;				
					}
				}
				break;

			case 'IsInAnyOfTheseGroups' :
				$this->param('Groups');
				foreach($this->ATTRIBUTES['Groups'] as $group) {
					if (array_key_exists($group,$_SESSION['User']['user_groups'])) {
						$authenticated = true;
						break;
					}
				}
				break;

			case 'IsLoggedIn' :															
				if (ss_loggedInUsersID() !== false) {
					$authenticated = true;	
				}
				break;
					
			case 'CanAccessAsset' :
				$this->param('as_id');
//				ss_log_message( " asset id ".$this->ATTRIBUTES['as_id'] );
				ss_log_message_r(  'Log:'.__FILE__.':'.__LINE__, $_SESSION['User'] );
				$admin = getRow("
					SELECT MAX(aug_can_use) AS HasAccess FROM asset_user_groups
					WHERE aug_as_id = ".safe($this->ATTRIBUTES['as_id'])."
						AND aug_ug_id IN (".ArrayKeysToList($_SESSION['User']['user_groups']).")
				");
				if ($admin['HasAccess']) $authenticated = true;
				break;
				
			case 'CanAdministerAsset' :
				$this->param('as_id');
				$admin = getRow("
					SELECT MAX(aug_can_administer) AS HasAccess FROM asset_user_groups
					WHERE aug_as_id = ".safe($this->ATTRIBUTES['as_id'])."
						AND aug_ug_id IN (".ArrayKeysToList($_SESSION['User']['user_groups']).")
				");
				if ($admin['HasAccess']) $authenticated = true; 
				break;

			case 'CanHighLevelAdministerAsset' :
				$this->param('as_id');
				$admin = getRow("
					SELECT MAX(aug_can_administer) AS HasAccess FROM asset_user_groups
					WHERE aug_as_id = ".safe($this->ATTRIBUTES['as_id'])."
						AND aug_ug_id IN (".ArrayKeysToList($_SESSION['User']['user_groups']).")
				");
				$asset = getRow("
					SELECT as_system, as_owner_au_id FROM assets
					WHERE as_id = ".safe($this->ATTRIBUTES['as_id'])."
				");
				$isSuperUser = ($sudo or $_SESSION['User']['us_id'] == 0);
				if (($admin['HasAccess']) and (($asset['as_system'] != 1 and $asset['as_owner_au_id'] != 0) or $isSuperUser)) {
					$authenticated = true;	
				}
				break;
				
			case 'CanReview' :
				if (ss_optionExists('Review Process')) {
					$admin = getRow("
						SELECT COUNT(*) AS TheCount FROM user_groups
						WHERE ug_id IN (".ArrayKeysToList($_SESSION['User']['user_groups']).")
							AND ug_reviewer = 1
					");
					if ($admin['TheCount']) $authenticated = true; 				
				}
				break;

			case 'CanReviewAsset' :
				if (ss_optionExists('Review Process')) {
					$admin = getRow("
						SELECT COUNT(*) AS TheCount FROM assets
						WHERE AssetReviewer = ".ss_getUserID()."
							AND AssetReview = 1
							AND as_id = ".safe($this->ATTRIBUTES['as_id'])."
					");
					if ($admin['TheCount']) $authenticated = true; 				
				}
				break;				
				
			case 'CanAdministerAssetBranch' :
				$this->param('as_id');
				$branchAssets = ss_GetBranchAssetsArray($this->ATTRIBUTES['as_id'],true);
				$admin = getRow("
					SELECT MIN(aug_can_administer) AS HasAccess FROM asset_user_groups
					WHERE aug_as_id IN (".ArrayToList($branchAssets)."
						AND aug_ug_id IN (".ArrayKeysToList($_SESSION['User']['user_groups']).")
				");
				if ($admin['HasAccess']) $authenticated = true; 				
				break;

			case 'RestrictedAdmin' :
				if( $_SESSION['User']['us_email'] == 'pepafuster@yahoo.es' )
					$authenticated = true;
				if( $_SESSION['User']['us_email'] == 'swiss@acmerockets.com' )
					$authenticated = true;

			case 'CanAdministerAtLeastOneAsset' :
				//ss_DumpVarHide($this, "at leaset one");				
				$admin = getRow("
					SELECT MAX(aug_can_administer) AS HasAccess FROM asset_user_groups
					WHERE aug_ug_id IN (".ArrayKeysToList($_SESSION['User']['user_groups']).")
				");

				if ($admin['HasAccess']) $authenticated = true; 
				break;	

			default :
				die('Invalid permission type sent to authenticate: '.$this->ATTRIBUTES['Permission']);
		}
	}	

	if ($authenticated) {
//		ss_log_message( "OK" );
		return true;
	} else {
//		ss_log_message( "NOT" );
		if ($this->ATTRIBUTES['LoginOnFail'] != 'Yes') {
			return false;
		} else {
            if (ss_OptionExists('Keep Logged In') and (ss_loggedInUsersID() !== false)){ // and $this->ATTRIBUTES['act'] != 'Administration'){
    			$backURL = getBackURL();
                $assetID = isset($this->ATTRIBUTES['as_id']) ? $this->ATTRIBUTES['as_id'] : 1;
    			location("{$_SERVER['SCRIPT_NAME']}?act=Security.Redirect&BackURL={$backURL}&as_id={$assetID}");
            }else{
//				ss_DumpVar(debug_backtrace());
                // Log the user out and send them off to the login screen
    			$result = new Request("Security.Logout",array('NoHusk'	=>	true));
    			//$_SESSION['UserID'] = -1;
    			$backURL = getBackURL();
    			//ss_DumpVarDie($this);
    			location("{$_SERVER['SCRIPT_NAME']}?act=Security.Login&BackURL={$backURL}");
            }
		}
	}
?>
