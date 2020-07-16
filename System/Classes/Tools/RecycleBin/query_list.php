<?php 
	$this->param('Message','');
	$Q_DeletedAssets = query("SELECT * FROM assets WHERE as_deleted = 1");
	
?>