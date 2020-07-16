<?php
	$this->display->layout = 'none';

	$this->param('as_id');
	
	ss_RestrictPermission('CanAdministerAtLeastOneAsset');
	
	$this->loadAsset();

	$canChangeStuff = ss_HasPermission('CanHighLevelAdministerAsset',$this->ATTRIBUTES['as_id']);
	if ($canChangeStuff) {	
		$cantChangeStuff = 'false';
	} else {
		$cantChangeStuff = 'true';
	}
	
	// Include and instantiate the class type
	$className = $this->fields['as_type'].'Asset';
	requireOnceClass($className);
	$temp = new $className;
	$temp->ATTRIBUTES = &$this->ATTRIBUTES;

	// Call the display handler for the specific type
	ss_ob_start();
	$temp->properties($this);
	$assetTypeSpecific = ob_get_contents();
	ss_ob_end_clean();
	
?>
<html><body><script language="Javascript">
d = parent.document; f = d.forms.PropertiesPanelForm; n = f.as_name;
n.disabled = <?=$cantChangeStuff?>; n.value = '<?=ss_JSStringFormat($this->fields['as_name'])?>'; 
m = f.as_appear_in_menus; m.disabled = <?=$cantChangeStuff?>; m.checked = <? if ($this->fields['as_appear_in_menus']) print('true'); else print('false');?>; 
f.as_id.value = <?=$this->ATTRIBUTES['as_id']?>;
d.getElementById('propertiesPanelBarText').innerHTML = '<?=ss_JSStringFormat($this->fields['at_display'])?>';
d.getElementById('assetTypeProperties').innerHTML = '<?=ss_JSStringFormat($assetTypeSpecific)?>';
</script></body></html>
