<html>
<body>
<?php

	
	
	// Include and instantiate the class type
	$className = $this->fields['as_type'].'Asset';
	requireOnceClass($className);
	$temp = new $className;
	$temp->ATTRIBUTES = &$this->ATTRIBUTES;

	// Call the display handler for the specific type
	$temp->getInfo(&$this);
	
?>

</body>
</html>