<?PHP

	$this->display->layout = "AdminPopup";	
	$this->display->title  = "Move Asset";
	
	$data = array();
	$AreDefined = 0;
	if (array_key_exists('DoAction',$this->ATTRIBUTES)) {	
		//if (!strlen($this->ATTRIBUTES['EntryErrors'])) {
		//	$AreDefined = 1;
		//}
		$data['NewAssetName'] = $assetName; 
		$AreDefined = 1;
	}
	$data['AreDefined'] = $AreDefined; 
	$data['Asset'] = $asset; 
	$key = ss_systemAsset('index.php');
	$result = new Request("Asset.Tree",
				array('OnClick' => 'moveAssetTo', 
					'OpenAssets' => array($key => 1,),
					'FilterByAdmin'		=>	true,
				));
				
	$data['TreeResult'] = $result->display; 

	
	//ss_DumpVar($this->ATTRIBUTES);
	
	//ss_DumpVar($data);
	print $this->processTemplate('MoveAsset', $data);
	
?>
	
	
