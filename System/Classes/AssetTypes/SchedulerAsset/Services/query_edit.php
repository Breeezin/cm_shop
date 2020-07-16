<?php
    requireOnceClass("SchedulerAdministration");
	$schedularAdmin = new SchedulerAdministration($assetID);
    
    // get the index of the current backstack
	$this->param('EvID');
	$this->param('br','');
	$this->param('backurl',$_SESSION['BackStack']->getIndexedURL($this->ATTRIBUTES['br'],'/'.$assetPath));
    $backURL = $this->ATTRIBUTES['backurl'];
	
	$schedularAdmin->ATTRIBUTES = $this->ATTRIBUTES;
	$schedularAdmin->primaryKey = $this->ATTRIBUTES['EvID'];	
	$errors = array();
	if (array_key_exists('Do_Service',$this->ATTRIBUTES)) {	
		// Validate and then write to the database		     
		$temp = new Request("Security.Sudo",array('Action'=>'start'));
		$schedularAdmin->loadFieldValuesFromForm($this->ATTRIBUTES);			
		$errors = $schedularAdmin->update();				
		$temp = new Request("Security.Sudo",array('Action'=>'stop'));
		if (count($errors) == 0) {
			if ($backURL) {
				locationRelative($backURL);								
			} else {					
	 			locationRelative($assetPath);								
			}
		} 
	}
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

	// get the form fields to display in the form
	$temp = new Request("Security.Sudo",array('Action'=>'start'));
	$form = $schedularAdmin->form($errors);
	$temp = new Request("Security.Sudo",array('Action'=>'stop'));
	if (array_key_exists('NewBackURL', $this->atts)) {
		$form .= '<INPUT TYPE="hidden" NAME="NewBackURL" VALUE="'.$this->atts['NewBackURL'].'">';
	}

?>

<?php print $errorText;?>
<FORM NAME="adminForm" METHOD="POST" ACTION="<?=$assetPath?>/Service/Edit/Do_Service/Yes">
	<P>
	<?php print $form ?>
	</P>	
	<INPUT TYPE="submit" NAME="DoAction" VALUE="Submit">				
	<INPUT TYPE="hidden" NAME="EvID" VALUE="<?=$this->ATTRIBUTES['EvID']?>">				
	<INPUT TYPE="hidden" NAME="br" VALUE="<?=$this->ATTRIBUTES['br']?>">				
	<INPUT TYPE="hidden" NAME="backurl" VALUE="<?=$this->ATTRIBUTES['backurl']?>">				
	<INPUT TYPE="RESET" NAME="RESET" VALUE="Reset">
</FORM>	