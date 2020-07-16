<?php
$list_script = "product_heading_list.php";
$edit_script = "product_heading_edit.php";
$new_script = "product_heading_add.php";
$delete_script = "";
$parent_script = "index.php";
$tablename = "product_heading";
$database_interface = "db.php";
$key = "ph_id";
$key_type = 'I';
$fields = array(
'ph_name' => 'S',
'ph_sort' => 'I',
'ph_url' => 'S',
);

$titles = array(
'ph_name' => 'Heading Name',
'ph_sort' => 'Sort Order',
'ph_url' => 'Clicked on URL',
);
?>
