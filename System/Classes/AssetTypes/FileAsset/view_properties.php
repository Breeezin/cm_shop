<?php
	ss_paramKey($asset->cereal,$this->fieldPrefix.'FILENAME','');

	$fileName = $asset->cereal[$this->fieldPrefix.'FILENAME'];
	$filePath = ss_storeForAsset($asset->getID()).$fileName;
	
	// Because the file is in a folder to make it secure
	$pureFileName = ListLast($fileName,"/");
	
	if (strlen($pureFileName) > 15) {
		$pureFileName = substr($pureFileName,0,12)."...";	
	}
	
	if (file_exists($filePath) and strlen($fileName)) {
		$fileSize = sprintf("%1.1f",filesize($filePath)/1024);
		
?>	
<table cellpadding="0" cellspacing="2" width="100%">
	<tr>
		<td class="propertiesLabel">File :</td>
		<td><a href="<?=$filePath?>" target="_blank"><?=ss_HTMLEditFormat($pureFileName)?></a></td>
	</tr>
	<tr>
		<td class="propertiesLabel">Size :</td>
		<td><?=$fileSize?> KiB</td>
	</tr>
</table>
<?	}  ?>