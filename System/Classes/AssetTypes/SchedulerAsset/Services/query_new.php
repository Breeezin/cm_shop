<?php
    requireOnceClass("SchedulerAdministration");
    $DoCoAdmin = new SchedulerAdministration($assetID);
	$this->param('br','');
	$this->param('backurl',$_SESSION['BackStack']->getIndexedURL($this->ATTRIBUTES['br'],'/'.$assetPath));

    $errors = array();

    $this->param('date',date('Y-m-d H:i:s'));
    $this->param('starttime',date('Y-m-d H:i:s', strtotime($this->ATTRIBUTES['date'])) );  
    $this->param('endtime',date('Y-m-d H:i:s', strtotime($this->ATTRIBUTES['date'])) );  
    $this->param('EvStart',$this->ATTRIBUTES['starttime']);
    $this->param('EvEnd',$this->ATTRIBUTES['endtime']);
    $this->param('EvType',3);
    $this->param('user',$_SESSION['User']['us_id']);

    $this->ATTRIBUTES['act'] = $assetPath."/Service/New/Do_Service/Yes";
    
    $DoCoAdmin->ATTRIBUTES = $this->ATTRIBUTES;
    
    // ss_DumpVar($DoCoAdmin);
    $DoCoAdmin->fields['EvUsers']->defaultValue = Array($this->ATTRIBUTES['user']);
    $DoCoAdmin->fields['EvStart']->defaultValue = ($this->ATTRIBUTES['EvStart']);
    $DoCoAdmin->fields['EvEnd']->defaultValue = ($this->ATTRIBUTES['EvEnd']);
    
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
            /*
            $sendNotification = new Request("Email.Send",array(
				'to'=> $emailAddress,
				'from'=>'system@acmerockets.com',
				'subject'=>'New Event Posting on '. $GLOBALS['cfg']['currentSite'],
				'html'=>'<html><body><p>A new event has been posted on your website.</p><pre>--------------------------------------------------------------------------------<BR>Automated Message<BR>--------------------------------------------------------------------------------</pre></body></html>',
				'text'=>"A new event has been posted on your website.\n -----Automated Message----",
			));
            */
            locationRelative($this->ATTRIBUTES['backurl']);
		}
       // ss_DumpVar($DoCoAdmin);        
	}        
     

    $loggedIn = false;
    foreach ($_SESSION['User']['user_groups'] as $group){
        if ($group > 0)
        $loggedIn = true;
        break;
    }
    if ($loggedIn) {
        $temp = new Request("Security.Sudo",array('Action'=>'start'));

    	// Check if any errors occured
    	if (count($errors) != 0) {
    		$errorText = '<P><TABLE WIDTH="95%" BORDER="0" ALIGN="CENTER"><TR><TD CLASS="entryErrors">
    			Errors were detected in the data you entered, please correct the
    			following issues and re-submit.  Nothing has been changed or added
    			to the database at this point.<UL>';
    		foreach ($errors as $messages) {
    			foreach ($messages as $message) {
    				$errorText .= "<LI>$message</LI>";
    			}
    		}
    		$errorText .= '</UL></TD></TR></TABLE></P>';
    	} else {
    		$errorText = '';
    	}

        // $newContent = ss_parseText($asset->cereal[$this->fieldPrefix.'NEW_CONTENT']);
    	// get the form fields to display in the form
    	$form = $DoCoAdmin->form($errors);

    	if (array_key_exists('br', $this->atts)) {
    		$form .= '<INPUT TYPE="hidden" NAME="br" VALUE="'.$this->atts['br'].'">';
    	}

        print $errorText;
        ?>
        <FORM NAME="adminForm" METHOD="POST" ACTION="<?=$this->ATTRIBUTES['act']?>">
    	    <P>
    	    <?php print $form ?>
    	    </P>
    	    <INPUT TYPE="submit" NAME="DoAction" VALUE="Submit">
    	    <INPUT TYPE="RESET" NAME="RESET" VALUE="Reset">

        </FORM>

        <?php
    	    $temp = new Request("Security.Sudo",array('Action'=>'stop'));
    } else {
        print('Sorry, you must be signed in to use this.');
    }
  
?>
