<?php
$list_script = "payment_gateways_list.php";
$edit_script = "payment_gateways_edit.php";
$new_script = "payment_gateways_add.php";
$delete_script = "payment_gateways_delete.php";
$parent_script = "index.php";
$tablename = "payment_gateways";
$database_interface = "db.php";
$key = "pg_id";
$key_type = 'I';
$fields = array(
'pg_name' => 'S',
'pg_description' => 'SN',
'pg_accumulation' => 'IN',
'pg_limit' => 'IN',
'pg_minimum_orders' => 'IN',
'pg_minimum_total' => 'IN',
'pg_minimum_penalty' => 'IN',
'pg_script' => 'S',
'pg_reserve_stock' => 'select 0, "false" union select 1, "true"',
'pg_image' => 'S',
'pg_charging_name' => 'S',
'pg_order_max' => 'IN',
'pg_skim' => 'I',
'pg_skim_fixed' => 'I',
'pg_customer_template' => 'T',
'pg_can_chargeback' => 'select 0, "false" union select 1, "true"',
'pg_rf_id' => 'select rf_id, rf_name from reset_frequency',
'pg_object' => 'S',
'pg_can_recheckout' => 'select 0, "false" union select 1, "true"',
);

$titles = array(
'pg_name' => 'Name',
'pg_description' => 'Description',
'pg_accumulation' => 'Current Daily Accumulation',
'pg_limit' => 'Maxiumum Daily Accumulation',
'pg_minimum_orders' => 'Customer min Orders',
'pg_minimum_total' => 'Minumum Order Amount',
'pg_minimum_penalty' => 'Penalty for less than Minimum Order Amount',
'pg_script' => 'Script Name',
'pg_reserve_stock' => 'Reserve Stock after order',
'pg_image' => 'Image',
'pg_charging_name' => 'Charge Name',
'pg_order_max' => 'Maximum Order Amount',
'pg_skim' => 'Processor Skim Percent',
'pg_skim_fixed' => 'Processor Skim Fixed Charge',
'pg_customer_template' => 'Customer Template',
'pg_can_chargeback' => 'Customer Can Chargeback',
'pg_rf_id' => 'Accumulation Reset Frequency',
'pg_object' => 'PHP Class',
'pg_can_recheckout' => 'Customer Can Re-Checkout',
);
?>
