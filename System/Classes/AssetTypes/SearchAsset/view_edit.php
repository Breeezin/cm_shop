<?php
	$data = array('imagesDirectory'	=>	$this->classDirectory.'/AssetTypes/'.$this->getClassName().'/Templates/Images/');	
	$data['as_id'] = $asset->getID();	
	$data['Prefix'] = $this->fieldPrefix;	
	$data['PerDisplay'] = '';
	if (array_key_exists($this->fieldPrefix.'ITEMSPERDISPLAY', $asset->cereal)) {
		$data['PerDisplay'] = $asset->cereal[$this->fieldPrefix.'ITEMSPERDISPLAY'];
	
	}
	$data['FieldSet'] = $this->fieldSet;
	$data['EnableSearchItem'] = '';
	
	ss_paramKey($asset->cereal,$this->fieldPrefix.'ENABLE_ITEMS', '');
	if ($asset->cereal[$this->fieldPrefix.'ENABLE_ITEMS'] != '1') {
		$data['EnableSearchItem'] = 'display:none';
	}
	
	$this->useTemplate('Edit',$data);
?>