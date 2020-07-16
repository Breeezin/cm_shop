<?php

if (ss_OptionExists('Advanced Data Collection')){
	if (array_key_exists('Do_Service',$this->ATTRIBUTES)) {
		$temp = new Request("Security.Sudo",array('Action'=>'start'));

		$DoCoAdmin->loadFieldValuesFromForm($this->ATTRIBUTES);
		// Validate and then write to the database
		$errors = $DoCoAdmin->validate();
		if (!count($errors)) {
            $errors = $DoCoAdmin->insert();
		}
		$temp = new Request("Security.Sudo",array('Action'=>'stop'));

        $emailAddress = $GLOBALS['cfg']['EmailAddress'];
        if (isset($asset->cereal[$this->fieldPrefix."EMAIL_RECIPIENT"])){
            $emailAddress = $asset->cereal[$this->fieldPrefix."EMAIL_RECIPIENT"];
        }

		if (count($errors) == 0) {
            $sendNotification = new Request("Email.Send",array(
				'to'=> $emailAddress,
				'from'=>'system@acmerockets.com',
				'subject'=>'New Event Posting on '. $GLOBALS['cfg']['currentSite'],
				'html'=>'<html><body><p>A new event has been posted on your website.</p><pre>--------------------------------------------------------------------------------<BR>Automated Message<BR>--------------------------------------------------------------------------------</pre></body></html>',
				'text'=>"A new event has been posted on your website.\n -----Automated Message----",
			));
            locationRelative("$assetPath/Service/Show/New/1");
		}
	}
}
?>
