<?php 
	return;
	$Q_Asset = query("SELECT as_id, as_serialized, as_layout_serialized FROM assets");
	
	while ($aAsset = $Q_Asset->fetchRow()) 
	{
    	$temp = @unserialize($aAsset['as_serialized']); 
    	$temp2 = @unserialize($aAsset['as_layout_serialized']);

    	$temp = serialize($temp);
    	$temp2 = serialize($temp2);
    	$Q_update = query("UPDATE assets 
    						SET as_serialized = '".escape($temp)."', 
    							as_layout_serialized = '".escape($temp2)."'
    						WHERE as_id = ".$aAsset['as_id']
    					);
	}

?>
