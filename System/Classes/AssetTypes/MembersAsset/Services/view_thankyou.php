<?php
	ss_paramKey($asset->cereal,$this->fieldPrefix.'REGISTRATION_THANK_YOU_CONTENT','');


	$editableContent = ss_parseText($asset->cereal[$this->fieldPrefix.'REGISTRATION_THANK_YOU_CONTENT']);
	$editableContent = stri_replace('[first_name]',ss_HTMLEditFormat(ss_getFirstName()),$editableContent);
	$editableContent = stri_replace('[last_name]',ss_HTMLEditFormat(ss_getLastName()),$editableContent);
	$data = array(
		'EditableContent'	=>	$editableContent,		
	);

	//ss_DumpVarDie($data,'', true);
	$this->useTemplate('ThankYouService',$data);
?>