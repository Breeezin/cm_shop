<?php
	ss_paramKey($asset->cereal,$this->fieldPrefix.'STD');	
	
	$fileLocation = '';
	if (strlen($asset->cereal[$this->fieldPrefix.'STD'])) {
		$fileLocation = ss_storeForAsset($asset->getID()).$asset->cereal[$this->fieldPrefix.'STD'];
	}
	
	$size = '0';
	if (file_exists($fileLocation)) {
		$size = sprintf("%1.1f",filesize($fileLocation)/1024);
	}
	
	
?>
<table cellpadding="0" cellspacing="2" width="100%">
	<tr>
		<td class="propertiesLabel">Size :</td>
		<td><?=$size?> KiB</td>
	</tr>
	<tr>
		<td class="propertiesLabel" valign="top">Thumbnail :</td>
		<td>
			<IMG BORDER="0" ALT="<?=$asset->fields['as_name']?>" SRC="index.php?act=ImageManager.get&Image=<?=ss_URLEncodedFormat($fileLocation)?>&Size=40x40">		
		</td>
	</tr>
</table>

