<?php
// redirection page...

if( getDefaultCurrencyCode() != 'EUR' )
{
        $_SESSION = NULL;
        die;
}

$ACK_URL = "{$normalSite}$assetPath/Service/Completed/tr_id/{$this->ATTRIBUTES['tr_id']}/tr_token/{$this->ATTRIBUTES['tr_token']}/us_id/$usID";
$NACK_URL = "{$normalSite}Members";
$POST_URL = "https://tpv.4b.es/tpvv/teargral.exe";		// test

$reference = $this->ATTRIBUTES['tr_id'];
$MerchantID='PI09001408';
$language = 'en';

?>
<HTML>
<HEAD>
<TITLE>P&aacute;gina de pago</TITLE>
</HEAD>
<SCRIPT language="javascript">

window.onload = function ()
{
	document.forms.TheForm.submit();
}
</SCRIPT>
<BODY>
<br />
<FORM NAME="TheForm" ACTION="<?=$POST_URL?>" METHOD="POST" ENCTYPE="application/xwww-form-urlencoded">
<INPUT NAME="reference" TYPE=hidden VALUE='<?=$reference?>'>
<INPUT NAME="MerchantID" TYPE=hidden VALUE='<?=$MerchantID?>'>
<INPUT NAME="language" TYPE=hidden VALUE='<?=$language?>'>
<CENTER>
<INPUT TYPE="submit" VALUE="Continue">
</CENTER>
</FORM>
</BODY>
</HTML>
