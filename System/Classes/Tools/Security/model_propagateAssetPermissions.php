<?php
	$this->param('as_id');

	ss_ExecuteRequestOnBranchAssets($this->ATTRIBUTES['as_id'],'Security.CreateAssetPermissions',array(
		'UpdateType'	=>	'Propagate'
	));

?>