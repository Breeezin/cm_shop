<?PHP 

	//$this->param("Type");
	$this->display->layout = 'AdminPopup';
	$data = array();
	$data['act'] = $_REQUEST['act'];
	$data['as_id'] = $this->ATTRIBUTES['as_id'];
	$data['ImageWidth'] = $this->ATTRIBUTES['ImageWidth'];
	$data['ImageHeight'] = $this->ATTRIBUTES['ImageHeight'];
	$data['OpenAssets'] = array();
	$data['HasOption'] = ss_optionExists('CM Advanced Embed Asset Selector')?'1':'0';
	
	if (strlen($this->ATTRIBUTES['as_id'])) {
		$result = new Request("Asset.AncestorsFromID", array('as_id' => $this->ATTRIBUTES['as_id'],));
		if ($result->value != null) {
			$data['OpenAssets'] = $result->value;		
		}
		//ss_DumpVar($result);
	}
	
	$treeResult = new Request("Asset.Tree", array('OnClick' => 'embedAsset','OpenAssets' => $data['OpenAssets'], 'FilterByAdmin'		=>	true,)); 
	$data['TreeResult'] = $treeResult->display;
	print $this->processTemplate('ImageSelector', $data);
	
?>