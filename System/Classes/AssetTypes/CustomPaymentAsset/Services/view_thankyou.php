<?php 
	ss_paramKeyAndNoStringLength($asset->cereal,$this->fieldPrefix."THANK_YOU_PAGE","Thank You. We will be in contact with you shortly.");
	print(ss_parseText($asset->cereal[$this->fieldPrefix."THANK_YOU_PAGE"]));
	$asset->display->title = 'Thank You';
?>