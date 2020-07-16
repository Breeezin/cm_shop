<?php
	$data = array('imagesDirectory'	=>	$this->classDirectory.'/AssetTypes/'.$this->getClassName().'/Templates/Images/');
	$data['as_id'] = $asset->getID();
	$this->useTemplate('Edit',$data);
?>