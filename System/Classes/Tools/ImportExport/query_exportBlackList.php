<?php

	// Firstly, we'll grab some users =b
	$Q_BlackList = query("
		SELECT * from shopsystem_blacklist
	");

	$blackList = ss_queryToTab($Q_BlackList);

?>
