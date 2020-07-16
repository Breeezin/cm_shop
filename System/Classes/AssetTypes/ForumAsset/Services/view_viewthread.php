<?php

	$data = array(
		'PageThruHTML'	=>	$pageThru->display,
		'Q_Messages'	=>	$Q_Messages,
		'Thread'		=>	$Thread,
		'AssetPath'		=>	ss_withoutPreceedingSlash(ss_EscapeAssetPath($asset->getPath())),
		'CurrentServer'	=>	$GLOBALS['cfg']['currentServer'],
		'IsAdmin'		=>	$this->isAdmin,
	);

	ss_customStyleSheet($this->styleSheet);
	$this->useTemplate('ViewThread',$data);
?>