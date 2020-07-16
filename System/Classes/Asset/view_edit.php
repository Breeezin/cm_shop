<?php 
	$this->display->layout = 'MdiAdmin';

	if ($closeTab) {
		if ($assetNameChanged) {
			print("<script language=\"Javascript\">parent.closeAssets(new Array('{$this->ATTRIBUTES['as_id']}'));parent.assetReload();</script>");
		} else {
			print("<script language=\"Javascript\">parent.closeAssets(new Array('{$this->ATTRIBUTES['as_id']}'));</script>");
		}
	} else {
	
		$head = array();
		$head['Script_Name'] = $_SERVER['SCRIPT_NAME'];
		$head['as_id'] = $this->ATTRIBUTES['as_id'];
		
		$this->display->head = $this->processTemplate('Head_Edit', $head);
		
		$data = array();
		
		// Check for errors
		
		$data['errors'] = $errors;
		//disk free space
		$data['freeSpace'] = ss_getDiskSpaceUsage(false);
		$data['fieldSet'] = $this->fieldSet;
		$data['Script_Name'] = $_SERVER['SCRIPT_NAME'];
		$data['act'] = $this->ATTRIBUTES['act'];
		$data['as_id'] = $this->ATTRIBUTES['as_id'];
		$data['as_deleted'] = "";
	
		$data['as_name'] = $this->fields['as_name'];
		$data['as_subtitle'] = $this->fields['as_subtitle'];
		$data['as_system'] = $this->fields['as_system'];
		$data['as_owner_au_id'] = $this->fields['as_owner_au_id'];
		$data['as_type'] = $this->fields['as_type'];
		$data['as_appear_in_menus'] = $this->fields['as_appear_in_menus'];
		$data['at_display'] = $this->fields['at_display'];
		$data['as_parent_as_id'] = $this->fields['as_parent_as_id'];
		$data['RelativeHere'] = $this->classDirectory."/";
		$data['User'] = 1; // from session
		$data['SoHeight'] = $this->ATTRIBUTES['SoHeight'];
		$data['JustDid'] = $justDid;
		$data['as_layout_serialized'] = $this->fields['as_layout_serialized'];
		$data['AssetTypeObject'] = $assetType;
		$data['this'] = $this;	
		$data['IsSuperUser'] = $isSuperUser;
		$data['IsDeployer'] = $isTheDeployer;
		$data['AssetNameChanged'] = $assetNameChanged;
		if (array_key_exists('DoAction',$this->ATTRIBUTES)) {
			$data['DoAction'] = 'Yes';
		}
		
		if (ss_optionExists("Schedule assets")) {
			$data['AssetOffline'] = $this->fields['AssetOffline'];
			$data['AssetOnline'] = $this->fields['AssetOnline'];
		}
		
		$data['fieldSet'] = $this->fieldSet;
		$Q_SubAssets = query("SELECT * FROM assets WHERE as_parent_as_id = $id AND as_deleted != 1  AND as_hidden != 1 ORDER BY as_sort_order, as_name ASC");
		$data['Q_SubAssets'] = $Q_SubAssets;
			          
		$customTags = array(
			'OPENCLOSE'	=>	'$result = new Request("Asset.OpenerCloser",array(\'Icon\' =>	\'__ICON__\', \'Panel\'	=>	__PANEL__, \'Name\' => \'__NAME__\',));	print($result->display);',
		);
	
		//Find out the asset types we know about and the limits assigned to them 
		$isSuperUserResult = new Request("Security.Authenticate",array(
			'Permission'	=>	'IsSuperUser',
			'LoginOnFail'	=>	false,
		));
		$isSuperUser = $isSuperUserResult->value;
		
		$isIMediaUserResult = new Request("Security.Authenticate",array(
			'Permission'	=>	'IsDeployer',
			'LoginOnFail'	=>	false,
		));
		$isIMediaUser = $isIMediaUserResult->value;
		
		//check the current user whether he is super/im user or not..		
		if ($isSuperUser || $isIMediaUser) {
			$data['as_deleted'] = $this->fields['as_deleted'] == 1 ? "Deleted":"";
		}			
		
		$this->display->content = $this->useTemplate('Body_Edit', $data,$customTags);							
	
	}
?>
