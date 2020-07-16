<?php 	
	if (array_key_exists('LYT_TITLEIMAGE',$data['this']->assetLayoutSettings) && strlen($data['this']->assetLayoutSettings['LYT_TITLEIMAGE'])) {
		print "<IMG ALT=\"{$data['this']->title}\" SRC=\"".ss_storeForAsset($data['this']->assetID)."{$data['this']->assetLayoutSettings['LYT_TITLEIMAGE']}\">";
	} else {
		if (($data['this']->titleImage != NULL) && (strlen($data['this']-titleImage) > 0)) { 
			// Use the specified header image
			print "<IMG ALT=\"{$data['this']->title}\" SRC=\"Custom/ContentStore/Layouts/images/{$data['this']->titleImage}\">";
		} else {
			$title = strtolower(str_replace(' ','',$data['this']->title));
			if (file_exists("Custom/ContentStore/Layouts/images/Headers/h-{$title}.gif")) {
				// Use auto-detected header image
				print "<IMG ALT=\"{$data['this']->title}\" SRC=\"Custom/ContentStore/Layouts/images/Headers/h-{$title}.gif\">";
			} else {
				// Display plain text header
				print ss_HTMLEditFormat($data['this']->title);	
			}
		}
	}
?>