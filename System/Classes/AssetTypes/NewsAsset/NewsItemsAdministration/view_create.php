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

	// set the title for the page
	$this->display->title = $this->ATTRIBUTES['BreadCrumbs'].' : New '.$this->singular;

	// get the form fields to display in the form
	if (array_key_exists('PushValues',$this->ATTRIBUTES)) {
		$this->ATTRIBUTES['DoAction'] = 1;
		$errors = array('~_~'=>'blah');
	}
	$form = $this->form($errors);
	if (array_key_exists('PushValues',$this->ATTRIBUTES)) {
		$errors = array();
	}
?>

<?php print $errorText ?>
<FORM NAME="adminForm" enctype="MULTIPART/FORM-DATA" METHOD="POST" ACTION="<?php print basename($_SERVER['SCRIPT_NAME']).'?act='.$this->ATTRIBUTES['act'] ?>" ONSUBMIT="processForm()">
	<script language="javascript">
		var extraProcesses = new Array();
		function processForm() {
			for (var x = 0; x < extraProcesses.length; x++) {
				extraProcesses[x]();
			}
		}
	</script>	<P>
	<?php print $form ?>
	</P>
	<INPUT TYPE="HIDDEN" NAME="BreadCrumbs" VALUE="<?php print ss_HTMLEditFormat($this->ATTRIBUTES['BreadCrumbs']) ?>">
	<INPUT TYPE="HIDDEN" NAME="DoAction" VALUE="Submit">
	<INPUT TYPE="HIDDEN" NAME="BackURL" VALUE="<?php print ss_HTMLEditFormat($this->ATTRIBUTES['BackURL']) ?>">
	<INPUT TYPE="SUBMIT" NAME="DoAction" VALUE="Save">
	<INPUT TYPE="SUBMIT" NAME="Send" VALUE="Save/Send">
	<INPUT TYPE="HIDDEN" NAME="as_id" VALUE="<?php print $this->assetLink ?>">	
</FORM>			