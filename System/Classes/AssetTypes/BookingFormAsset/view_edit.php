<?php
	$data = array(
		'FieldSet'	=>	$this->fieldSet,
		'as_id'			=>	$asset->getID(),		
		'SecureSite'	=>	ss_withTrailingSlash($GLOBALS['cfg']['secure_server']),
	);
	$this->useTemplate("Edit",$data);
?>