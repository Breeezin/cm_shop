<?php

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
/*	$data['fieldSet'] = $this->fieldSet;

	$form = $this->processTemplate('EnterDocketNumber',$data);
	*/
	$this->display->title = $this->ATTRIBUTES['BreadCrumbs']." : Send Email";

?>
<?=$errorText;?>
<table>
<tr>
	<td><strong>Order #</strong></td><td><?=$Q_Order['or_tr_id']?></td>
</tr>
<tr>
	<td><strong>Purchaser</strong></td><td><? print ss_htmlEditFormat($Q_Order['or_purchaser_firstname']); ?> <? print ss_htmlEditFormat($Q_Order['or_purchaser_lastname']); ?> (<a href="mail:<?=$Q_Order['or_purchaser_email']?>"><?=$Q_Order['or_purchaser_email']?></a>)</td>
</tr>
</table>
<form method="post" action="index.php?act=<?=$this->ATTRIBUTES['act']?>&DoAction=1">
	<script language="javascript">
		var extraProcesses = new Array();
		function processForm() {
			for (var x = 0; x < extraProcesses.length; x++) {
				extraProcesses[x]();
			}
		}
	</script>
	<textarea name="SpecialNote" rows="10" cols="40" style="width:100%"></textarea>

<p><input type="Submit" name="Submit" value="Send Email">
	<input type="button" name="Cancel" value="Cancel" onclick="document.location='<?=ss_jsStringFormat($this->ATTRIBUTES['BackURL']);?>';">
	<INPUT TYPE="HIDDEN" NAME="BreadCrumbs" VALUE="<?php print ss_HTMLEditFormat($this->ATTRIBUTES['BreadCrumbs']) ?>">
<input type="hidden" name="BackURL" value="<?=ss_HTMLEditFormat($this->ATTRIBUTES['BackURL']);?>">
<input type="hidden" name="or_id" value="<?=ss_HTMLEditFormat($this->ATTRIBUTES['or_id']);?>">
</p>
</form>
