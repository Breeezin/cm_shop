<?php

	$uppercase = FALSE;
	$lowercase = FALSE;
	//if (strtolower($this->ATTRIBUTES['lowercase']) == 'yes') $lowercase = TRUE;
	//if (strtolower($this->ATTRIBUTES['uppercase']) == 'yes') $uppercase = TRUE;

	$separator = '';
	
	// Get the path of the root asset
	$path = new Request("Asset.PathFromID",array('as_id' => $this->ATTRIBUTES['AST_MENU_FOOTER_ROOT_ASSETID']));
	$this->ATTRIBUTES['root'] = $path->value;
		
	
	$this->ATTRIBUTES['root'] = ss_withoutPreceedingSlash($this->ATTRIBUTES['root']);
	if ($this->ATTRIBUTES['root'] == 'index.php') {
		$this->ATTRIBUTES['root'] = '';
	} else {
		$this->ATTRIBUTES['root'] = ss_withTrailingSlash($this->ATTRIBUTES['root']);
	}
	
	$counter = 1;
	if (!$noMenus) {
	$rowCount = $result->numRows();
		while ($row = $result->fetchRow()) {
	
			// Figure out what to display
			if ($row['as_menu_name'] != null) {
				$display = $row['as_menu_name'];
			} else {
				$display = $row['as_name'];
			}
			
			if ($lowercase) $display = strtolower($display);
			if ($uppercase) $display = strtoupper($display);
			
			// Figure out the link
			$link = "{$this->ATTRIBUTES['root']}{$row['as_name']}";
			$link = ss_EscapeAssetPath($link);
			
			// Figure out the separator
			if ($counter-1 == $this->ATTRIBUTES['AST_MENU_FOOTER_LINKSPERFIRSTROW']) {
				$separator = '<BR>';
			} 
			if (($counter != 1) && ($counter % $this->ATTRIBUTES['AST_MENU_FOOTER_LINKSPERROW'] == 0)) {
				$separator = '<BR>';
			}
		
			// Display it	
			if( IsSet( $row['as_subtitle'] ) and strlen( $row['as_subtitle'] ) > 0 )
				print "$separator<A HREF=\"$link\" CLASS=\"{$this->ATTRIBUTES['AST_MENU_FOOTER_LINKCLASS']}\" title=\"{$row['as_subtitle']}\">$display</A>";
			else
				print "$separator<A HREF=\"$link\" CLASS=\"{$this->ATTRIBUTES['AST_MENU_FOOTER_LINKCLASS']}\">$display</A>";
			
			$separator = $this->ATTRIBUTES['AST_MENU_FOOTER_SEPARATOR'];
			$counter++;
		}
	}

?>
