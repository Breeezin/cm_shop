<?php
$list_script = "payment_gateway_options_list.php?order=po_pg_id";
$edit_script = "payment_gateway_options_edit.php";
$new_script = "payment_gateway_options_add.php";
$delete_script = "payment_gateway_options_delete.php";
$parent_script = "index.php";
$tablename = "payment_gateway_options";
$database_interface = "db.php";
$key = "po_id";
$key_type = 'I';
$fields = array(
'po_pg_id' => 'select pg_id, pg_name from payment_gateways',
'po_card_type' => 'select cct_id, cct_name from credit_card_types',
'po_restrict_from_country' => 'select cn_id, cn_name from countries',
'po_restrict_from_country2' => 'select cn_id, cn_name from countries',
'po_restrict_from_country3' => 'select cn_id, cn_name from countries',
'po_restrict_from_country4' => 'select cn_id, cn_name from countries',
'po_restrict_to_country' => 'select cn_id, cn_name from countries',
'po_restrict_to_person' => 'select 0, "false" union select 1, "true"',
'po_firstname_regex' => 'S',
'po_lastname_regex' => 'S',
'po_currency' => 'S',
'po_currency_name' => 'S',
'po_currency_image' => 'S',
'po_currency_symbol' => 'S',
'po_currency_symbol_before' => 'select 0, "false" union select 1, "true"',
'po_currency_precision' => 'I',
'po_option_discountx100' => 'I',
'po_charge_list' => 'I',
'po_active' => 'select 0, "false" union select 1, "true"',
'po_preference' => 'I',
'po_option_description' => 'T',
'po_site' => 'select si_id, si_name from configured_sites',
);

$titles = array(
'po_pg_id' => 'Gateway',
'po_restrict_from_country' => 'Not this Country',
'po_restrict_from_country2' => 'Not this Country either',
'po_restrict_from_country3' => 'Not this Country or this one',
'po_restrict_from_country4' => 'Not this Country or this one too',
'po_restrict_to_country' => 'Only this Country',
'po_restrict_to_person' => 'Only Available for Manual users',
'po_firstname_regex' => 'First Name regex Match',
'po_lastname_regex' => 'Last Name regex Match',
'po_currency' => 'Charging Currency Code',
'po_currency_name' => 'Currency Name',
'po_currency_image' => 'Currency Image',
'po_currency_symbol' => 'Currency Symbol',
'po_currency_symbol_before' => 'Symbol appears first',
'po_currency_precision' => 'Decimal Places',
'po_option_discountx100' => 'Currency Discount (x100)',
'po_charge_list' => 'Chargelist',
'po_active' => 'Gateway Active',
'po_preference' => 'Preference (low 1st)',
'po_card_type' => 'Credit Card Type',
'po_option_description' => 'Option Text',
'po_site' => 'Site',
);
?>
