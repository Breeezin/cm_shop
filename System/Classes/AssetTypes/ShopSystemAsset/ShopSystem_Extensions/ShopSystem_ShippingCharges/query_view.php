<?php

	$this->param('ssc_id');

	$Q_ShippingCharge = query("
		SELECT * FROM {$this->tableName}
		WHERE ssc_id = ".safe($this->ATTRIBUTES['ssc_id'])."
	");

?>