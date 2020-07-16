<?php
	ss_paramKey($asset->cereal,$this->fieldPrefix.'ARRAY');	
	
	$fileLocation = '';
		
	$fileLocation = expandPath(ss_storeForAsset($asset->getID()));
	
	
	$size = 0;
	$printSize = '0';
	if (is_dir($fileLocation)) {
		$dh=opendir($fileLocation);
		
		while ($file=readdir($dh)){
			if($file!="." && $file!=".."){
				$fullpath=$fileLocation.$file;							
				$size += filesize($fullpath);
			}				
		}
		$printSize = sprintf("%1.1f",$size/1024);
	}
	
?>
<table cellpadding="0" cellspacing="2" width="100%">
	<tr>
		<td class="propertiesLabel">Size :</td>
		<td><?=$printSize?> KiB</td>
	</tr>	
</table>

