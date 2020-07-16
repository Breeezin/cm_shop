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
	$this->display->title = $this->ATTRIBUTES['BreadCrumbs'].' : View/Edit '.$this->singular;

	// get the form fields to display in the form
	$form = $this->form($errors);
?>
<?php 
	if (array_key_exists("Preview",$this->ATTRIBUTES)) {
?>
<SCRIPT language="JavaScript">
	function openPreviewWindow() {
		
		newWindow = window.open("index.php?act=Newsletter.Preview&nl_id=<?=$this->ATTRIBUTES['nl_id']?>", "NewsletterPreview<?=md5($GLOBALS['cfg']['currentServer']);?>", 'width=640,height=480,scrollbars=yes,menubar=yes,resizable=yes');
		if (newWindow) {
			document.write('<P>The newsletter preview will open in a new window.</P>');
			newWindow.focus();
		} else {
			document.write('<P>You appear to be running software that blocks pop-up windows. (e.g. Google Toolbar, Netscape)</P>');
			document.write('<P>Please try <A HREF="Javascript:void(0);" ONCLICK="newWindow = window.open(\'index.php?act=Newsletter.Preview&nl_id=<?=$this->ATTRIBUTES['nl_id']?>\', \'NewsletterPreview<?=md5($GLOBALS['cfg']['currentServer']);?>\', \'width=640,height=480,scrollbars=yes,menubar=yes,resizable=yes\'); if (newWindow) { newWindow.focus(); }">clicking here</A> to view the newsletter preview.</P>');
			document.write('<P>You may wish to add this site into your \'allow list\' to avoid seeing this message again.</P>');
		}
	}
	openPreviewWindow();
</SCRIPT>		
<?php
	}
?>

		
		

<?php print $errorText ?>
<FORM METHOD="POST" ACTION="<?php print basename($_SERVER['SCRIPT_NAME']).'?act='.$this->ATTRIBUTES['act'] ?>" NAME="adminForm">
	<P>
	<?php print $form ?>
	</P>
	<INPUT TYPE="HIDDEN" NAME="BreadCrumbs" VALUE="<?php print ss_HTMLEditFormat($this->ATTRIBUTES['BreadCrumbs']) ?>">
	<INPUT TYPE="HIDDEN" NAME="DoAction" VALUE="Submit">
	<INPUT TYPE="HIDDEN" NAME="BackURL" VALUE="<?php print ss_HTMLEditFormat($this->ATTRIBUTES['BackURL']) ?>">
	<INPUT TYPE="SUBMIT" NAME="DoAction" VALUE="Save">
	<INPUT TYPE="SUBMIT" NAME="Send" VALUE="Save/Send">
	<INPUT TYPE="SUBMIT" NAME="SendPreview" VALUE="Save/Send Preview Email" onclick="if (confirm('Are you sure you want to send a preview email to '+this.form.nl_sender_email.value+'? Click OK to send.')) return true; else return false;">
	<INPUT TYPE="SUBMIT" NAME="Preview" VALUE="Save/View Preview">
	<INPUT TYPE="HIDDEN" NAME="<?php print $this->tablePrimaryKey ?>" VALUE="<?php print $this->ATTRIBUTES[$this->tablePrimaryKey] ?>">
</FORM>	