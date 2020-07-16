<?php
$list_script = "product_dropdown_list.php";
$edit_script = "product_dropdown_edit.php";
$new_script = "product_dropdown_add.php";
$delete_script = "product_dropdown_delete.php";
$parent_script = "index.php";
$tablename = "product_dropdown";
$database_interface = "db.php";
$key = "pd_id";
$key_type = 'I';
$fields = array(
'pd_ph_id' => 'select ph_id, ph_name from product_heading',
'pd_sort' => 'I',
'pd_ca_id' => 'select ca_id, ca_name from shopsystem_categories order by ca_name',
'pd_column' => 'I',
);

$titles = array(
'pd_ph_id' => 'Category Heading',
'pd_sort' => 'Category Sort Order',
'pd_ca_id' => 'Category Name',
'pd_column' => 'Dropdown Column',
);
?>
