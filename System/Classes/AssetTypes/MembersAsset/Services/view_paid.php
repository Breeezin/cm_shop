<?php
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

		// and or_card_denied IS NULL
		
			//and tr_payment_details_szln IS NOT NULL
		$issueCount = getField( "select count(*) as count from client_issue where ci_us_id = $usID and (ci_closed IS NULL or (ci_closed IS NOT NULL and ci_closed > NOW() - INTERVAL 180 DAY))" );
		// $issues = query( "select * from client_issue where ci_us_id = $usID and (ci_invisible IS NULL or ci_invisible = 0) order by (ci_closed IS NOT NULL), ci_id desc limit 10" );
		$issues = query( "select ci_id, ci_transaction_number, ci_created, ci_closed, max(cir_created) from client_issue left join client_issue_response on cir_ci_id = ci_id where ci_us_id = $usID and (ci_invisible IS NULL or ci_invisible = 0) and ((ci_closed IS NULL) or (ci_closed > NOW() - INTERVAL 180 DAY )) and (cir_deleted IS NULL or cir_deleted = false)  and (cir_invisible IS NULL or cir_invisible = false) group by ci_id, ci_transaction_number, ci_created, ci_closed order by 5 desc limit 10" );

		$newCount = getField( "select count(*) as count from client_issue join client_issue_response on cir_ci_id = ci_id join users on ci_us_id = us_id where ci_us_id = $usID and cir_created > us_members_viewed" );
