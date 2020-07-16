<?PHP 

	//$this->param("Type");
	$this->display->layout = 'AdminPopup';
	$data = array();
	$data['act'] = $_REQUEST['act'];
	print $this->processTemplate('LinkSelector', $data);
	
?>