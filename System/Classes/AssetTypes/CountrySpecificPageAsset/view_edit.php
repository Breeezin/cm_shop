<?php
	$data = array(
		'imagesDirectory'	=>	$this->classDirectory.'/AssetTypes/'.$this->getClassName().'/Templates/Images/',
		'FieldSet'	=>	$this->fieldSet,
		'as_id'	=>	$asset->getID(),
	);
	$this->useTemplate('Edit',$data);
?>