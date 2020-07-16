<?php 	

	require("view_thankyou.php");		

	if (array_key_exists("LoginRequired", $this->ATTRIBUTES)) {
		// User isn't a member...
		$login = new Request('Security.Login',array(
			'BackURL'	=>	ss_withoutPreceedingSlash($asset->getPath()),
			'NoHusk'	=>	1,
		));		
		print	$login->display;
	}
?>