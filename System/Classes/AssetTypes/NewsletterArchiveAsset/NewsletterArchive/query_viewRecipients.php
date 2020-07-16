<?php
	$this->param("na_id");
	$this->param("BackURL");
	
	$Newsletter = getRow("
		SELECT * FROM newsletter_archive
		WHERE na_id = {$this->ATTRIBUTES['na_id']}
			AND (
				na_as_id = {$this->ATTRIBUTES['as_id']}
					OR 
				na_as_id IS NULL
				)
	");
	
	$Q_Recipients = query("
		SELECT * FROM newsletter_archive_recipients
		WHERE nar_nl_id = {$this->ATTRIBUTES['na_id']}
		ORDER BY nar_lastname, nar_firstname
	");

?>