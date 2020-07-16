<?php

	$this->param('nl_id');
	
	$Newsletter = getRow("
		SELECT * FROM newsletters
		WHERE nl_id = ".safe($this->ATTRIBUTES['nl_id'])."
	");

	// Construct some fake information
	$Q_NewsletterRecipients = new FakeQuery(array(
		"us_id","us_first_name","us_last_name","us_email","us_html_email"
	));
	$Q_NewsletterRecipients->addRow(array("-2","<<First Name Will Go Here>>","<<Last Name Will Go Here>>",$Newsletter['nl_sender_email'],"1"));

	$this->sendOldNewsletter($Newsletter,$Q_NewsletterRecipients,null,'index.php?act=NewslettersAdministration.Edit&nl_id='.$this->ATTRIBUTES['nl_id']."&BackURL=".ss_URLEncodedFormat($this->ATTRIBUTES['BackURL'])."&BreadCrumbs=".ss_URLEncodedFormat($this->ATTRIBUTES['BreadCrumbs']));
	
?>