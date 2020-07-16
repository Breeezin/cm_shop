<?php
$list_script = "exchange_list.php";
$edit_script = "exchange_edit.php";
$new_script = "exchange_add.php";
$delete_script = "exchange_delete.php";
$parent_script = "index.php";
$tablename = "ExchangeRates";
$database_interface = "db_shared.php";
$key = "CONCAT_WS('->',Source,Dest)";
$key_type = 'S';
$fields = array(
//			'Source' => 'S',
//			'Dest' => 'S',
			'Rate' => 'I',
			'ForceRate' => 'I',
          );

$titles = array(
//			'Source' => 'Source Currency',
//			'Dest' => 'Destination Currency',
			'Rate' => 'Exchange Rate',
			'ForceRate' => 'Forced Exchange Rate',
          );

?>
