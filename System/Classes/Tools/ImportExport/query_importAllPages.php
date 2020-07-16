<?php
	$dir = 'Custom/ContentStore/ImportExport/';
	if ($dh = opendir($dir)) {
		while (($file = readdir($dh)) !== false) {
			 
			if (strtolower(substr($file,-5)) == '.html') {
				
				if (strlen($file) <= 20 or (strlen($file) > 20 and strtolower(substr($file,-20)) != '-lyt_subcontent.html')) {
					
					$assetPath = $this->exportFileToAssetPath($file);
					$result = new Request('Asset.IDFromPath',array(
						'AssetPath'	=>	$assetPath
					));
					if ($result->value === null) {
						// Asset wasn't found
						$assetNameWithoutExtension = array_pop(explode('/',ss_withoutPreceedingSlash($assetPath)));
						$parent = explode('/',ss_withoutPreceedingSlash($assetPath));
						$trash = array_pop($parent);
						if (count($parent) == 0) {
							$parent = array('index.php');	
						}
						$parentAsset = implode('/',$parent);
						
						$result = new Request('Asset.IDFromPath',array(
							'AssetPath'	=>	$parentAsset,
						));
						$parentAsset = $result->value;
						
						if ($result->value !== null) {
						
							// New page asset
							$result = new Request('Asset.Add',array(
								'as_name'	=>	$assetNameWithoutExtension,
								'as_type'	=>	'Page',
								'as_appear_in_menus'	=>	0,
								'as_parent_as_id'	=>	$parentAsset,
								'DoAction'	=>	1,
								'AsService'	=>	true,
							));					
						}
						
					}				
				}
			}
		}
		closedir($dh);
	}
	
	$Q_Assets = query("
		SELECT as_id FROM assets
		WHERE as_type LIKE 'Page'
			AND (as_deleted IS NULL OR as_deleted = 0)
	");
	
?>