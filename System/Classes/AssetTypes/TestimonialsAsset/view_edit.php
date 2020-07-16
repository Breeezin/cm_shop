<?php
	$data = array('imagesDirectory'	=>	$this->classDirectory.'/AssetTypes/'.$this->getClassName().'/Templates/Images/');
	$data['as_id'] = $asset->getID();	
	$data['Prefix'] = $this->fieldPrefix;
	$data['PanelItems'] = '';
	if (array_key_exists($this->fieldPrefix.'PANELITEMS', $asset->cereal)) {
		$data['PanelItems'] = $asset->cereal[$this->fieldPrefix.'PANELITEMS'];
	
	}
	
	$this->useTemplate('Edit',$data);
?>