<?php
$list_script = "scraper_list.php";
$edit_script = "scraper_edit.php";
$new_script = "scraper_new.php";
$delete_script = "";
$parent_script = "index.php";
$tablename = "competitor_scraper";
$database_interface = "db.php";
$key = "cs_id";
$key_type = 'I';
$fields = array(
'cs_pr_id' => 'select pr_id, pr_name from shopsystem_products where pr_offline IS NULL and pr_ve_id = 2',
'cs_co_id' => 'select co_id, co_name from competitor',
'cs_url' => 'S',
'cs_start_pattern' => 'S',
'cs_end_pattern' => 'S',
'cs_start_delimiter' => 'S',
'cs_end_delimiter' => 'S',
'cs_start_delimiter2' => 'S',
'cs_end_delimiter2' => 'S',
'cs_out_of_stock' => 'S',
'cs_instance' => 'I',
'cs_active' => 'select 0, "false" UNION select 1, "true"',
);

$titles = array(
'cs_pr_id' => 'Product',
'cs_co_id' => 'Competitor',
'cs_url' => 'Product URL',
'cs_start_pattern' => 'Start Pattern',
'cs_end_pattern' => 'End Pattern',
'cs_start_delimiter' => 'Start Delimiter',
'cs_end_delimiter' => 'End Delimiter',
'cs_start_delimiter2' => 'Alternate Start Delimiter',
'cs_end_delimiter2' => 'Alternate End Delimiter',
'cs_out_of_stock' => 'Out of stock pattern',
'cs_instance' => 'Instance',
'cs_active' => 'Active',
);
?>
