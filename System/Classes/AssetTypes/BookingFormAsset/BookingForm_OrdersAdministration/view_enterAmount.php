<?php
	if ($close) {	
		print ("<script language=\"Javascript\">window.opener.location.href = window.opener.location.href;window.close();</script>");
	} else {
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
	
	// Check for errors
	$data = array();
	$data['errors'] = $errors;
	$data['fieldSet'] = $this->fieldSet;

	$form = $this->processTemplate('EnterAmount',$data);

	$this->display->title = 'Please enter amount to charge customer:';

?>
<?php print $errorText ?><FORM enctype="MULTIPART/FORM-DATA" METHOD="POST" ACTION="<?php print basename($_SERVER['SCRIPT_NAME']).'?act='.$this->ATTRIBUTES['act'] ?>" NAME="adminForm" ONSUBMIT="processForm()">
	<script language="javascript">
		var extraProcesses = new Array();
		function processForm() {
			for (var x = 0; x < extraProcesses.length; x++) {
				extraProcesses[x]();
			}
		}
	</script>
	<?php print $form ?>
	<br />
	<INPUT TYPE="HIDDEN" NAME="BreadCrumbs" VALUE="<?php print ss_HTMLEditFormat($this->ATTRIBUTES['BreadCrumbs']) ?>">
	<INPUT TYPE="HIDDEN" NAME="DoAction" VALUE="Submit">
	
	<INPUT TYPE="SUBMIT" NAME="DoAction" VALUE="Submit">
	<INPUT TYPE="RESET" NAME="RESET" VALUE="Reset">
	<INPUT TYPE="HIDDEN" NAME="<?php print $this->fieldSet->tablePrimaryKey ?>" VALUE="<?php print $this->ATTRIBUTES[$this->fieldSet->tablePrimaryKey] ?>">
</FORM>			
<? } ?>