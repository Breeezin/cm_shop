<?php
	header('Content-Type: application/download',true);
	header('Content-Disposition: attachment; filename=StockList.csv',true);
	print($stockList);
?>
