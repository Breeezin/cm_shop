<?php
    // should only be embedded, this is a workaround for not
    // being able to embed in the back end
    // IE: Create a link & embed that to embed this!
    $this->display->layout = 'None';

	// query the database
	$Q_Threads = getRow("
		SELECT *,
            FirstMessage.fm_content AS FirstMessageContent,
			FirstMessage.fm_poster_firstname AS FirstFirstName,
			FirstMessage.fm_poster_lastname AS FirstLastName,
			FirstMessage.fm_poster_email AS FirstEmail,
			LastMessage.fm_poster_firstname AS LastFirstName,
			LastMessage.fm_poster_lastname AS LastLastName,
			LastMessage.fm_poster_email AS LastEmail,
			LastMessage.fm_timestamp AS LastDateTime,
			LastMessage.fm_id AS LastMessageID,
            LastMessage.fm_content AS LastMessageContent,
			LastMessage.fm_poster_firstname AS LastFirstName
		FROM forum_threads, forum_messages AS FirstMessage, forum_messages AS LastMessage
		WHERE thr_id = FirstMessage.fm_thr_id AND FirstMessage.fm_id = 1
			AND thr_id = LastMessage.fm_thr_id AND LastMessage.fm_id = thr_last_thr_id
			AND thr_as_id = ".$asset->getID()."
		ORDER BY LastMessage.fm_timestamp DESC	
		LIMIT 1
	");

	// Find out how many replies in the thread
	$replies = getRow("
		SELECT COUNT(*) AS TheCount FROM forum_messages
		WHERE fm_thr_id = {$Q_Threads['thr_id']}
	");
	
	$this->param('RowsPerPage',$this->threadsPerPage);
	$pagesHTML = '';
	if ($replies['TheCount'] > $this->ATTRIBUTES['RowsPerPage']) {
		$pagesHTML = '( <img src="Images/post-viewpage.gif"> ';
		for($i=1;$i<=ceil($replies['TheCount']/$this->ATTRIBUTES['RowsPerPage']);$i++) {
			$pagesHTML .= "<a href=\"".ss_HTMLEditFormat(ss_withoutPreceedingSlash($asset->getPath()))."?Service=ViewThread&thr_id={$Q_Threads['thr_id']}&CurrentPage=".$i."\">$i</a> ";
		}
		$pagesHTML .= ' )';
	}
    
	$Q_Threads['LastMessagePage'] = ceil($replies['TheCount']/$this->ATTRIBUTES['RowsPerPage']);
	$Q_Threads['Replies'] = $replies['TheCount']-1;
	$Q_Threads['PagesHTML'] = $pagesHTML;
	
    
    
    if($Q_Threads) {
	    $data = array(
		    'row'	=>	$Q_Threads,
		    'AssetPath'	=>	ss_withoutPreceedingSlash(ss_EscapeAssetPath($asset->getPath())),
		    'as_id'	=>	$asset->getID(),
		    'CurrentServer'	=>	$GLOBALS['cfg']['currentServer'],
	    );
    if ( $Q_AssetName = getRow("select as_name from assets where as_id =".$asset->getID()) )
        $data['as_name']=$Q_AssetName['as_name'];

	    ss_customStyleSheet($this->styleSheet);
	    $this->useTemplate('Peek',$data);
    } else {
        echo 'The forum is currently empty.';
     }

?>