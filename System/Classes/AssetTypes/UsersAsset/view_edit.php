<?php
	$data = array(
		'imagesDirectory'	=>	$this->classDirectory.'/AssetTypes/'.$this->getClassName().'/Templates/Images/',
		'FieldSet'	=>	$this->fieldSet,
	);
	$this->useTemplate('Edit',$data);
?>