<?php 
		

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

	//ss_DumpVarDie($userAdmin,"aftereee");
	// get the form fields to display in the form
	$temp = new Request("Security.Sudo",array('Action'=>'start'));
	$form = $userAdmin->form($errors);
	$temp = new Request("Security.Sudo",array('Action'=>'stop'));
	

?>

<?php print $errorText;?>
<FORM NAME="adminForm" METHOD="POST" ACTION="<?=$assetPath?>/Service/Edit/Do_Service/Yes">
	<P>
	<?php print $form ?>
	</P>	
	<INPUT TYPE="hidden" name="DoAction" value="yes">
	<INPUT TYPE="submit" NAME="Submit" VALUE="Submit">		
	<INPUT TYPE="RESET" NAME="RESET" VALUE="Reset">
</FORM>	