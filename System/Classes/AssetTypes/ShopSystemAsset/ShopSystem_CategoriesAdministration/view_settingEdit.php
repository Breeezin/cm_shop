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
	$this->display->title = $this->ATTRIBUTES['BreadCrumbs'].' : Product Settings'.$this->singular;
	
?>

<?php print $errorText ?>
<FORM METHOD="POST" ACTION="<?php print basename($_SERVER['SCRIPT_NAME']).'?act='.$this->ATTRIBUTES['act'] ?>" NAME="adminForm">
	<P>
	<?php 
		if ($attSetting != null) {
	?>
	Attributes Setting: <?=$attSetting->display(false, 'adminForm')?><BR>
	<!------Apply To Sub Categories : <input type="checkbox" name="AttApplyToSubs" value="yes">---->
	<?php 
		}
	?>
	</p>
	<p>
	<?php 
		if ($optionSetting != null) {
	?>
		Options Setting: <?=$optionSetting->display(false, 'adminForm')?><BR>
		<!-----Apply To Sub Categories : <input type="checkbox" name="OpApplyToSubs" value="yes">------->
	<?php 
		}
	?>
	</P>
	<INPUT TYPE="HIDDEN" NAME="BreadCrumbs" VALUE="<?php print ss_HTMLEditFormat($this->ATTRIBUTES['BreadCrumbs']) ?>">
	<INPUT TYPE="HIDDEN" NAME="DoAction" VALUE="Submit">
	<INPUT TYPE="HIDDEN" NAME="BackURL" VALUE="<?php print ss_HTMLEditFormat($this->ATTRIBUTES['BackURL']) ?>">
	<INPUT TYPE="SUBMIT" NAME="DoAction" VALUE="Submit">
	<INPUT TYPE="RESET" NAME="RESET" VALUE="Reset">
	<INPUT TYPE="HIDDEN" NAME="<?php print $this->tablePrimaryKey ?>" VALUE="<?php print $this->ATTRIBUTES[$this->tablePrimaryKey] ?>">
	<INPUT TYPE="HIDDEN" NAME="as_id" VALUE="<?php print $this->assetLink ?>">
</FORM>		