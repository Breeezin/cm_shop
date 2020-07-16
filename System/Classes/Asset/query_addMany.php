<?php
	//<CFMODULE TEMPLATE="#CFG.TopLevel#" act="authenticate" Processor="User" Permission="Administer"/>
	$errors = array();
	$this->param("AssetsToAdd","");
	//Find out the asset types we know about and the limits assigned to them 
	$whereSQL = "";
	$types = array();
	
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
		// if the user is super/im then show all asset types
		$Q_AssetTypes = query("
			SELECT * FROM asset_types
		");
		// list of types to display
		$displayTypes = ArrayToList($Q_AssetTypes->columnValuesArray("at_display"),", ");
	
		// put the types into array 
		while($aType = $Q_AssetTypes->fetchRow()) {
			$types["{$aType['at_display']}"] = $aType['at_name'];
		}
					
	} else {
		// if the user is not super or im
		// only shows the asset types he can create
		$Q_AssetTypes = query("SELECT at_id, at_display, at_name, as_type, Count(as_id) AS AssetCount, at_limit
					FROM assets, asset_types
					WHERE at_name Like as_type 
						AND as_deleted != 1
					GROUP  BY as_type");
		$astyIDs = ArrayToList($Q_AssetTypes->columnValuesArray("at_id"));
		
		// list of types to display
		// put the types into array 
		$displayTypes = '';
		while($aType = $Q_AssetTypes->fetchRow()) {
			if (($aType['at_limit'] == null) or ($aType['AssetCount'] < $aType['at_limit'])) {
				$displayTypes = ListAppend($displayTypes,$aType['at_name'],", ");
				$types["{$aType['at_display']}"] = $aType['at_name'];
			//ss_DumpVar($aType);
			}
		}
		
		//ss_DumpVar($astyIDs);
		
		// check any other asset types that are included the list
		$Q_OtherTypes = query("SELECT * FROM asset_types WHERE at_id NOT IN ($astyIDs)");
		// put the other types into array 
		
		while($aType = $Q_OtherTypes->fetchRow()) {
			if (($aType['at_limit'] == null) or ($aType['at_limit']  > 0)) {
				$displayTypes = ListAppend($displayTypes,$aType['at_name'],", ");
				$types["{$aType['at_display']}"] = $aType['at_name'];
				//ss_DumpVar($aType);
			}
		}
	}
	
	
?>