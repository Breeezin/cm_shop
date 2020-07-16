<?php
	$assetID = $asset->getID();
	$assetPath = ss_withoutPreceedingSlash($asset->getPath());
	$data = array('FieldSet' => $this->fieldSet, 'as_id' => $assetID);
	$secureSite = ss_withTrailingSlash($GLOBALS['cfg']['secure_server']);
	$data['SubscriptionOrders'] = "WebPayAdministration.List&as_id=$assetID";
	$data['SecureSite'] = $secureSite;
	
	$this->useTemplate("Edit",$data);
?>