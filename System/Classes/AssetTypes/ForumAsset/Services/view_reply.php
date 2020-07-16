<?php

	$data = array(
		'AssetPath'	=>	ss_withoutPreceedingSlash(ss_EscapeAssetPath($asset->getPath())),
		'as_id'	=>	$asset->getID(),
		'CurrentServer'	=>	$GLOBALS['cfg']['currentServer'],
		'thr_id'		=>	$this->ATTRIBUTES['thr_id'],
		'fm_id'		=>	$this->ATTRIBUTES['fm_id'],
		'Content'	=>	$this->ATTRIBUTES['Content'],
		'Subject'	=>	$reply['thr_subject'],
		'FormattedContent'	=>	$this->formatPost($this->ATTRIBUTES['Content']),
		'Preview'	=>	($this->ATTRIBUTES['Submit'] == 'Preview'),
		'Errors'	=>	$errors,
	);
	
	ss_customStyleSheet($this->styleSheet);
	$this->useTemplate('Reply',$data);

?>