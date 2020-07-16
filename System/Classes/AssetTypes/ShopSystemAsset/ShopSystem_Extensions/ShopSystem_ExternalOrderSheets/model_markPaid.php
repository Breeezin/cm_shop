<?php
	$this->param('ors_id');
	$this->param('BackURL');
	
	$Q_Update = query("
		UPDATE shopsystem_order_sheets
		SET ors_paid = NOW()
		WHERE ors_id = ".safe($this->ATTRIBUTES['ors_id'])."
	");	

	locationRelative('index.php?act=shopsystem_order_sheets.View&BackURL='.ss_URLEncodedFormat($this->ATTRIBUTES['BackURL']).'&ors_id='.$this->ATTRIBUTES['ors_id']);

?>
