<?php

	$this->display->layout = 'none';

	function causeDelay() {
		for ($j=0; $j < 800; $j++) {
			$b = 235.546;
			for ($i = 0; $i<1000; $i++) {
				$t = $b*$i*123;
			}	
		}
	}

	// allow to run forever
	set_time_limit(0);

	// loop a bit	
	for ($i=0; $i<120; $i++) {
		//causeDelay();
		print ($i.' ');
		flush();
	}

	//die();
?>