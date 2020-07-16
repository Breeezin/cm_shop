<?PHP 

	//$this->param("Type");
	$this->display->layout = 'AdminPopup';
	
	$data = array();
	$data['act'] = $_REQUEST['act'];
	$data['as_id'] = $this->ATTRIBUTES['as_id'];
	$data['SelectedImage'] = $this->ATTRIBUTES['SelectedIamge'];
	$data['OnClick'] = $this->ATTRIBUTES['OnClick'];
	$data['Directory'] = $directory;
	$data['Error'] = $error;
			
	print $this->processTemplate('SharedImageSelector', $data);
	
?>