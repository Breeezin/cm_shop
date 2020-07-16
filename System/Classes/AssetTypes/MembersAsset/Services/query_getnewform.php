<?php 
	$this->param('IndustryType', '');
	if (strlen($this->ATTRIBUTES['IndustryType'])) {
		locationRelative($assetPath.'/Service/New');
	}
	
	
	// if the industry type selected is other, then the form is for Frequent Buyer Club type otherwise Travel Industry
	if ($this->ATTRIBUTES['IndustryType'] == 'Other') {
		$memberType = 'FB';
	} else {
		$memberType = 'TI';
	}
	requireOnceClass("LoyalMembersAdministration");
	$userAdmin = new LoyalMembersAdministration($memberType);
	$this->ATTRIBUTES['BackURL'] = $assetPath."/Service/GetNewForm";
	$this->ATTRIBUTES['act'] = $assetPath."/Service/GetNewForm/DoAction/Yes";	
	$userAdmin->ATTRIBUTES = $this->ATTRIBUTES;
	
	$errors = array();	
	
?>