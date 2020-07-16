<?php
	$this->display->layout = 'none';

	$this->param('as_id');
	
	ss_RestrictPermission('CanAdministerAtLeastOneAsset');
	
	$this->loadAsset();
	
	if (ss_HasPermission('IsSuperUser')) {
		$canChangeStuff = ($this->fields['as_system'] != 1);
	} else {
		$canChangeStuff = ($this->fields['as_owner_au_id'] != 0) and ($this->fields['as_system'] != 1);
	}
	
	if ($canChangeStuff) {
		if (array_key_exists('as_name',$this->ATTRIBUTES)) {
			
			$asset = getRow("
				SELECT as_parent_as_id FROM assets
				WHERE as_id = ".safe($this->ATTRIBUTES['as_id'])."
			");

			$as_name = ss_newAssetName($this->ATTRIBUTES['as_name'],$asset['as_parent_as_id']);
			
			$Q_Update = query("
				UPDATE assets
				SET as_name = '".escape($as_name)."'
				WHERE as_id = ".safe($this->ATTRIBUTES['as_id'])."
			");

?>
<html><body><script language="Javascript">
d = parent.document; f = d.forms.PropertiesPanelForm; n = f.as_name;
f.as_name.value = '<?=ss_JSStringFormat($as_name)?>';
parent.assetReload();
</script></body></html>
<?
			
		} else if (array_key_exists('as_appear_in_menus',$this->ATTRIBUTES)) {
			
			$Q_Update = query("
				UPDATE assets
				SET as_appear_in_menus = ".safe($this->ATTRIBUTES['as_appear_in_menus'])."
				WHERE as_id = ".safe($this->ATTRIBUTES['as_id'])."
			");
			
		}

	}
	
?>
