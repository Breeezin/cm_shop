<?php
ss_log_message( "bank_litecoin.php" );
if( $chargeCurrency['CurrencyCode'] != 'LTC' )
{
	$_SESSION = NULL;
	ss_log_message( "Charging currency invalid - {$chargeCurrency['CurrencyCode']}" );
	die;
}

// check to see if this person has more than 3 outstanding litecoin orders, if so, reject this.

$pending_count = getField( "select count(*) from shopsystem_orders join transactions on tr_id = or_tr_id join litecoin_addresses on tr_id = la_tr_id
								where or_archive_year IS NULL and or_us_id = ".ss_getUserID()." and la_paid = false and or_cancelled is not null and or_deleted > 0" );

ss_log_message( "Pending count = $pending_count" );
if( $pending_count > 3 )
{
	echo "You have too many outstanding litecoin orders, please pay for one or more of them before proceeding";
	ss_log_message( "You have too many outstanding litecoin orders, please pay for one or more of them before proceeding" );
	die;
}

// generate a new address for the default account acmerockets

require_once '/var/www/chroot/acmerockets/System/Libraries/jsonrpcphp/includes/jsonRPCClient.php';
 
if( $litecoin = new jsonRPCClient('http://litecoinusername:litecoinpassword@localhost:9332/') )
{
	$current_account = "acmerockets";
	$new_destination_address = $litecoin->getnewaddress($current_account)."\n";
	if( !query( "insert into litecoin_addresses (la_tr_id, la_address, la_account) values ({$Q_Order['or_tr_id']}, '$new_destination_address', '$current_account' )" ) )
	{
		ss_log_message( "query failed > insert into litecoin_addresses (la_tr_id, la_address, la_account) values ({$Q_Order['or_tr_id']}, '$new_destination_address', '$current_account' )" );
		echo "Something went wrong, please contact customer service";
		die;
	}
}
else
{
	ss_log_message( "unable to connect to litecoind on localhost" );
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
