<?php
	// This file is for code to check that the PHP server 
	// is set up correctly for this site
	if ((bool) ini_get('magic_quotes_gpc')) {
		print('<html><body><h3>SERVER CONFIGURATION ERROR</h3>Please disable magic_quotes_gpc in php.ini (or create an .htaccess file with the line "php_value magic_quotes_gpc 0" in it and install in the root of this website) </body></html>');
		exit;
	}
	if ((bool) ini_get('register_globals')) {
		print('<html><body><h3>SERVER CONFIGURATION ERROR</h3>Please disable register_globals in php.ini (or create an .htaccess file with the line "php_value register_globals 0" in it and install in the root of this website) </body></html>');
		exit;
	}
?>
