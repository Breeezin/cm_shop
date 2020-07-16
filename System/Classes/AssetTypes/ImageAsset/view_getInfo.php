<?php
	
	ss_paramKey($asset->cereal,$this->fieldPrefix.'STD');	
	require_once('System/Libraries/image/image.php');
	
	$img = new image(ss_storeForAsset($asset->getID()).$asset->cereal[$this->fieldPrefix.'STD']);
?>
<FORM name="AssetInfo">
	<input type="hidden" name="Folder" value="<?=ss_storeForAsset($asset->getID())?>">
	<input type="hidden" name="FileName" value="<?=$asset->cereal[$this->fieldPrefix.'STD']?>">
	<input type="hidden" name="ImageWidth" value="<?=$img->getWidth()?>">
	<input type="hidden" name="ImageHeight" value="<?=$img->getHeight()?>">
</FORM>
<?php 
	
	if (array_key_exists('Caller', $this->ATTRIBUTES)) {
		print("<script>{$this->ATTRIBUTES['Caller']}</script>");
	}
?>