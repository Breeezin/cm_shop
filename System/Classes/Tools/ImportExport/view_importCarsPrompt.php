<?php
	$this->display->title = 'Import Cars';

	if (!array_key_exists('DoAction',$this->ATTRIBUTES)) {

		$this->useTemplate('ImportCarsPrompt',$data);
	}
?>
