<? 	
	$this->param("as_id");
	
	$entryErrors = array();

	//ss_DumpVar($this->ATTRIBUTES);
	// Grab the asset details --->
	$asset = getRow("
		SELECT * FROM assets 
		WHERE (as_id = {$this->ATTRIBUTES['as_id']})
	");
	

?>
