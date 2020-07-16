<?php

	// Default some values
	$this->param('RowsPerPage',$this->threadsPerPage);
	$this->param('CurrentPage','1');
	$this->param('PagesPerBlock','100000');
	$this->param('OrderBy','');
	$this->param('SortBy','');
	
	$startRow = ($this->ATTRIBUTES['CurrentPage']-1)*$this->ATTRIBUTES['RowsPerPage'];
	$maxRows = $this->ATTRIBUTES['RowsPerPage'];
	
	// query the database
	$Q_Threads = query("
		SELECT *,
			FirstMessage.fm_poster_firstname AS FirstFirstName,
			FirstMessage.fm_poster_lastname AS FirstLastName,
			FirstMessage.fm_poster_email AS FirstEmail,
			LastMessage.fm_poster_firstname AS LastFirstName,
			LastMessage.fm_poster_lastname AS LastLastName,
			LastMessage.fm_poster_email AS LastEmail,
			LastMessage.fm_timestamp AS LastDateTime,
			LastMessage.fm_id AS LastMessageID,
			LastMessage.fm_poster_firstname AS LastFirstName
		FROM forum_threads, forum_messages AS FirstMessage, forum_messages AS LastMessage
		WHERE thr_id = FirstMessage.fm_thr_id AND FirstMessage.fm_id = 1
			AND thr_id = LastMessage.fm_thr_id AND LastMessage.fm_id = thr_last_thr_id
			AND thr_as_id = ".$asset->getID()."
		ORDER BY LastMessage.fm_timestamp DESC	
		LIMIT $startRow,$maxRows
	");

	// the the total number of rows
	$totalRows = getRow("
		SELECT COUNT(*) AS TheCount FROM forum_threads 
	");
	
	// display a page thru
	$backURL = ss_withoutPreceedingSlash($asset->getPath())."?Service=ThreadList";
	$pageThru = new Request('PageThru.Display',array(
		'ItemCount'		=>	$totalRows['TheCount'],	
		'ItemsPerPage'	=>	$this->ATTRIBUTES['RowsPerPage'],
		'CurrentPage'	=>	$this->ATTRIBUTES['CurrentPage'],
		'PagesPerBlock'	=>	$this->ATTRIBUTES['PagesPerBlock'],
		'URL'			=>	$backURL,
		'HidePreviousNext'	=>	true,
		'BeforePageThru'		=>	'',
		'BeforePrevious10'		=>	'<td width="20" height="20" align="center" class="forumNumber">',
		'AfterPrevious10'		=>	'</td>',
		'BeforePrevious'		=>	'<td width="20" height="20" align="center" class="forumNumber">',
		'AfterPrevious'			=>	'</td>',
		'BeforeLink'			=>	'<td width="20" height="20" align="center" class="forumNumberOn">',
		'AfterLink'				=>	'</td>',
		'BeforeCurrent'			=>	'<td width="20" height="20" align="center" class="forumNumber">',
		'AfterCurrent'			=>	'</td>',
		'BeforeNext'			=>	'<td width="20" height="20" align="center" class="forumNumber">',
		'AfterNext'				=>	'</td>',
		'BeforeNext10'			=>	'<td width="20" height="20" align="center" class="forumNumber">',
		'AfterNext10'			=>	'</td>',
		'AfterPageThru'			=>	'',
	));

	// Add some useful stuff into the query :-)
	$Q_Threads->addColumn('LastMessagePage');
	$Q_Threads->addColumn('Replies');
	$Q_Threads->addColumn('PagesHTML');
	
	$currentRow = 0;
	while($row = $Q_Threads->fetchRow() ){
		
		// Find out how many replies in the thread
		$replies = getRow("
			SELECT COUNT(*) AS TheCount FROM forum_messages
			WHERE fm_thr_id = {$row['thr_id']}
		");
		
		$pagesHTML = '';
		if ($replies['TheCount'] > $this->ATTRIBUTES['RowsPerPage']) {
			$pagesHTML = '( <img src="Images/post-viewpage.gif"> ';
			for($i=1;$i<=ceil($replies['TheCount']/$this->ATTRIBUTES['RowsPerPage']);$i++) {
				$pagesHTML .= "<a href=\"".ss_HTMLEditFormat(ss_withoutPreceedingSlash($asset->getPath()))."?Service=ViewThread&thr_id={$row['thr_id']}&CurrentPage=".$i."\">$i</a> ";
			}
			$pagesHTML .= ' )';
		}
		$Q_Threads->setCell('PagesHTML',$pagesHTML,$currentRow);
		
		$Q_Threads->setCell('Replies',$replies['TheCount']-1,$currentRow);
		$Q_Threads->setCell('LastMessagePage',ceil($replies['TheCount']/$this->ATTRIBUTES['RowsPerPage']),$currentRow);
		
		$currentRow++;
	}	
	
	// Check the subscription level of the current user
	if (ss_HasPermission('IsLoggedIn')) {
		$subscription = 'none';
		$allThreads = query("
			SELECT * FROM forum_thread_subscriptions
			WHERE fts_us_id = ".ss_getUserID()."
				AND fts_thr_id IS NULL
				AND fts_as_id = ".$asset->getID()."
		");
		if ($allThreads->numRows()) {
			$status = $allThreads->fetchRow();
			if ($status['fts_first_post_only'] == 1) {
				$subscription = $asset->getID()."_fristpsot";	
			} else {
				$subscription = $asset->getID();	
			}
		}
	} else {
		// Guests dont have settings
		$subscription = 'n/a';	
	}
?>
