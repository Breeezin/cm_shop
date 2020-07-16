<?php
	$this->param('ssc_id');
	$this->param('BackURL');
	
	$Q_Update = query("
		UPDATE shopsystem_shipping_charges
		SET ssc_paid = NOW()
		WHERE ssc_id = ".safe($this->ATTRIBUTES['ssc_id'])."
	");	

	locationRelative('index.php?act=shopsystem_shipping_charges.View&BackURL='.ss_URLEncodedFormat($this->ATTRIBUTES['BackURL']).'&ssc_id='.$this->ATTRIBUTES['ssc_id']);

?>