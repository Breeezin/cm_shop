<?php	

	$data = array(
		'Width'	=>	$asset->cereal['AST_FLASH_WIDTH'],
		'Height'	=>	$asset->cereal['AST_FLASH_HEIGHT'],
		'AssetFileFullPath'	=>	'',
		'Attributes'	=>	'',
	);
	
	$name = $asset->cereal['AST_FLASH_FILENAME'];
	if (strlen($name)) {
		$data['AssetFileFullPath'] = ss_storeForAsset($asset->getID()).$name;
	}
	
	$attributes = $asset->cereal['AST_FLASH_ATTRIBUTES'];
	if (count($attributes)) {
		$data['Attributes'] .= '?';
		$index = 0;
		foreach($attributes as $att) {
			if ($index) {
				$data['Attributes'] .='&';
			}
			
			$data['Attributes'] .= $att['attName'].'='.$att['attValue'];
			$index++;
		}
	}
	
	$this->useTemplate("Display",$data);
?>