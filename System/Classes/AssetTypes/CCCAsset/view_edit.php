<?php
	$data = array('imagesDirectory'	=>	$this->classDirectory.'/AssetTypes/'.$this->getClassName().'/Templates/Images/');
	$data['as_id'] = $asset->getID();	
	$data['Prefix'] = $this->fieldPrefix;
	$data['PerPage'] = '';
	if (array_key_exists($this->fieldPrefix.'ITEMS_PER_PAGE', $asset->cereal)) {
		$data['PerPage'] = $asset->cereal[$this->fieldPrefix.'ITEMS_PER_PAGE'];
	
	}
	
	$this->useTemplate('Edit',$data);
?>