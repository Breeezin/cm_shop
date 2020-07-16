<?php 
	if (array_key_exists('LYT_WINDOWTITLE',$data['this']->assetLayoutSettings) && strlen($data['this']->assetLayoutSettings['LYT_WINDOWTITLE'])) {
		print ss_HTMLEditFormat($data['this']->assetLayoutSettings['LYT_WINDOWTITLE']);
	} else {
		print ss_HTMLEditFormat($data['this']->siteName)." - ".ss_HTMLEditFormat($data['this']->simpleTitle);
	}
?>