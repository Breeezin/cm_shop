<?php
requireOnceClass('AssetTypes');

class ForumAsset extends AssetTypes {
	
	var $fieldPrefix = 'AST_FORUM_';
	var $styleSheet	= 'forum';
    var $defaultService = 'ThreadList';
	
	function getClassName() {
		return substr(basename(__FILE__),1,-4);
	}
	
	function display(&$asset) {
		require('query_display.php');
	}
	
	function embed(&$asset) {        
        $this->defaultService = 'Peek';
		$this->display($asset);
	}

	function properties(&$asset) {
		require('view_properties.php');
	}

	function processSave(&$asset) {
//        ss_DumpVarDie( "yipee" );
        if( ss_optionExists( "use phpBB" ) )
        {
            $new_name = '';

            if( ss_optionExists( "phpBB forum name self" ) )
            {
                // create a phpBB forum with the name $asset['ATTRIBUTES']['as_name']
                $new_name = $asset->ATTRIBUTES['as_name'];
            }
            if( ss_optionExists( "phpBB forum name parent" ) )
            {
                $Q_Parent = getRow( "select as_name from assets where as_id = ".$asset->ATTRIBUTES['as_parent_as_id'] );
                $new_name = $Q_Parent['as_name'];
            }

            if( $new_name != '' )
            {
                define('FORUMS_TABLE', 'phpbb_forums');

                $Q_Exists = getRow( "select count(*) as count from ".FORUMS_TABLE." where forum_name = '".$new_name."'");
                if( $Q_Exists['count'] == 0 )
                {
                    define('AUTH_LIST_ALL', 0);
                    define('AUTH_ALL', 0);
                    define('AUTH_REG', 1);
                    define('AUTH_ACL', 2);
                    define('AUTH_MOD', 3);
                    define('AUTH_ADMIN', 5);
                    define('AUTH_VIEW', 1);
                    define('AUTH_READ', 2);
                    define('AUTH_POST', 3);
                    define('AUTH_REPLY', 4);
                    define('AUTH_EDIT', 5);
                    define('AUTH_DELETE', 6);
                    define('AUTH_ANNOUNCE', 7);
                    define('AUTH_STICKY', 8);
                    define('AUTH_POLLCREATE', 9);
                    define('AUTH_VOTE', 10);
                    define('AUTH_ATTACH', 11);

                    $forum_auth_ary = array
                    (
                        "auth_view" => AUTH_ALL, 
                        "auth_read" => AUTH_ALL, 
                        "auth_post" => AUTH_ALL, 
                        "auth_reply" => AUTH_ALL, 
                        "auth_edit" => AUTH_REG, 
                        "auth_delete" => AUTH_REG, 
                        "auth_sticky" => AUTH_MOD, 
                        "auth_announce" => AUTH_MOD, 
                        "auth_vote" => AUTH_REG, 
                        "auth_pollcreate" => AUTH_REG
                    );

                    $Q_MaxOrder = getRow( "SELECT MAX(forum_order) AS max_order FROM " . FORUMS_TABLE . " WHERE cat_id = 1");    // only first category
                    $next_order = $Q_MaxOrder['max_order'] + 10;
                    
                    $Q_MaxID = getRow( "SELECT MAX(forum_id) AS max_id FROM " . FORUMS_TABLE );
                    $next_id = $Q_MaxID['max_id'] + 1;

                    //
                    // Default permissions of public :: 
                    //
                    $field_sql = "";
                    $value_sql = "";
                    while( list($field, $value) = each($forum_auth_ary) )
                    {
                        $field_sql .= ", $field";
                        $value_sql .= ", $value";

                    }

                    // There is no problem having duplicate forum names so we won't check for it.
                    $Q_UpdateGroup = query( "INSERT INTO " . FORUMS_TABLE . " (forum_id, forum_name, cat_id, forum_desc, forum_order, forum_status, prune_enable "
                        . $field_sql . ") VALUES ('" . $next_id . "', '" . str_replace("\'", "''", $new_name) . "', 1, '', $next_order, 0, 0 "
                        . $value_sql . ")" );

                }
            }
        }
		return null;
	}

    function newAsset(&$asset) {
        return $this->processSave( $asset );
    }
	
