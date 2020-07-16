<?php

	// Default some values
	$this->param('RowsPerPage',$this->messagesPerPage);
	$this->param('CurrentPage','1');
	$this->param('PagesPerBlock','100000');
	$this->param('OrderBy','');
	$this->param('SortBy','');

	$this->param('thr_id');
	
	$startRow = ($this->ATTRIBUTES['CurrentPage']-1)*$this->ATTRIBUTES['RowsPerPage'];
	$maxRows = $this->ATTRIBUTES['RowsPerPage'];
	
	// Grab the thread
	$Thread = getRow("
		SELECT * FROM forum_threads, forum_messages
		WHERE thr_id = ".safe($this->ATTRIBUTES['thr_id'])."
			AND fm_thr_id = ".safe($this->ATTRIBUTES['thr_id'])."
			AND fm_id = 1
	");

	// Update the "viewed" stats
	$sessionKey = 'Forum'.$asset->getID().'ViewedThreads';
	ss_paramKey($_SESSION,$sessionKey,array());
	$views = $Thread['thr_views'];
	if (!strlen($views)) $views = 0;
	if (array_search($this->ATTRIBUTES['thr_id'],$_SESSION[$sessionKey]) === false) {
		$views++;
		$updateStats = query("
			UPDATE forum_threads
			SET thr_views = $views
			WHERE thr_id = ".safe($this->ATTRIBUTES['thr_id'])."
		");	
		array_push($_SESSION[$sessionKey],$this->ATTRIBUTES['thr_id']);
	}
	
	// Grab the messages
	$Q_Messages = query("
		SELECT * FROM forum_messages
		WHERE fm_thr_id = ".safe($this->ATTRIBUTES['thr_id'])."
		ORDER BY fm_id ASC
		LIMIT $startRow,$maxRows
	");
	
	// Figure out how many posts for each user and also format all the message bodies
	$Q_Messages->addColumn('Posts');
	$currentRow = 0;
	while ($row = $Q_Messages->fetchRow()) {
		$posts = getRow("
			SELECT COUNT(*) AS TheCount FROM forum_messages
			WHERE fm_poster_us_id = {$row['fm_poster_us_id']}
		");
		$Q_Messages->setCell('Posts',$posts['TheCount'],$currentRow);

		$Q_Messages->setCell('fm_content',$this->formatPost($row['fm_content']),$currentRow);
		
		$currentRow++;
	}
	
	// the the total number of messages
	$totalRows = getRow("
		SELECT COUNT(*) AS TheCount FROM forum_messages
		WHERE fm_thr_id = ".safe($this->ATTRIBUTES['thr_id'])."
	");
	
	// display a page thru
	$backURL = ss_withoutPreceedingSlash($asset->getPath())."?Service=ViewThread&thr_id=".$this->ATTRIBUTES['thr_id'];
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
	
?>
