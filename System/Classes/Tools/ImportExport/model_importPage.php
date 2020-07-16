<?php

	if ($this->ATTRIBUTES['Type'] == 'All' or $this->ATTRIBUTES['Type'] == 'Main') {
		if (isset($content)) {	
			$content = $this->importpageGenerate($content);
			$PageDetails['AST_PAGE_PAGECONTENT'] = $content;
			
			$PageDetailsCereal = serialize($PageDetails);
			
			$result = query("
				UPDATE assets
				SET as_serialized = '".escape($PageDetailsCereal)."',
					as_search_content = '".escape(strip_tags($content))."'
				WHERE as_id = {$Page['as_id']}
			");
			
		}
	}
	if ($this->ATTRIBUTES['Type'] == 'All' or $this->ATTRIBUTES['Type'] == 'Sub') {
		if (isset($subContent)) {	
			$subContent = $this->importpageGenerate($subContent);
			$LayoutDetails['LYT_LAYOUT_SUBPAGECONTENT'] = $subContent;
			
			$LayoutDetailsCereal = serialize($LayoutDetails);
			
			$result = query("
				UPDATE assets
				SET as_layout_serialized = '".escape($LayoutDetailsCereal)."'			
				WHERE as_id = {$Page['as_id']}
			");
			
		}
	}
?>
