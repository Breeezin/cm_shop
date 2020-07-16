<?php
	header('Content-Type: application/download',true);
	header('Content-Disposition: attachment; filename=BlackList.txt',true);
	print($blackList);
?>
