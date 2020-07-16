<?php
/* security */
	$this->param('as_id', '0');
	$this->param('as_owner_au_id', '0');
	
	
	$this->display->layout = 'None';
	$id = $this->ATTRIBUTES['as_id'];
	$owner = $this->ATTRIBUTES['as_owner_au_id'];

	$asset = getRow("SELECT * FROM assets WHERE as_id = {$this->ATTRIBUTES['as_id']}");
	
	$ownerDetails = getRow("SELECT * FROM users WHERE us_id = $owner");
	/* Get a list of all groups */
	$Q_UserGroups = query("SELECT * FROM user_groups	WHERE ug_mailing_list IS NULL OR ug_mailing_list = 0 ORDER BY ug_name");
  /*
    AllowChange="#IIF((SESSION.User eq ATTRIBUTES.as_owner_au_id) OR (SESSION.User eq 0), DE('Yes'), DE('No'))#"
	AllowChangeUse="#IIF((Column EQ 'Use') AND (NOT ss_OptionExists('Members Area')),DE('No'),DE('Yes'))#"				
	
	AllowChange="#IIF((SESSION.User eq ATTRIBUTES.as_owner_au_id) OR (SESSION.User eq 0), DE('Yes'), DE('No'))#"
	AllowChangeUse="#IIF((Column EQ 'Use') AND (NOT ss_OptionExists('Members Area')),DE('No'),DE('Yes'))#"				
   */						
	$Q_MailGroups = query("SELECT * FROM user_groups	WHERE ug_mailing_list = 1	ORDER BY ug_name");


$RelativeHere = $this->classDirectory."/";

$isSuperUser = new Request('Security.Authenticate',array(
	'Permission'	=>	'IsSuperUser',
	'LoginOnFail'	=>	false,
));
$isSuperUser = $isSuperUser->value;

$isTheDeployer = new Request('Security.Authenticate',array(
	'Permission'	=>	'IsDeployer',
	'LoginOnFail'	=>	false,
));
$isTheDeployer = $isTheDeployer->value;

$security = array();
$security['us_first_name'] = $ownerDetails['us_first_name'];
$security['us_last_name'] = $ownerDetails['us_last_name'];
$security['User'] = $_SESSION['User']['us_id'];
$security['RelativeHere'] = $RelativeHere;
$security['as_id'] = $id;
$security['IsSuperUser'] = $isSuperUser;
$security['IsDeployer'] = $isTheDeployer;
$security['as_owner_au_id'] = $owner;
$security['Q_UserGroups'] = $Q_UserGroups;
$security['Q_MailGroups'] = $Q_MailGroups;
$security['Asset'] = $asset;

print ($this->processTemplate('Security',$security));	

?>