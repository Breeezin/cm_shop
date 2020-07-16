<?php
$list_script = "competitor_list.php";
$edit_script = "competitor_edit.php";
$new_script = "competitor_new.php";
$delete_script = "";
$parent_script = "index.php";
$tablename = "competitor";
$database_interface = "db.php";
$key = "co_id";
$key_type = 'I';
$fields = array(
'co_name' => 'S',
'co_base_url' => 'S',
'co_currency' => 'S',
'co_active' => 'select 0, "false" UNION select 1, "true"',
'co_scrape' => 'select 0, "false" UNION select 1, "true"',
'co_session_call_url' => 'S',
);

$titles = array(
'co_name' => 'Site',
'co_base_url' => 'Home Page',
'co_currency' => 'Currency',
'co_active' => 'Active',
'co_scrape' => 'Scrape?',
'co_session_call_url' => 'Session Init URL',
);
?>
