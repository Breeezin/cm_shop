<?php
	header('Content-Type: application/download',true);
	header('Content-Disposition: attachment; filename=users.txt',true);
	print($users);
?>