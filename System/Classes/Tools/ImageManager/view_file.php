<?PHP
	$this->param('Type');	
	$this->param('File');
	
	if (strtolower($this->ATTRIBUTES['Type']) == 'image') {
        //briar added width and name 16.8.05
		$this->param('Width', '');
        $this->param('Name','');
        $this->display->layout = 'popupImage';
		$this->useTemplate('popupImage', $this->ATTRIBUTES);
	} else if (strtolower($this->ATTRIBUTES['Type']) == 'flash') {
		
		$this->param('Height');
		$this->param('Width');
		$this->display->layout = 'popupFlash';
		
		$this->useTemplate('popupFlash', $this->ATTRIBUTES);
	}
?>
