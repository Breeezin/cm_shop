<?php
	$this->param('sos_id');
	$this->param('BackURL');
	
	$Q_Update = query("
		UPDATE shopsystem_supplier_order_sheets
		SET sos_paid = NOW()
		WHERE sos_id = ".safe($this->ATTRIBUTES['sos_id'])."
	");	

	locationRelative('index.php?act=shopsystem_supplier_order_sheets.View&BackURL='.ss_URLEncodedFormat($this->ATTRIBUTES['BackURL']).'&sos_id='.$this->ATTRIBUTES['sos_id']);

?>