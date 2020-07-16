<?php 
/*	$data = array();
	$data['or_id'] = $Q_Order['or_id'];
	$data['or_recorded'] = $Q_Order['or_recorded'];
	$data['or_purchaser_firstname'] = $Q_Order['or_purchaser_firstname'];
	$data['or_purchaser_lastname'] = $Q_Order['or_purchaser_lastname'];	
	$data['tr_reference'] = $Q_Order['tr_reference'];	
		   
	$details = unserialize($Q_Order['or_details']);
	
	
	$shippingDetails = unserialize($Q_Order['or_shipping_details']);
	$data['ShippingDetailsHTML'] = '';
	foreach($shippingDetails['ShippingDetails'] as $aValue) {
		$data['ShippingDetailsHTML'] .= $aValue."<BR>";
	}
	
	$data['PurchaserDetailsHTML'] = '';
	foreach($shippingDetails['PurchaserDetails'] as $aValue) {
		$data['PurchaserDetailsHTML'] .= $aValue."<BR>";
	}
	
	ss_paramKey($details, 'BasketHTML', '');
	ss_paramKey($details, 'GiftMessage', '');		
	$data['BasketHTML'] = $details['BasketHTML'];
	$data['GiftMessage'] = $details['GiftMessage'];
	$data['ThankYouNote'] = $shopSetting['AST_SHOPSYSTEM_THANKYOUNOTE'];	
	
	
	$data['LogoHTML'] = $GLOBALS['cfg']['website_name'];
	if(file_exists(expandPath('Custom/ContentStore/Layouts/Images/Shop/invoiceLogo.gif'))) {
		$data['LogoHTML'] = "<img src='Custom/ContentStore/Layouts/Images/Shop/invoiceLogo.gif'>";
	}
	
	
	$this->useTemplate('Invoice', $data);*/
	if (!strlen($Booking['tr_charge_total'])) {
	
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
	
?>


<?php print $errorText ?>
<table cellspacing="0" cellpadding="0" class="noPrint">
<FORM enctype="MULTIPART/FORM-DATA" METHOD="POST" ACTION="<?php print basename($_SERVER['SCRIPT_NAME']).'?act='.$this->ATTRIBUTES['act'] ?>" NAME="adminForm" ONSUBMIT="processForm()">
	<script language="javascript">
		var extraProcesses = new Array();
		function processForm() {
			for (var x = 0; x < extraProcesses.length; x++) {
				extraProcesses[x]();
			}
		}
	</script>
	<tr>
	<td>
		<strong>Please enter the payment amount:</strong> <?php print $form ?>
		<INPUT TYPE="SUBMIT" NAME="DoAction" VALUE="Submit">
	</tr>
	<br />
	<INPUT TYPE="HIDDEN" NAME="BreadCrumbs" VALUE="<?php print ss_HTMLEditFormat($this->ATTRIBUTES['BreadCrumbs']) ?>">
	<INPUT TYPE="HIDDEN" NAME="DoAction" VALUE="Submit">
	<INPUT TYPE="HIDDEN" NAME="<?php print $this->fieldSet->tablePrimaryKey ?>" VALUE="<?php print $this->ATTRIBUTES[$this->fieldSet->tablePrimaryKey] ?>">
	<INPUT TYPE="HIDDEN" NAME="bo_id" VALUE="<?php print $this->ATTRIBUTES['bo_id'] ?>">
	<INPUT TYPE="HIDDEN" NAME="as_id" VALUE="<?php print $Booking['bo_as_id'] ?>">
</FORM>	
			
</table>
<?
	
	}	
	
	if (!strlen($Booking['bo_paid']) and strlen($Booking['tr_total'])) {
		$Transaction = $Booking;
		$res = new Request('Asset.PathFromID',array(
			'as_id'	=>	$Booking['bo_as_id'],
		));
		$assetPath = $res->value;
		print('<p class="noPrint"><strong>Secure Payment Link:</strong><br />');
		print(ss_withTrailingSlash($GLOBALS['cfg']['secure_server'])."index.php?act=WebPay.ByCreditCard&tr_id={$Transaction['tr_id']}&tr_token={$Transaction['tr_token']}&BackURL=".ss_URLEncodedFormat(ss_withTrailingSlash($GLOBALS['cfg']['plaintext_server']).ss_withoutPreceedingSlash($assetPath)."?Service=ThankYou").'</p>');
	}
	print("<div class=\"noPrint\"><input type=\"button\" name=\"print\" value=\"Print\" onclick=\"window.print();\"></div>");
	print($Booking['bo_details']);

	$this->display->title = 'Below are details for booking: '.$Booking['bo_tr_id'];
	$this->display->layout = "AdministrationPrint";
?>