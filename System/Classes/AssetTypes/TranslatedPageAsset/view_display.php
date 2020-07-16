<?php
	if (array_key_exists("Translate",$_REQUEST)) {
		ss_paramKey($asset->cereal,'AST_PAGE_TRANSLATEDPAGECONTENT','');
		print ss_parseText($asset->cereal['AST_PAGE_TRANSLATEDPAGECONTENT'],$asset->getID());
	} else {
		ss_paramKey($asset->cereal,'AST_PAGE_PAGECONTENT','');
		print ss_parseText($asset->cereal['AST_PAGE_PAGECONTENT'],$asset->getID());
	}
	$_REQUEST['TranslationAvailable'] = 1;
?>		