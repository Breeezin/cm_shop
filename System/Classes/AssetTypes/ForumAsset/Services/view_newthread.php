<?php

	$data = array(
		'AssetPath'	=>	ss_withoutPreceedingSlash(ss_EscapeAssetPath($asset->getPath())),
		'as_id'	=>	$asset->getID(),
		'CurrentServer'	=>	$GLOBALS['cfg']['currentServer'],
		'FormattedContent'	=>	$this->formatPost($this->ATTRIBUTES['Content']),
		'Preview'	=>	($this->ATTRIBUTES['Submit'] == 'Preview'),
		'Content'	=>	$this->ATTRIBUTES['Content'],
		'Subject'	=>	$this->ATTRIBUTES['Subject'],
		'Errors'	=>	$errors,
	);
	
	ss_customStyleSheet($this->styleSheet);
	$this->useTemplate('NewThread',$data);

?>