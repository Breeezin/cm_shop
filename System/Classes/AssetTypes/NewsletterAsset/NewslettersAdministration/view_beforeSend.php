<?php
	$this->display->title = "Send Newsletter";

	$data = array(
		'Q_UserGroups'	=>	$Q_NewsletterRecipientGroups,
		'UserCount'		=>	$NewsletterRecipientCount['Total'],
		'GroupCount'	=>	$Q_NewsletterRecipientGroups->numRows(),
		'Q_NewsletterArchiveAssets'	=> $Q_NewsletterArchiveAssets,
		'nl_id'	=> $this->ATTRIBUTES['nl_id'],
		'Subject'		=>	$Newsletter['nl_subject'],
        'WindowTitle' => $Newsletter['nl_subject'],
	);

	$this->useTemplate('BeforeSend',$data);

?>
