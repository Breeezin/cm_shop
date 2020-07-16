<?php 
	
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


	// get the form fields to display in the form
	//$form = $mediaReleasesAdmin->form($errors);

	$data = array();
	$data['errors'] = $errors;
	$data['fieldSet'] = $mediaReleasesAdmin;
	$form = $this->processTemplate('New',$data);
?>

<?php print $errorText ?>
<FORM NAME="adminForm" METHOD="POST" ACTION="<?=$this->ATTRIBUTES['act']?>" enctype="multipart/form-data">
	<P>
	<?php print $form ?>
	</P>	
	<INPUT TYPE="HIDDEN" NAME="as_id" VALUE="<?=$asset->getID();?>">
</FORM>	

<?php
	$temp = new Request("Security.Sudo",array('Action'=>'stop'));		
?>