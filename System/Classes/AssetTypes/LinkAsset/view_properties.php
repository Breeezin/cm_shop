<?php
	ss_paramKey($asset->cereal,$this->fieldPrefix.'URL');
	ss_paramKey($asset->cereal,$this->fieldPrefix.'RELATIVE',0);
		
	//print $asset->cereal[$this->fieldPrefix.'URL'];
	if ($asset->cereal[$this->fieldPrefix.'RELATIVE'] == 1) {
		$link = $GLOBALS['cfg']['currentServer'].$asset->cereal[$this->fieldPrefix.'URL'];
	} else {
		$link = $asset->cereal[$this->fieldPrefix.'URL'];
	}		
	
?>
<table cellpadding="0" cellspacing="2" width="100%">
	<tr>
		<td class="propertiesLabel">Link :</td>
		<td><a href="<?=$link?>">Click to open</a></td>
	</tr>
</table>