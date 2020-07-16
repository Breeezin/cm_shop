<?php

	
	$data = array(
		'FieldSet'	=>	$this->fieldSet,
		'AssetFileFullPath'	=>	'',
		'Attributes'	=>	'',
	);
	$name = $this->fieldSet->getFieldValue($this->fieldPrefix.'FILENAME');
	if (strlen($name)) {
		$data['AssetFileFullPath'] = ss_storeForAsset($asset->getID()).$name;
	}
	$attributes = $this->fieldSet->getFieldValue($this->fieldPrefix.'ATTRIBUTES');
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
	$this->useTemplate("Edit",$data);
?>