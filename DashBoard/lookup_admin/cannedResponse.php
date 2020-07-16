<?php
$list_script = "cannedResponse_list.php";
$edit_script = "cannedResponse_edit.php";
$new_script = "";
$delete_script = "cannedResponse_delete.php";
$parent_script = "index.php";
$tablename = "canned_responses";
$database_interface = "db.php";
$key = "cr_id";
$key_type = 'I';
$fields = array(
'cr_cq_id' => 'select cq_id, cq_text from canned_question',
'cr_name' => 'S',
'cr_text' => 'T',
);

$titles = array(
'cr_cq_id' => 'Question',
'cr_name' => 'Name',
'cr_text' => 'Response',
);
?>
