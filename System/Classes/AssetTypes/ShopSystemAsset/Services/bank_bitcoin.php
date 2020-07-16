<?php
if( getDefaultCurrencyCode() != 'BTC' )
{
	$_SESSION = NULL;
	die;
}
ob_start();
eval( '; ?>'.$payrow['pg_customer_template'] );
$f = ob_get_contents();
ob_end_clean();
echo $f;
echo "<br /><a href='".rawurldecode($backURL)."'>Continue</a>";

// fire off an email...
if (file_exists(expandPath("Custom/ContentStore/Templates/{$GLOBALS['cfg']['currentSiteFolder']}".'ShopSystemAsset/sty_invoice.css')))
	$stylesheet = "Custom/ContentStore/Templates/{$GLOBALS['cfg']['currentSiteFolder']}".'ShopSystemAsset/sty_invoice.css';
else
	$stylesheet = 'System/Classes/AssetTypes/ShopSystemAsset/ShopSystem_OrdersAdministration/Templates/sty_invoice.css';

$emailResult = new Request('Email.Send',array(
	'from'	=>	$GLOBALS['cfg']['EmailAddress'],
	'to'	=>	$Q_Order['or_purchaser_email'],
	'subject'	=>	"Payment instructions for your order at {$GLOBALS['cfg']['website_name']}",
	'html'	=>	$f,
	'css'	=>	$stylesheet,
	'templateFolder'	=>	$Q_Order['or_site_folder'],
));

?>
