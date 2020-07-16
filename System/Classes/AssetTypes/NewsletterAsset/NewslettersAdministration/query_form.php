<?php

	// If this is being displayed for an edit, and it's not a reedit of the 
	// form after a failure we need to default the values from the database
	// (note that if primary key is given, we determine this to be an edit, 
	// if it's not, then it's not an edit.
	$this->loadFieldValues($this->ATTRIBUTES,NULL,NULL,$errors);
	
?>