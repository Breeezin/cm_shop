<?php

	$this->param('as_id');
	$this->param('Type', 'Main');

	$Page = getRow("
		SELECT * FROM assets
		WHERE as_id = ".safe($this->ATTRIBUTES['as_id'])."
	");
	
	$PageDetails = array();
	if (($Page['as_serialized'] !== null) and (strlen($Page['as_serialized']))) {
		$PageDetails = unserialize($Page['as_serialized']);
	}
	$LayoutDetails = array();
	if (($Page['as_layout_serialized'] !== null) and (strlen($Page['as_layout_serialized']))) {
		$LayoutDetails = unserialize($Page['as_layout_serialized']);
	}
	
	$result = new Request('Asset.PathFromID',array(
		'as_id'	=>	$this->ATTRIBUTES['as_id'],
	));
	$PagePath = $result->value;
	
	ss_paramKey($PageDetails,'AST_PAGE_PAGECONTENT','');
	$content = $PageDetails['AST_PAGE_PAGECONTENT'];
	
	ss_paramKey($LayoutDetails,'LYT_LAYOUT_SUBPAGECONTENT','');	
	$subContent = $LayoutDetails['LYT_LAYOUT_SUBPAGECONTENT'];
	
	
?>
