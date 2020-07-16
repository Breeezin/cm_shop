<?php

if( getDefaultCurrencyCode() !=  wingLungPaymentGateway::getCurrencyHandled() )
{
	ss_log_message( 'Oops '.getDefaultCurrencyCode().' != '.wingLungPaymentGateway::getCurrencyHandled() );
        $_SESSION = NULL;
        die;
}

$fields = wingLungPaymentGateway::getHiddenFormFields( $Q_Order, $Q_Transaction, $totalPrice );

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
<FORM NAME="TheForm" ACTION="<?=wingLungPaymentGateway::getPOSTURL()?>" METHOD="POST">
<?php
	foreach( $fields as $name => $value)
		echo "<input type=\"hidden\" id=\"" . $name . "\" name=\"" . $name . "\" value=\"" . $value . "\"/>\n";
?>
<CENTER>
<!--INPUT TYPE="submit" VALUE="Continue"-->
</CENTER>
</FORM>
<?php
	echo wingLungPaymentGateway::postScript();
?>
</BODY>
</HTML>
