<?php
	$this->param("Image", "");   /* Relative path to image */
	$srcPath = expandPath($this->ATTRIBUTES["Image"]);		
	unlink($srcPath);
	location($this->ATTRIBUTES['RFA']);
?>
