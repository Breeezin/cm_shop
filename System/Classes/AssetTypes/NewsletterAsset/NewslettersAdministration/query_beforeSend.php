<?php
	requireOnceClass('FieldSet');

	$this->param('nl_id');
	
	$Newsletter = getRow("
		SELECT * FROM newsletters
		WHERE nl_id = ".safe($this->ATTRIBUTES['nl_id'])."
	");
	
	$Q_NewsletterRecipientGroups = query("
		SELECT * FROM newsletter_recipient_user_groups, user_groups
		WHERE nrug_nl_id = ".safe($this->ATTRIBUTES['nl_id'])."
			AND nrug_ug_id = ug_id
	");
	
	$userGroups = $Q_NewsletterRecipientGroups->columnValuesList('uug_ug_id',",","");
	$userGroupNames = $Q_NewsletterRecipientGroups->columnValuesList('ug_name',", ","");
	
	$NewsletterRecipientCount = getRow("
		SELECT COUNT(DISTINCT uug_us_id) AS Total FROM user_user_groups, users
		WHERE uug_ug_id IN ({$userGroups})
			AND us_id = uug_us_id
			AND us_no_spam IS NULL
	");

	$Q_NewsletterArchiveAssets = query("
		SELECT * FROM assets
		WHERE as_type LIKE 'NewsletterArchive'
	");
	
?>
