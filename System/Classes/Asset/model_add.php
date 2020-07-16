<?php 


	//$this->param("EntryErrors",'');
	//ss_log_message_r("this", $this);
	if (array_key_exists('DoAction',$this->ATTRIBUTES)) {
		$this->param("as_type");
		$this->param("as_appear_in_menus");
		$this->param("as_name");
		$this->param("as_parent_as_id");	
		$this->param('OnlineNow',0);
		
		//ss_DumpVarDie($this->ATTRIBUTES);
		
		//Make sure the user is allowed to create one
		$Q_IsPermitted = getRow("SELECT * FROM asset_types WHERE at_name LIKE '{$this->ATTRIBUTES['as_type']}'");

		if ($Q_IsPermitted['at_id'] != null) {
			
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
			$canAdd = false;
			if ($isSuperUser || $isIMediaUser) {
				$canAdd = true;
			} 

			startTransaction();
			
			if(!$canAdd) {
				$Q_AssetTypes = getRow("SELECT Count(as_id) AS AssetNum
					FROM assets
					WHERE as_type Like '{$this->ATTRIBUTES['as_type']}' 
					AND as_deleted = 0 
					AND as_id >= 500
				");
				if ($Q_IsPermitted['at_limit'] > $Q_AssetTypes['AssetNum'] || !strlen($Q_IsPermitted['at_limit'])) 
					$canAdd = true;
			}
			
			if ($canAdd) {
				//Find a new Asset Name 
				$as_name = ss_newAssetName($this->ATTRIBUTES['as_name'],$this->ATTRIBUTES['as_parent_as_id']);
				//ss_DumpVarDie($as_name);
				//insert those details
				$Q_MaxAst = query("SELECT Max(as_id) AS MaxAst FROM assets WHERE as_id >= 500");
				$maxAst = $Q_MaxAst->fetchRow();
				
				$this->ATTRIBUTES['MaxAst'] = $maxAst['MaxAst'];
				$this->ATTRIBUTES['as_name'] = $as_name;
				
				if (strlen($this->ATTRIBUTES['MaxAst']))
				 	$this->ATTRIBUTES['MaxAst'] = $this->ATTRIBUTES['MaxAst'] + 1;
				else 
					$this->ATTRIBUTES['MaxAst'] = 500;
				
				// Create default layout settings
				$assetLayout = array(
					'LYT_LAYOUT'		=>	'default',
					'LYT_STYLESHEET'	=>	'main',
					'LYT_LAYOUT_APPLY_TO_CHILDREN'	=>	1,
				);
	
				// Check if we inherit our parents layout
				$ParentAsset = getRow("
					SELECT * FROM assets
					WHERE as_id = ".safe($this->ATTRIBUTES['as_parent_as_id'])."
				");
				if (is_array($ParentAsset)) {
					$parentLayout = ss_TryUnserialize($ParentAsset['as_layout_serialized']);
					ss_paramKey($parentLayout,'LYT_LAYOUT','default');
					ss_paramKey($parentLayout,'LYT_STYLESHEET','main');
					ss_paramKey($parentLayout,'LYT_LAYOUT_APPLY_TO_CHILDREN',true);
					
					if ($parentLayout['LYT_LAYOUT_APPLY_TO_CHILDREN']) {
						$assetLayout['LYT_LAYOUT'] = $parentLayout['LYT_LAYOUT'];			
						$assetLayout['LYT_STYLESHEET'] = $parentLayout['LYT_STYLESHEET'];			
					}
				}
				$assetLayoutCereal = serialize($assetLayout);
				
					
				$sortOrder = newSortOrder("assets","as_sort_order","as_parent_as_id","{$this->ATTRIBUTES['as_parent_as_id']}");
				$now = now();	
				
				$extraFieldsSQL = '';
				$extraValuesSQL = '';
				if (ss_optionExists('Schedule assets')) {
					// if we support scheduling and they didn't tick "make online online immediately?" then we don't put the item online.
					if ($this->atts['OnlineNow'] != 1) {
						$extraFieldsSQL .= ", AssetOnline";
						$extraValuesSQL .= ", 'Never' ";
					}
				}
							
				$Q_InsAst =query("
					INSERT INTO assets 
						(as_id, as_parent_as_id, as_name, 
						 as_last_modified, as_archive, as_serialized, as_type,
						 as_appear_in_menus, as_dev_asset, as_promotion_date,
						 as_reversion_date, as_sort_order, as_layout_serialized, as_owner_au_id,as_hidden $extraFieldsSQL)
					VALUES
						({$this->ATTRIBUTES['MaxAst']}, {$this->ATTRIBUTES['as_parent_as_id']}, '{$as_name}', 
						NOW(), 1, NULL, '{$this->ATTRIBUTES['as_type']}', 
						{$this->ATTRIBUTES['as_appear_in_menus']}, 0, NULL, 
						NULL, $sortOrder, '".escape($assetLayoutCereal)."', ".$_SESSION['User']['us_id'].", 0 $extraValuesSQL) 
				");
	
				// check this later'{$now}', 1, NULL, '{$this->ATTRIBUTES['as_type']}', 0, 0, NULL, NULL, NULL, NULL, #SESSION.User#) ");
					
				$this->ATTRIBUTES['as_id'] = $this->ATTRIBUTES['MaxAst'];
				$this->id = $this->ATTRIBUTES['as_id'];
				
				ss_storeForAsset($this->ATTRIBUTES['as_id']);				
					
				$className = $this->ATTRIBUTES['as_type']."Asset";				
					
				requireOnceClass($className);
					
				$assetType = new $className;
				
				$as_serialized = $assetType->newAsset($this);
				
				if ($as_serialized != null) {	
					$Q_Update = query("UPDATE assets 
						SET as_serialized = '$as_serialized'	
						WHERE as_id = {$this->ATTRIBUTES['as_id']}
					");
				}
				
				// Assign some permissions based on the parent
				$temp = new Request("Security.CreateAssetPermissions",array(
					'as_id'	=>	$this->ATTRIBUTES['as_id'],
					'UpdateType' => 'NewAsset',
				));
				$result = $this->ATTRIBUTES['as_id'];
				
			} else {
				$this->ATTRIBUTES['EntryErrors'] = "Limited";
				$result = null;
			}
			
			commit();
		} else {
			$this->ATTRIBUTES['EntryErrors'] = "No Such Type";
			$result = null;
		}		
	}
	

?>
