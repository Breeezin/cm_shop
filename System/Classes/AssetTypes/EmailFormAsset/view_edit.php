<?php
	$data = array(
		'FieldSet'	=>	$this->fieldSet,
		'as_id'	=>	$asset->getID(),
	);
	$this->useTemplate("Edit",$data);
?>