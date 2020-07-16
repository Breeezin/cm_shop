<?php
	$this->param('or_id');
	$this->param('BackURL');
	$this->param('Note');
	$this->param('ShowPacking', 0);

	if( $this->ATTRIBUTES['ShowPacking'] == 1 )
		$sp = 1;
	else
		$sp = 0;

	$Q_Insert = query("
		INSERT INTO shopsystem_order_notes
			(orn_text, orn_timestamp, orn_show_packing, orn_or_id)
		VALUES
			('".escape($this->ATTRIBUTES['Note'])."', NOW(), $sp, ".safe($this->ATTRIBUTES['or_id']).")
	");
	
	locationRelative($this->ATTRIBUTES['BackURL']);
?>
