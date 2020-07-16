<?php
	ss_paramKey($asset->cereal,$this->fieldPrefix.'STD');	
	locationRelative(ss_storeForAsset($asset->getID()).$asset->cereal[$this->fieldPrefix.'STD']);
?>