<?PHP

	$this->display->layout = "AdminPopup";	
	$this->display->title  = "Copy {$asset['as_name']}";
	

	$data = array();
	$AreDefined = 0;
	if (array_key_exists('DoAction',$this->ATTRIBUTES)) {			
		$AreDefined = 1;				
	}
	$data['AreDefined'] = $AreDefined; 
	$data['AnyError'] = count($entryErrors); 
	$data['Asset'] = $asset; 
	
	

	$key = ss_systemAsset('index.php');
	$result = new Request("Asset.Tree",array(
			'OnClick' => 'copyAssetTo', 
			'OpenAssets' => array($key => 1,),
			'FilterByAdmin'		=>	true,
	));
			
	$data['TreeResult'] = $result->display; 

	$data['EntryErrors'] = $entryErrors; 
	
	//ss_DumpVar($this->ATTRIBUTES);
	
	//ss_DumpVar($data);
	print $this->processTemplate('CopyAsset', $data);
	
?>
	
	
