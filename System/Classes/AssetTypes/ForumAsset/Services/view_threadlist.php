<?php

// try and get the asset name

	$data = array(
		'PageThruHTML'	=>	$pageThru->display,
		'Q_Threads'	=>	$Q_Threads,
		'AssetPath'	=>	ss_withoutPreceedingSlash(ss_EscapeAssetPath($asset->getPath())),
		'as_id'	=>	$asset->getID(),
		'Subscription'	=>	$subscription,
		'CurrentServer'	=>	$GLOBALS['cfg']['currentServer'],
		'IsAdmin'		=>	$this->isAdmin,
	);

    if ( $Q_AssetName = getRow("select as_name from assets where as_id =".$asset->getID()) )
        $data['as_name']=$Q_AssetName['as_name'];

	ss_customStyleSheet($this->styleSheet);
	$this->useTemplate('ThreadList',$data);
	
?>
