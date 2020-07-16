<?php

if( getDefaultCurrencyCode() !=  acqraUnionpayPaymentGateway::getCurrencyHandled() )
{
        $_SESSION = NULL;
        die;
}

$fields = acqraUnionpayPaymentGateway::getHiddenFormFields( $Q_Order, $Q_Transaction, $totalPrice );

?>
<HTML>
<HEAD>
<TITLE>Off to the payment processor</TITLE>
</HEAD>
<SCRIPT language="javascript">

window.onload = function ()
{
document.forms.TheForm.submit();
}
</SCRIPT>
<BODY>
<br />
<br />
<FORM NAME="TheForm" ACTION="<?=acqraUnionpayPaymentGateway::getPOSTURL()?>" METHOD="POST">
<?php
	foreach( $fields as $name => $value)
		echo "<input type=\"hidden\" id=\"" . $name . "\" name=\"" . $name . "\" value=\"" . $value . "\"/>\n";
?>
<CENTER>
<!--INPUT TYPE="submit" VALUE="Continue"-->
</CENTER>
</FORM>
<?php
	echo acqraUnionpayPaymentGateway::postScript();
?>
</BODY>
</HTML>
