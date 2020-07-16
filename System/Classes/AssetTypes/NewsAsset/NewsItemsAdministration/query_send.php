<?php

	$this->param('nei_id');
	$this->param('as_id');
	$this->param('BackURL');
	
	$Q_RecipientGroups = query("
		SELECT * FROM news_items_user_groups
		WHERE niu_nei_id = ".safe($this->ATTRIBUTES['nei_id'])."
	");
	
	if ($Q_RecipientGroups->numRows() > 0) {
		$userGroups = $Q_RecipientGroups->columnValuesList('neu_ug_id',",","");
	} else {
		$userGroups = '-1';	
	}
	
	$Q_Recipients = query("
		SELECT DISTINCT us_id, us_first_name, us_last_name, us_email, us_html_email FROM users, user_user_groups
		WHERE us_id = uug_us_id
			AND uug_ug_id IN ({$userGroups})
			AND us_no_spam IS NULL
	");
	
	//$this->sendNewsletter($Newsletter,$Q_NewsletterRecipients,$this->ATTRIBUTES['ArchiveNeID'],'index.php?act=NewslettersAdministration.List');
	
?>
