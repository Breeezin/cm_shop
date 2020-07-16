<?php
if (ss_OptionExists('Advanced Data Collection')){  //to prevent 'accidental' postings
    $loggedIn = true;
    if (ss_OptionExists('Advanced Data Collection') == 'Member'){
        //must be a member to post, check user is logged in
        $loggedIn = false;
        foreach ($_SESSION['User']['user_groups'] as $group){
        	if ($group > 0)
        	$loggedIn = true;
        	break;
        }
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

        $newContent = ss_parseText($asset->cereal[$this->fieldPrefix.'NEW_CONTENT']);
    	// get the form fields to display in the form
    	$form = $DoCoAdmin->form($errors);
    	if (array_key_exists('NewBackURL', $this->atts)) {
    		$form .= '<INPUT TYPE="hidden" NAME="NewBackURL" VALUE="'.$this->atts['NewBackURL'].'">';
    	}
    print $newContent;
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
        print('Sorry, you must be signed in as a member to contribute to the calendar.');
    }
}
?>
