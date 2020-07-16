<?php
requireOnceClass("Administration");

class Database extends Administration {
	
	function exposeServices() {
		$prefix = 'CSSEditor';
		return array(		 	
		 	"{$prefix}.Edit"		=>	array('method'	=>	'edit'),		 	
		 	"DataCollection.ImagesCleanUp"		=>	array('method'	=>	'imagesCleanUp')		 	
		 );		
	}
	function imagesCleanUp() {
		require('model_imagesCleanUp.php');
	}
	function edit() {		
		require('model_edit.php');
		require('view_edit.php');
	}
	
	function inputFilter() {
		parent::inputFilter();	
	}
}
?>