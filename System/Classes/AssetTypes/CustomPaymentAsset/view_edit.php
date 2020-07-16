<?php
	$data = array(
		'FieldSet'	=>	$this->fieldSet,
		'as_id'	=>	$asset->getID(),
		'SecureSite'=>	$GLOBALS['cfg']['secure_server'],
	);
	$this->useTemplate("Edit",$data);
?>