//		query( "update users set us_members_viewed = now() where us_id = $usID" );

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

		$wishList = query( "select * from shopsystem_stock_notifications, shopsystem_products, shopsystem_product_extended_options
			where stn_us_id = $usID
			 and stn_stock_code = pro_stock_code
			 and pro_pr_id = pr_id
			 and pr_deleted IS NULL
			 and pr_offline IS NULL
			 and pr_is_service = 'false'
			 " );

		ss_login($usID,$tempE);
		$userDetail = ss_getUser();

		$data = array(
			'EditableContent'	=>	$editableContent,
			'NewCount'	=>  $newCount,
			'IssueCount'	=>  $issueCount,
			'Issues'	=>  $issues,
			'VisibleOrders'			=>	$visibleOrders,
			'WishList'			=>	$wishList,
			'AssetPath'			=>	$assetPath,
		);

		if( count($_POST) > 0 )
		{
			if( array_key_exists( 'SaveBankDetails', $_POST ) )
			{
				$bt_address = 'unspecified';
//				if( array_key_exists( 'bt_address', $_POST ) && strlen( $_POST['bt_address'] ) )
//					$bt_address = escape( $_POST['bt_address'] );

				$bt_account = '';
				if( array_key_exists( 'bt_account', $_POST ) && strlen( $_POST['bt_account'] ) )
					$bt_account = escape( $_POST['bt_account'] );

				$bt_received = 0;
				$sus = '';
				if( array_key_exists( 'bt_received', $_POST ) && strlen( $_POST['bt_received'] ) )
				{
					$sus = trim( $_POST['bt_received'] );
					if( ( $pos = strpos( $sus, '$' ) ) !== FALSE )
						$sus = substr( $sus, $pos + 1 );

					$bt_received = (float)( $sus );
				}

				$tr_id = 0;
				if( array_key_exists( 'tr_id', $_POST ) && strlen( $_POST['tr_id'] ) )
					$tr_id = (int)( $_POST['tr_id'] );

				if( strlen( $bt_account ) && $bt_received > 0  && $tr_id > 0 )
				{
					ss_log_message( "user $usID indicating bank transfer for tr_id $tr_id" );
					$us = getRow( "select or_us_id from shopsystem_orders where or_tr_id = ".((int) $tr_id) );
					if( $us['or_us_id'] == $usID )
					{
						ss_log_message( "Bank address '$bt_address', account '$bt_account', amount $bt_received" );
						$ex = getField( "select bt_id from bank_transfer_information where bt_tr_id = $tr_id" );
						if( $ex > 0 )		// dumbasses
							die;
						query( "insert into bank_transfer_information (bt_tr_id, bt_address, bt_account, bt_received ) values ($tr_id, '$bt_address', '$bt_account', $bt_received )" );
						// complete this order and place on standby
						query( "UPDATE shopsystem_orders set or_standby = NOW() where or_tr_id = ".((int) $tr_id) );
						query( "UPDATE transactions SET tr_completed = 1, tr_timestamp = NOW() where tr_id = ".((int) $tr_id) );
						// reserve stock
						$Q_Order = getRow("SELECT * FROM shopsystem_orders WHERE or_tr_id = $tr_id");
						$basket = unserialize($Q_Order['or_details']);
						foreach($basket['OrderProducts'] as $aProduct)
						{
							$name = escape("{$aProduct['Product']['pr_name']} ({$aProduct['Product']['Options']})");
							$price = $aProduct['Qty'] * $aProduct['Product']['Price'];
//							$price = escape($this->formatPrice('display', $price));
							ss_log_message( "inserting into shopsystem_order_products $name, $price" );
							$Q_Insert = query(" INSERT INTO shopsystem_order_products 
													(orpr_or_id, orpr_pr_id, orpr_pr_name, orpr_price, orpr_qty, orpr_timestamp, orpr_site_folder) 
												VALUES
													({$Q_Order['or_id']}, {$aProduct['Product']['pr_id']}, '$name', '$price', {$aProduct['Qty']}, Now(), '{$GLOBALS['cfg']['folder_name']}')		
								");

							$ProductOption = getRow(" SELECT * FROM shopsystem_products, shopsystem_product_extended_options
									WHERE pro_pr_id = pr_id and pro_pr_id = '{$aProduct['Product']['pr_id']}'
								");

							if ($ProductOption['pro_stock_available'] !== null)
							{
								// If the product option is using the stock level management..
								ss_log_message( "reducing stock for $name from {$ProductOption['pro_stock_available']} by {$aProduct['Qty']}" );
								$Q_UpdateProductOption = query(" UPDATE shopsystem_product_extended_options
																	SET pro_stock_available = ".($ProductOption['pro_stock_available']-$aProduct['Qty'])."
																WHERE pro_id = {$ProductOption['pro_id']}
									");
								ss_audit( 'update', 'Products', $aProduct['Product']['pr_id'], "Indicated bank transfer order $tr_id, available stock less ".$aProduct['Qty'] );
							}
						}
						// TODO we need to gather up information about this order and fire off an email here.

					}
				}
				else
					ss_log_message( "Save bank details failed, Transaction: $tr_id Account:$bt_account received:$bt_received($sus)" );
			}
			else
				foreach( $_POST as $key=>$val )
				{
					$ar = explode( '_', $key );
					ss_log_message( "Received '$key'" );
					ss_log_message_r(  'Log:'.__FILE__.':'.__LINE__, $ar );
					if( (count($ar) == 4 ) && !strcmp($ar[0], "Received") )
					{
						$order = $ar[1];
						$stock_code = base64_decode($ar[2]);
						$boxn = $ar[3];

						$us = getRow( "select or_us_id from shopsystem_orders where or_id = ".((int) $order) );
						if( $us['or_us_id'] == $usID )
						{
							query( "insert into shopsystem_order_notes (orn_text, orn_timestamp, orn_or_id) values ('Client marking "
								.escape($stock_code).':'.((int) $boxn)." as received', NOW(), ".((int) $order)." )" );
							ss_audit( 'update', 'Orders', (int)$order, 'marking received '.escape($stock_code).':'.((int) $boxn) );
							query( "update shopsystem_order_sheets_items set orsi_received = now() where orsi_or_id = ".((int)$order)." and orsi_stock_code = '".escape( $stock_code )."' and orsi_box_number = ".((int) $boxn) );
							if( affectedRows() == 0 )
								echo "Database error, unable to mark as shipped<br/><br/><br/><br/>";
						}
						else
						{
							echo "Clever tit, go away";
							die;
						}
					}
				}
		}

		$this->useTemplate('Paid',$data);
		return;
	}
	else
	{
		$editableContent = "<h1>You have no password.<br /><a href='/Members/Service/Edit'>Click here to edit my profile to add a password</a></h1>";
		$nothing = query("select NULL");
		$data = array(
			'EditableContent'	=>	$editableContent,
			'AssetPath'			=>	$assetPath,
			'VisibleOrders'			=>	$nothing,
		);

		$this->useTemplate('Paid',$data);
		return;

	}


?>
