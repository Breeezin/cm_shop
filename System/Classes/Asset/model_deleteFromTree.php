<?php
	$this->display->layout='none';

	$this->param('as_id');

	if (ss_HasPermission('CanHighLevelAdministerAsset',$this->ATTRIBUTES['as_id'])) {

		$result = new Request("Asset.Delete",array(
			'as_id'	=>	$this->ATTRIBUTES['as_id'],
			'AsService'	=>	true,
		));
		
		
?>
<html><body><script language="Javascript">
	parent.closeAssets(new Array('<?=$this->ATTRIBUTES['as_id']?>'));
	parent.assetReload();
	alert('The item has been deleted.');
</script></body></html>
<?
	} else {
?>
<html><body><script language="Javascript">
	alert('You do not have permission to delete this item.');	
</script></body></html>
<?
	}
?>