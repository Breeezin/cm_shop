<?php

	$this->param('nl_id');
	$this->param('ArchiveNeID');
	
	$Newsletter = getRow("
		SELECT * FROM newsletters
		WHERE nl_id = ".safe($this->ATTRIBUTES['nl_id'])."
	");
	
	$Q_NewsletterRecipientGroups = query("
		SELECT * FROM newsletter_recipient_user_groups
		WHERE nrug_nl_id = ".safe($this->ATTRIBUTES['nl_id'])."
	");
	
	$userGroups = $Q_NewsletterRecipientGroups->columnValuesList('uug_ug_id',",","");
	
	$Q_NewsletterRecipients = query("
		SELECT DISTINCT us_id, us_first_name, us_last_name, us_email, us_html_email FROM users, user_user_groups
		WHERE us_id = uug_us_id
			AND uug_ug_id IN ({$userGroups})
			AND us_no_spam IS NULL
	");
	
	//$this->sendNewsletter($Newsletter,$Q_NewsletterRecipients,$this->ATTRIBUTES['ArchiveNeID'],'index.php?act=NewslettersAdministration.List');
	
?>
