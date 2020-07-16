<?php 
	//$this->display->layout = "popup";
	$this->display->layout = "AdminPopup";
	
	$this->display->title = "Add Multiple assets";

	$ListLayouts = ListToArray(file_get_contents(expandPath('Custom/ContentStore/Layouts/Layouts.txt')),Chr(10));
	$layouts = "";
	foreach ($ListLayouts as $aLayout) {
		$layouts = ListAppend($layouts,ListLast($aLayout,":"),", ");		
	}
	
	$StyleSheetsList = ListToArray(file_get_contents(expandPath('Custom/ContentStore/Layouts/Stylesheets.txt')),Chr(10));
	$stylesheets = "";
	foreach ($StyleSheetsList as $aStylesheet) {
		$stylesheets = ListAppend($stylesheets,ListLast($aStylesheet,":"),", ");		
	}
	$AreDefined = 0;
	
	if (array_key_exists('DoAction',$this->ATTRIBUTES)) {	
		if (!count($errors)) {
			$AreDefined = 1;
		}
	}
	$data = array();
	
	$data['ListLayouts'] = $layouts;
	$data['TypeDisplay'] = $displayTypes;
	$data['Script_Name'] =  $_SERVER['SCRIPT_NAME'];
	$data['act'] = $this->ATTRIBUTES['act'];
	$data['EntryErrors'] = $errors;
	$data['HasError'] = count($data['EntryErrors']);
	//$data['IsNotDefined'] = $IsNotDefined;
	$data['AreDefined'] = $AreDefined; 	       
	$data['StyleSheetsList'] = $stylesheets;
	$data['AssetsToAdd'] = $this->ATTRIBUTES['AssetsToAdd'];

	
	//ss_DumpVar("data", $data);
	$this->useTemplate('AddMany', $data);
	

?>