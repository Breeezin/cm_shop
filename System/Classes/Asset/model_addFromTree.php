<?php
	$this->display->layout='none';

	$this->param('as_type');
	$this->param('as_parent_as_id');

	$result = new Request("Asset.Add",array(
		'AsService'	=>	true,
		'DoAction'	=>	true,
		'as_type'	=>	$this->ATTRIBUTES['as_type'],
		'as_appear_in_menus'	=>	0,
		'as_parent_as_id'	=>	$this->ATTRIBUTES['as_parent_as_id'],
	));
	
	if ($result->value !== null) {	
		$asset = getRow("
			SELECT * FROM assets
			WHERE as_id = {$result->value}		
		");
		
?>
<html><body><script language="Javascript">
	parent.subAssetAddCallback(<?=$result->value?>, '<?=ss_JSStringFormat($asset['as_name'])?>', '<?=ss_JSStringFormat($asset['as_type'])?>');
</script></body></html>
<?
	} else {
?>
<html><body><script language="Javascript">
	alert('Error. <?=$this->ATTRIBUTES['as_type']?> limit reached.');	
</script></body></html>
<?
	}
?>