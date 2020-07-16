<?php

	ss_paramKey($asset->cereal, $this->fieldPrefix.'ALLOWED_GROUPS', array());
	$isMember = 0;
	$groups = array_keys($_SESSION['User']['user_groups']);
	foreach ($asset->cereal[$this->fieldPrefix.'ALLOWED_GROUPS'] as $group) {				
		if (in_array($group, $groups)) {
			$isMember = 1;
			break;
		}
	}

	$assetPath = ss_withoutPreceedingSlash($asset->getPath());

	if( !array_key_exists('User', $_SESSION )
	 || !array_key_exists('us_email', $_SESSION['User'] )
	 || ( strlen( $_SESSION['User']['us_email'] ) == 0 ) )
	{
		// User isn't a member...
		$login = new Request('Security.Login',array(
			'BackURL'	=>	$assetPath,
			'NoHusk'	=>	1,
		));
		$type = "";
		$data = array(
			'EditableContent'	=>	ss_parseText($asset->cereal[$this->fieldPrefix.'LOGIN_CONTENT']),
			'BackURL'			=>	$assetPath,
			'Type'				=>	$type,
			'LoginForm'			=>	$login->display,
		);
		$this->useTemplate('LoginService',$data);
		return;
	}

	if( array_key_exists('User', $_SESSION )
	 && array_key_exists('us_password', $_SESSION['User'] )
	 && ( strlen( $_SESSION['User']['us_password'] ) > 0 ) )
	{

		ss_paramKey($asset->cereal,$this->fieldPrefix.'LAYOUT', '');
		if (strlen($asset->cereal[$this->fieldPrefix.'LAYOUT'])) {
			$asset->display->layout = $asset->cereal[$this->fieldPrefix.'LAYOUT'];
		}
		
		$editableContent = ss_parseText($asset->cereal[$this->fieldPrefix.'WELCOME_CONTENT']);
		$editableContent = stri_replace('[first_name]',ss_HTMLEditFormat(ss_getFirstName()),$editableContent);
		$editableContent = stri_replace('[last_name]',ss_HTMLEditFormat(ss_getLastName()),$editableContent);
		$usID = ss_getUserID();
		$tempE = null;

		if( array_key_exists('User', $_SESSION )
		  and array_key_exists('us_id', $_SESSION['User'] ) )
		{
			if( !array_key_exists('us_token', $_SESSION['User'] )
			  OR $_SESSION['User']['us_token'] == NULL
			  OR strlen( $_SESSION['User']['us_token'] ) == 0 )
			{

				srand( $usID );
				ss_setUserToken( md5( rand() ) );
			}
		}

		if( array_key_exists( 'remove', $_GET ) )
		{
			$remove = safe( $_GET['remove'] );

			query( "delete from shopsystem_stock_notifications
				where stn_us_id = $usID
				 and stn_stock_code = '$remove'" );
		}

			//and tr_payment_details_szln IS NOT NULL
		$issueCount = getField( "select count(*) as count from client_issue where ci_us_id = $usID and (ci_closed IS NULL or (ci_closed IS NOT NULL and ci_closed > NOW() - INTERVAL 180 DAY))" );
		// $issues = query( "select * from client_issue where ci_us_id = $usID and (ci_invisible IS NULL or ci_invisible = 0) order by (ci_closed IS NOT NULL), ci_id desc limit 10" );
		$issues = query( "select ci_id, ci_transaction_number, ci_created, ci_closed, max(cir_created) from client_issue left join client_issue_response on cir_ci_id = ci_id where ci_us_id = $usID and (ci_invisible IS NULL or ci_invisible = 0) and ((ci_closed IS NULL) or (ci_closed > NOW() - INTERVAL 180 DAY )) and (cir_deleted IS NULL or cir_deleted = false)  and (cir_invisible IS NULL or cir_invisible = false) group by ci_id, ci_transaction_number, ci_created, ci_closed order by 1 desc limit 10" );

		$newCount = getField( "select count(*) as count from client_issue join client_issue_response on cir_ci_id = ci_id join users on ci_us_id = us_id where ci_us_id = $usID and cir_created > us_members_viewed" );
//		query( "update users set us_members_viewed = now() where us_id = $usID" );
		unset( $_SESSION['NewMessage'] );

		$abandonedCount = getField( "select count(*) as count from shopsystem_orders, transactions left join payment_gateways on pg_id = tr_bank
			where or_us_id = $usID 
			and or_tr_id = tr_id
			and tr_completed = 0
			and ( ( or_recorded > NOW() - INTERVAL 1 DAY and pg_skim != 0 )
			OR ( or_recorded > NOW() - INTERVAL 3 DAY and pg_skim = 0 ) )
			and or_cancelled IS NULL
			and tr_bank > 0
			order by or_id desc
			" );

		$abandonedOrders = query( "select * from shopsystem_orders, transactions  left join payment_gateways on pg_id = tr_bank
			where or_us_id = $usID 
			and or_tr_id = tr_id
			and tr_completed = 0
			and ( ( or_recorded > NOW() - INTERVAL 1 DAY and pg_skim != 0 )
			OR ( or_recorded > NOW() - INTERVAL 3 DAY and pg_skim = 0 ) )
			and or_cancelled IS NULL
			and tr_bank > 0
			order by or_id desc
			limit 2
			" );

		$visibleOrders = query( "select * from shopsystem_orders, transactions 
			where or_us_id = $usID 
			and or_tr_id = tr_id
			and or_deleted  = 0
			and tr_completed = 1
			and or_recorded > NOW() - INTERVAL 18 WEEK
			and or_cancelled IS NULL
			and ( or_shipped IS NULL OR or_shipped > NOW() - INTERVAL 18 WEEK )
			order by or_id desc
			" );

		ss_login($usID,$tempE);
		$userDetail = ss_getUser();

		$data = array(
			'EditableContent'	=>	$editableContent,
			'NewCount'	=>  $newCount,
			'IssueCount'	=>  $issueCount,
			'Issues'	=>  $issues,
			'AbandonedCount'	=>  $abandonedCount,
			'AbandonedOrders'	=>  $abandonedOrders,
			'VisibleOrders'			=>	$visibleOrders,
			'AssetPath'			=>	$assetPath,
		);

		$this->useTemplate('Messages',$data);
		return;
	}
	else
	{
		$nothing = query("select NULL");
		$data = array(
			'AssetPath'			=>	$assetPath,
			'VisibleOrders'			=>	$nothing,
		);

		$this->useTemplate('Messages',$data);
		return;

	}


?>
