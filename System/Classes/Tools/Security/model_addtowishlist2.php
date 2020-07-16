<?php

	// add the product into the wish list

	$this->param('pr_id','');
	$pr_id = safe($this->ATTRIBUTES['pr_id']);
	$this->param('Async', 0);
	if( !$this->ATTRIBUTES['Async'] )
		$this->param('BackURL');

	$UserID = ss_getUserID();


	if( $UserID > 0 )
	{
		if( $pr_id > 0 )
		{
			$user = $_SESSION['User'];
			$option = getRow("
					SELECT * FROM shopsystem_product_extended_options	
					WHERE pro_pr_id = $pr_id
				");

			$stockCode = $option['pro_stock_code'];

			// Delete it just in case its already there
			$Q_Clean = query("
				DELETE FROM shopsystem_stock_notifications
				WHERE stn_stock_code = '$stockCode'
					AND stn_us_id = $UserID
					AND stn_site_folder LIKE '".escape($GLOBALS['cfg']['folder_name'])."'
			");
			
			// Now insert a fresh record
			$Q_Insert = query("
				INSERT INTO shopsystem_stock_notifications
					(stn_stock_code, stn_email, stn_us_id, stn_site_folder)
				VALUES
					('$stockCode','".escape($user['us_email'])."', {$user['us_id']}, '".escape($GLOBALS['cfg']['folder_name'])."')
			");
		}
		else if( $pr_id < 0 )
		{
			$pr_id = -$pr_id;
			$user = $_SESSION['User'];
			$option = getRow("
					SELECT * FROM shopsystem_product_extended_options	
					WHERE pro_pr_id = $pr_id
				");

			$stockCode = $option['pro_stock_code'];

			// Delete it just in case its already there
			$sql = "DELETE FROM shopsystem_stock_notifications
				WHERE stn_stock_code = '$stockCode'
					AND stn_us_id = $UserID";
/*					AND stn_site_folder LIKE '".escape($GLOBALS['cfg']['folder_name'])."'";	*/
			ss_log_message( $sql );
			$Q_Clean = query($sql);
		}

		if( !$this->ATTRIBUTES['Async'] )
			locationRelative($this->ATTRIBUTES['BackURL']);
		else
		{
			echo "notloggedin=0;";
			die;
		}
	}
	else
	{
		if( $this->ATTRIBUTES['Async'] )
		{
			echo "notloggedin=1;";
			die;
		}
		else
		{
			// User isn't a member...
			$login = new Request('Security.Login',array(
				'BackURL'			=>	"index.php?act=Security.AddToWishList&pr_id=$pr_id&BackURL={$this->ATTRIBUTES['BackURL']}",
				'NoHusk'	=>	1,
			));

			$type = "";
			$data = array(
				'EditableContent'	=>	ss_parseText($asset->cereal[$this->fieldPrefix.'LOGIN_CONTENT']),
				'BackURL'			=>	'Members',
				'Type'				=>	$type,
				'LoginForm'			=>	$login->display,
			);
			$this->useTemplate('LoginService',$data);
		}

	}


?>
