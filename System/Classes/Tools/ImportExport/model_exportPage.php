<?php
	if ($this->ATTRIBUTES['Type'] == 'All' or $this->ATTRIBUTES['Type'] == 'Main') {
		$content = $this->exportpageGenerate($content);
		
		// Add some extra stuff
		$content = '<html><head><link rel="stylesheet" href="../Layouts/sty_main.css" type="text/css"></head><body>'.$content.'</body></html>';
		
		// Now output the file
		$outputFileName = $this->assetPathToExportFile($PagePath);
		$fp = fopen(expandPath('Custom/ContentStore/ImportExport/'.$outputFileName),'w');
		fwrite($fp,$content);
		fclose($fp);
		chmod (expandPath('Custom/ContentStore/ImportExport/'.$outputFileName), 0766);  
	}
	
	if ($this->ATTRIBUTES['Type'] == 'All' or $this->ATTRIBUTES['Type'] == 'Sub') {
		if (strlen($subContent)) {
			
			$subContent = $this->exportpageGenerate($subContent);
			// Add some extra stuff
			$subContent = '<html><head><link rel="stylesheet" href="../Layouts/sty_main.css" type="text/css"></head><body>'.$subContent.'</body></html>';
			$outputSubFileName = $this->assetPathToExportFile($PagePath.'-LYT_SUBCONTENT');
			// Now output the file		
			$fp = fopen(expandPath('Custom/ContentStore/ImportExport/'.$outputSubFileName),'w');
			fwrite($fp,$subContent);
			fclose($fp);
			chmod (expandPath('Custom/ContentStore/ImportExport/'.$outputSubFileName), 0766);  
		}
	}
?>