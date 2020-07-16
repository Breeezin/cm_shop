<?php
$list_script = "pdf_list.php";
$edit_script = "pdf_edit.php";
$new_script = "pdf_add.php";
$delete_script = "";
$parent_script = "index.php";
$tablename = "pdf_printout_attributes";
$database_interface = "db.php";
$key = "label_no";
$key_type = 'I';
$fields = array(
'addr1_x' => 'I',
'addr1_y' => 'I',

'addr1_font_id' => 'select font_id, font_name from fonts',
'addr1_font_attr' => 'S',
'addr1_font_size' => 'I',
'addr1_font_align' => 'select align_id, align_name from alignment',
'addr1_font_colour' => 'select colour_id, colour_name from colours',

'addr2_font_id' => 'select font_id, font_name from fonts',
'addr2_font_attr' => 'S',
'addr2_font_size' => 'I',
'addr2_font_align' => 'select align_id, align_name from alignment',
'addr2_font_colour' => 'select colour_id, colour_name from colours',

'addr3_font_id' => 'select font_id, font_name from fonts',
'addr3_font_attr' => 'S',
'addr3_font_size' => 'I',
'addr3_font_align' => 'select align_id, align_name from alignment',
'addr3_font_colour' => 'select colour_id, colour_name from colours',

'addr4_font_id' => 'select font_id, font_name from fonts',
'addr4_font_attr' => 'S',
'addr4_font_size' => 'I',
'addr4_font_align' => 'select align_id, align_name from alignment',
'addr4_font_colour' => 'select colour_id, colour_name from colours',

'addr5_font_id' => 'select font_id, font_name from fonts',
'addr5_font_attr' => 'S',
'addr5_font_size' => 'I',
'addr5_font_align' => 'select align_id, align_name from alignment',
'addr5_font_colour' => 'select colour_id, colour_name from colours',
);

$titles = array(
'addr1_x' => 'X',
'addr1_y' => 'Y',

'addr1_font_id' => 'Font 1 ID',
'addr1_font_attr' => 'Font 1 Attributes',
'addr1_font_size' => 'Font 1 Size',
'addr1_font_align' => 'Font 1 Alignment',
'addr1_font_colour' => 'Font 1 Colour',

'addr2_font_id' => 'Font 2 ID',
'addr2_font_attr' => 'Font 2 Attributes',
'addr2_font_size' => 'Font 2 Size',
'addr2_font_align' => 'Font 2 Alignment',
'addr2_font_colour' => 'Font 2 Colour',

'addr3_font_id' => 'Font 3 ID',
'addr3_font_attr' => 'Font 3 Attributes',
'addr3_font_size' => 'Font 3 Size',
'addr3_font_align' => 'Font 3 Alignment',
'addr3_font_colour' => 'Font 3 Colour',

'addr4_font_id' => 'Font 4 ID',
'addr4_font_attr' => 'Font 4 Attributes',
'addr4_font_size' => 'Font 4 Size',
'addr4_font_align' => 'Font 4 Alignment',
'addr4_font_colour' => 'Font 4 Colour',

'addr5_font_id' => 'Font 5 ID',
'addr5_font_attr' => 'Font 5 Attributes',
'addr5_font_size' => 'Font 5 Size',
'addr5_font_align' => 'Font 5 Alignment',
'addr5_font_colour' => 'Font 5 Colour',

);
?>
