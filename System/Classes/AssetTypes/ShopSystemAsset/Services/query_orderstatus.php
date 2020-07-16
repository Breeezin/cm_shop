<?php 
	$this->param('tr_id','');
	$this->param('Message', '');

	if( $transaction = getRow( "select * from transactions join shopsystem_orders on or_tr_id = tr_id where tr_id = ".((int) $this->ATTRIBUTES['tr_id']) ) )
	{
		ss_log_message( "grabbing status for tr_id ".((int) $this->ATTRIBUTES['tr_id']) );

		if( $transaction['or_us_id'] == ss_getUserID() )
		{
			ss_log_message( "User ID ".ss_getUserID()." has returned home with message ".$this->ATTRIBUTES['Message'] );

//			if( $transaction['tr_completed'] > 0 )		// this might still be zero
			if( strstr( $this->ATTRIBUTES['Message'], "successfully" ) )
			{
				$data = array(
					'Message' 	=> ss_parseText($this->asset->cereal['AST_SHOPSYSTEM_THANKYOU_CONTENT']),
				);
				$secureSite = $GLOBALS['cfg']['secure_server'];
				$secureSite = ss_withTrailingSlash($secureSite);
				
				ss_customStyleSheet($this->styleSheet);
				$this->useTemplate('ThankYou', $data);
			}
			else
			{
				echo "<html>I'm sorry something went wrong with the payment gateway, ".$this->ATTRIBUTES['Message']."<br /><a href='/Members'>Click here to continue</a></html>";
				die;
			}
		}
		else
		{
			global $cfg;

			// redirect back to this page on their orginal website
			$siteFolder = $transaction['or_site_folder'];
			if( $siteFolder == $cfg['multiSites'][$cfg['currentServer']] )
				die;

			ss_log_message( "Not this site ".$cfg['currentSiteFolder'].", but $siteFolder" );
			foreach( $cfg['multiSites'] as $site => $folder )
				if( $folder == $siteFolder )
					location( $site.$_SERVER['REQUEST_URI'] );

			// still here?
			die;
		}
	}
	else
		die;
?>