	function formatPost($content) {
		$content = ss_HTMLEditFormatWithBreaks($content);
		// [b]bold[/b] [i]italic[/i]
		foreach(array('b','i','ul','li') as $tag) {
			$content = stri_replace("[$tag]","<$tag>",$content);	
			$content = stri_replace("[/$tag]","</$tag>",$content);	
		}
		
		// [quote]blah[/quote]
		preg_match_all("|\[quote](.*)\[/quote\]|iU",$content,$matches,PREG_OFFSET_CAPTURE | PREG_PATTERN_ORDER);
		for ($i=count($matches[0])-1;$i>=0;$i--) {
			$newQuote = "";
			if ($matches[0][$i][1] !== 0) $newQuote = "<br />";
			$newQuote .= "Quote:<br /><div class=\"forumQuote\">".$matches[1][$i][0]."</div>";
			$content = str_replace($matches[0][$i][0],$newQuote,$content);
		}
		
		// [quote=Matt]blah[/quote]
		preg_match_all("|\[quote=([^]]*)\](.*)\[/quote\]|iU",$content,$matches,PREG_OFFSET_CAPTURE | PREG_PATTERN_ORDER);
		for ($i=count($matches[0])-1;$i>=0;$i--) {
			$newQuote = "";
			if ($matches[0][$i][1] !== 0) $newQuote = "<br />";
			$newQuote .= "Quote:<br /><div class=\"forumQuote\">Originally Posted by <strong>".ss_HTMLEditFormat($matches[1][$i][0])."</strong>:<br />".$matches[2][$i][0]."</div>";
			$content = str_replace($matches[0][$i][0],$newQuote,$content);
		}
		
		return $content;		
	}
	
	function defineFields(&$asset) {
		require('query_defineFields.php');
	}	

	function edit(&$asset) {
		require('view_edit.php');
	}
	
	function notify($thr_id,$fm_id) {
		
		// See if they're only subscribing to first posts
		$firstPostOnly = '1=1';
		if ($fm_id != 1) $firstPostOnly = 'fts_first_post_only = 0';

		// Check they are subscribing to the correct thread
		$matchingThread = "(fts_thr_id = ".safe($thr_id)." OR fts_thr_id IS NULL)";
		
		// Find all users subscribing
		$Q_Users = query("
			SELECT us_email, us_first_name, us_last_name FROM users, forum_thread_subscriptions
			WHERE us_id = fts_us_id
				AND $matchingThread
				AND $firstPostOnly
				AND fts_as_id = ".$this->asset->getID()."
		");
		
		// Grab the messages
		$Message = getRow("
			SELECT * FROM forum_threads, forum_messages
			WHERE fm_thr_id = thr_id
				AND thr_as_id = ".$this->asset->getID()."
				AND thr_id = ".safe($thr_id)."
				AND fm_id = ".safe($fm_id)."
		");
		$content = $this->formatPost($Message['fm_content']);

		$replies = getRow("
			SELECT COUNT(*) AS TheCount FROM forum_messages
			WHERE fm_thr_id = {$thr_id}
		");
				
		$data = array(
			'Page'		=>	ceil($replies['TheCount']/$this->messagesPerPage),
			'Subject'	=>	$Message['thr_subject'],
			'Content'	=>	$content,
			'thr_id'		=>	$thr_id,
			'fm_id'		=>	$fm_id,
			'AssetPath'	=>	ss_EscapeAssetPath($this->asset->getPath()),
			'CurrentServer'	=>	$GLOBALS['cfg']['currentServer'],
			'Poster'	=>	$Message['fm_poster_firstname'].' '.$Message['fm_poster_lastname'],
			'SiteName'	=>	$GLOBALS['cfg']['website_name'],
		);
		$configContactDetails = ss_parseText($GLOBALS['cfg']['ContactDetails'], null, true);
		$email = $this->processTemplate('NotificationEmail',$data)."<p>$configContactDetails</p>";
		
		require_once('System/Libraries/htmlMimeMail/htmlMimeMail.php');			
		$mailer = new htmlMimeMail();		
		$mailer->setFrom($GLOBALS['cfg']['EmailAddress']);
		$mailer->setSubject("[Forum] ".$Message['thr_subject']);				
		$mailer->setHTML($email);				
		
		while ($row = $Q_Users->fetchRow()) {
			$mailer->send(array($row['us_email']));				
		}
			
	}

}

?>
