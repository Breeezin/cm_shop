<?php 
	if ($this->ATTRIBUTES['AsService'] and array_key_exists('DoAction',$this->ATTRIBUTES)) {
		return $result;
	} else {
		$this->display->layout = "AdminPopup";
		
		$this->display->title  = "Create New Item";
		
		$AreDefined = 0;
		
		if (array_key_exists('DoAction',$this->ATTRIBUTES)) {	
			if (!strlen($this->ATTRIBUTES['EntryErrors'])) {
				$AreDefined = 1;
			}
		}
		
		$IsNotDefined = !array_key_exists('AsService',$this->ATTRIBUTES);
		$data = array();
		$data['Script_Name'] =  $_SERVER['SCRIPT_NAME'];
		$data['act'] = $this->ATTRIBUTES['act'];
		$data['OnClick'] = 'this.forms.AddForm.as_parent_as_id.value=9';
		$data['HasError'] = strlen($this->ATTRIBUTES['EntryErrors']);
		//$data['AreDefined'] = array_key_exists('DoAction',$this->ATTRIBUTES) AND !array_key_exists('AsService',$this->ATTRIBUTES) AND !strlen($this->ATTRIBUTES['EntryErrors']);
		$data['IsNotDefined'] = $IsNotDefined;
		$data['AreDefined'] = $AreDefined; 		
		$data['as_name'] = $this->ATTRIBUTES['as_name'];	
		$data['AssetTypes'] = $types;
		$data['MaxAst'] = array_key_exists('MaxAst',$this->ATTRIBUTES) ? $this->ATTRIBUTES['MaxAst'] : -1;
		$data['as_name'] = array_key_exists('as_name',$this->ATTRIBUTES) ? $this->ATTRIBUTES['as_name'] : '';
		$data['as_type'] = array_key_exists('as_type',$this->ATTRIBUTES) ? $this->ATTRIBUTES['as_type'] : '';
		$data['as_parent_as_id'] = array_key_exists('as_parent_as_id',$this->ATTRIBUTES) ? $this->ATTRIBUTES['as_parent_as_id'] : '';
		$data['IsDefinedParentLink'] = array_key_exists('as_parent_as_id',$this->ATTRIBUTES);
		//ss_DumpVar("data", $data);
		$this->useTemplate('Add', $data);

	}		
?>