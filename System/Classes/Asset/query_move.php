<? 	
	$this->param("as_id");
		
	// Grab the asset details --->
	$asset = getRow("
		SELECT * FROM assets 
		WHERE (as_id = {$this->ATTRIBUTES['as_id']})
			AND (NOT as_system = 1)
	");
	

?>
