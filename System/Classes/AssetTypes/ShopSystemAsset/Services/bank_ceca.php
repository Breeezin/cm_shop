<?php
die;		// disabled for now
// ceca redirection page...

$ACK_URL = "{$normalSite}$assetPath/Service/Completed/tr_id/{$this->ATTRIBUTES['tr_id']}/tr_token/{$this->ATTRIBUTES['tr_token']}/us_id/$usID";
$NACK_URL = "{$normalSite}Members";
$POST_URL = "https://pgw.ceca.es/cgi-bin/tpv";		//live
//$POST_URL = "http://tpv.ceca.es:8000/cgi-bin/tpv";		// test
//$key = '61521422';

$ChargeCurrency = '978';		// euros

$key = 15557165;
$MerchantID='102616042';
$AcquirerBIN='0000554008';
$TerminalID='00000003';

$messedUpPrice=(int)($totalPrice*100);

$somecrap = "".$key.$MerchantID.$AcquirerBIN.$TerminalID.$this->ATTRIBUTES['tr_id'].$messedUpPrice.$ChargeCurrency."2"."SHA1".$ACK_URL.$NACK_URL;
$hash = sha1( $somecrap );
?>
<HTML>
<HEAD>
<TITLE>P&aacute;gina de pago</TITLE>
</HEAD>
<SCRIPT language="javascript">

window.onload = function ()
{
<?php /*	document.forms.TheForm.submit(); */ ?>
}
</SCRIPT>
<BODY>
You are being redirected to our new payment processor (CECA).<br />
Please note that if you want to receive your order confirmation email, you will need to return back to our site.<br />
<br />
If you have any problems, contact your bank to authorize "Acmes Las Palmas" to charge your card,<br />
then, from your Members Page, you can pay for and finalize this order.<br />
These are not orders until you have successfully had your card charged.<br />
<br />
We CANNOT charge your card manually any more, please do not request this.<br />
At this time, we only accept VISA.<br />
<br />
<FORM NAME="TheForm" ACTION="<?=$POST_URL?>" METHOD="POST" ENCTYPE="application/xwww-form-urlencoded">
<INPUT NAME="MerchantID" TYPE=hidden VALUE='<?=$MerchantID?>'>
<INPUT NAME="AcquirerBIN" TYPE=hidden VALUE='<?=$AcquirerBIN?>'>
<INPUT NAME="TerminalID" TYPE=hidden VALUE='<?=$TerminalID?>'>
<INPUT NAME="URL_OK" TYPE=hidden VALUE='<?=$ACK_URL?>'>
<INPUT NAME="URL_NOK" TYPE=hidden VALUE='<?=$NACK_URL?>'>
<INPUT NAME="Firma" TYPE=hidden VALUE='<?=$hash?>'>
<INPUT NAME="Cifrado" TYPE=hidden VALUE='SHA1'>
<INPUT NAME="Idioma" TYPE=hidden VALUE='6'>
<INPUT NAME="Num_operacion" TYPE=hidden VALUE='<?=$this->ATTRIBUTES['tr_id']?>'>
<INPUT NAME="Importe" TYPE=hidden VALUE='<?=$messedUpPrice?>'>
<INPUT NAME="TipoMoneda" TYPE=hidden VALUE='<?=$ChargeCurrency?>'>
<INPUT NAME="Exponente" TYPE=hidden VALUE=2>
<INPUT NAME="Pago_soportado" TYPE=hidden VALUE=SSL>
<CENTER>
<INPUT TYPE="submit" VALUE="Continue">
</CENTER>
</FORM>
</BODY>
</HTML>
