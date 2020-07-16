<?php
	$success = false;
	$errors = array();
	if (array_key_exists("DoAction",$asset->ATTRIBUTES) ) {
        // ss_DumpVar($fieldSet);
        // ss_DumpVarDie($asset);
		$fieldSet->loadFieldValuesFromForm($asset->ATTRIBUTES);

		$errors = $fieldSet->validate();

        if (count($errors) == 0) {
             $success = true;

            /*
            Save to database
            */
            $fieldSet->insert();


            $Q_Update = query("	UPDATE {$fieldSet->tableName}
                    SET efs_timestamp = NOW()
            		WHERE {$fieldSet->tablePrimaryKey} = {$fieldSet->primaryKey}
            ");
		if ($asset->cereal[$this->fieldPrefix."NOTIFICATION_EMAIL"] == 1) {
            $email = ss_HTMLEditFormat($asset->cereal[$this->fieldPrefix.'NOTIFICATION_EMAIL_ADDRESS']);

            if (strlen($email) == 0)
                $email = $GLOBALS['cfg']['EmailAddress'];

			$sendNotification = new Request("Email.Send",array(
				'to'=> $email,
				'from'=>'system@acmerockets.com',
				'subject'=>'Survey Completed',
				'html'=>'<html><body><p>Someone has completed the survey on your website.</p>
                <pre>--------------------------------------------------------------------------------
                                Automated Message
                --------------------------------------------------------------------------------</pre>
                </body></html>',
						'text'=>'Someone has completed the survey on your <a href="'.$GLOBALS['cfg']['currentServer'].'">website</a>.\n \n -----Automated Message----',
					));
            }



		}
	} else {
	    $fieldSet->loadFieldValuesFromForm($asset->ATTRIBUTES,false);

	}
?>
