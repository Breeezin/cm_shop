<?php

	$this->param('as_id');

	$Q_SubAssets = query("
		SELECT * FROM assets 
		WHERE as_parent_as_id = {$this->ATTRIBUTES['as_id']} 
			AND as_deleted != 1
			AND as_hidden != 1
		ORDER BY as_sort_order, as_name ASC
	");
	
	$data = array();
	$data['Q_SubAssets'] = $Q_SubAssets;

	$this->display->layout = 'none';
	$this->useTemplate('ChildrenRefresh',$data);
?>