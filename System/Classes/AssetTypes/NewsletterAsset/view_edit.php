<?php
	$data = array('imagesDirectory'	=>	$this->classDirectory.'/AssetTypes/'.$this->getClassName().'/Templates/Images/');
	$data['as_id'] = $asset->getID();
	$data['FieldSet'] = $this->fieldSet;
	$this->useTemplate('Edit',$data);
?>