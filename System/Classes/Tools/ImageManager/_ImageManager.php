<?php

class ImageManager extends Plugin {
	
	function __construct() {
		$this->pluginDirectory = dirname(__FILE__);
		parent::__construct();
		//$this->Plugin();
	}

	function Selector() {
		require("query_selector.php");
		$this->processTemplate("view_selector", $data);
		$this->display->layout = "None";
	}
	
	function Get() {
		require("query_get.php");
	}
	function display() {
		require("view_file.php");
	}
		
	function Upload() {
		require("model_upload.php");
	}
	
	function Delete() {
		require("model_delete.php");
	}

	function SimpleUpload() {
		$this->display->layout = "None";
		require("model_simpleUpload.php");
		require("view_simpleUpload.php");
	}

    function Create() {
		require("model_create.php");
	}

	function exposeServices() {
		return array(
			"ImageManager.Selector"	=>	array('method'	=>	'Selector'),
			"ImageManager.Display"=>	array('method'	=>	'display'),			
			"ImageManager.get"		=>	array('method'	=>	'Get'),
			"ImageManager.upload"	=>	array('method'	=>	'Upload'),
			"ImageManager.delete"	=>	array('method'	=>	'Delete'),
			"ImageManager.SimpleUpload"	=>	array('method'	=>	'SimpleUpload'),
            "ImageManager.Create"	=>	array('method'	=>	'Create'),
		);
	}
	
}


?>
