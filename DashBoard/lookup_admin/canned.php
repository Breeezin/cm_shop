<?php
$list_script = "canned_question_list.php";
$edit_script = "canned_question_edit.php";
$new_script = "";
$delete_script = "canned_question_delete.php";
$parent_script = "index.php";
$tablename = "canned_question";
$database_interface = "db.php";
$key = "cq_id";
$key_type = 'I';
$fields = array(
'cq_text' => 'S',
'cq_invisible' => 'select 0, "false" UNION select 1, "true"',
);

$titles = array(
'cq_text' => 'Question',
'cq_invisible' => 'Invisible to Client',
);
?>
