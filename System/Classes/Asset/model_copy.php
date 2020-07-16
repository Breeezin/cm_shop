<?PHP

	if (array_key_exists('DoAction',$this->ATTRIBUTES)) {
		
		startTransaction();
		
		$this->param("as_id");
		$this->param("as_parent_as_id");
		
		// Find out what the next available asset ID is --->
		$maxAst = newPrimaryKey("assets","as_id", 500);
		$inserts = array(); // stores all insert queries 
		$insertPermissions = array(); // stores all insert queries 
		$updates = array(); // stores all update queries 
		$lastAssetToSearch = $maxAst - 1;
		
		/*
		// We'll do this iteratively, 
			- build a list within a list kinda structure 
			-
			-	<source>|<dest>,<source>|<dest>,...
			- then copy source to dest appending to the list any that are necessary
			*/
		$copiers = $this->ATTRIBUTES['as_id']."|".$this->ATTRIBUTES['as_parent_as_id'];
		//ss_DumpVar($copiers,"start {$this->ATTRIBUTES['as_parent_as_id']}");
		$toBeCopied = "";
		//ss_DumpVarDie($this->ATTRIBUTES);
		while(strlen($copiers)) {
			
			$copySet = ListFirst($copiers);
			$copiers = ListRest($copiers);
		
			$source = ListFirst($copySet, "|");
			$destParent   = ListLast($copySet, "|");
			
			// Get details of this asset --->
			$Q_AssetD = getRow("SELECT * FROM assets WHERE as_id = $source");
						
			// Fix up the destination name if necessary--->
			$assetName = ss_newAssetName($Q_AssetD['as_name'],$destParent);
			
			//print(" new name $maxAst $assetName <BR>");
			// Get the details of source asset --->
			$Q_Asset = getRow("SELECT * FROM assets WHERE as_id = $source");
			$LimitAssetType = getRow("SELECT * FROM asset_types WHERE at_name LIKE '{$Q_Asset['as_type']}'");
			$NumAssetType = getRow("SELECT Count(as_id) AS NumAssets FROM assets WHERE as_type LIKE '{$Q_Asset['as_type']}'");
			//
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
			$canCopy = false;
			if ($isSuperUser || $isIMediaUser) {
				$canCopy = true;
			} 
			if(!$canCopy) {
				if ($LimitAssetType['at_limit'] > $NumAssetType['NumAssets']) 
					$canCopy = true;
			}
			
			/*
			INSERT INTO assets
					(as_id, as_parent_as_id, as_name, as_last_modified, as_archive, as_type,
					 as_appear_in_menus,as_dev_asset, as_promotion_date,
					 as_reversion_date, as_sort_order, as_layout_serialized, as_owner_au_id, 
				     as_system, as_hidden, as_header_name, as_menu_name, 
					 as_can_use_default, as_can_admin_default, as_child_can_use_default, as_child_can_admin_default
					)
					 VALUES
					 (574, 557,'Home',  Now(), 1, 'Page',
						1, 0, 'NULL', 
					'NULL', 6, 'a:8:{s:14:"LYT_TITLEIMAGE";s:36:"bf4f4eb6a6246a3f7cf2fb7eb77eb1e1.jpg";s:20:"LYT_MENU_NORMALIMAGE";s:0:"";s:23:"LYT_MENU_MOUSEOVERIMAGE";s:0:"";s:10:"LYT_LAYOUT";s:4:"home";s:28:"LYT_LAYOUT_APPLY_TO_CHILDREN";s:1:"1";s:15:"LYT_DESCRIPTION";s:21:"this is <p>a test</p>";s:14:"LYT_STYLESHEET";s:7:"invoice";s:12:"LYT_KEYWORDS";s:13:"testing.. 123";}',1
						0, 0, 'Welcome', 'The Home Page',
						1, 0, 1, 0
					)
				; [nativecode=1064 ** You have an error in your SQL syntax.  Check the manual that corresponds to your MySQL server version for the right syntax to use near '0, 0, 'Welcome', 'The Home Page',
					1, 0, 1, 0
					)' at li]
			*/	
			
			
			if ($LimitAssetType['at_limit'] == null OR $canCopy) {
				// Insert a new asset record --->				
				$as_type = strlen($Q_Asset['as_type'])?  "'".escape($Q_Asset['as_type'])."'" : 'Page';
				$as_archive = strlen($Q_Asset['as_archive'])? "'{$Q_Asset['as_archive']}'" : 'NULL';
				$as_appear_in_menus = strlen($Q_Asset['as_appear_in_menus'])? $Q_Asset['as_appear_in_menus'] : '0';
				$as_hidden = strlen($Q_Asset['as_hidden'])? $Q_Asset['as_hidden'] : '0';
				$as_system = strlen($Q_Asset['as_system'])? $Q_Asset['as_system'] : '0';
				$as_dev_asset = strlen($Q_Asset['as_dev_asset'])? $Q_Asset['as_dev_asset'] : '0';
				$as_promotion_date = strlen($Q_Asset['as_promotion_date'])? "'{$Q_Asset['as_dev_asset']}'" : 'NULL';
				$as_reversion_date = strlen($Q_Asset['as_reversion_date'])? "'{$Q_Asset['as_reversion_date']}'" : 'NULL';
				$as_layout_serialized = strlen($Q_Asset['as_layout_serialized'])? "'".escape($Q_Asset['as_layout_serialized'])."'" : 'NULL';
				$as_header_name = strlen($Q_Asset['as_header_name'])? "'".escape($Q_Asset['as_header_name'])."'" : 'NULL';
				$as_menu_name = strlen($Q_Asset['as_menu_name'])? "'".escape($Q_Asset['as_menu_name'])."'" : 'NULL';
				$sortOrder = $this->ATTRIBUTES['Copy'] ==  "Copy Branch" ? $Q_Asset['as_sort_order'] : newSortOrder("assets","as_sort_order","as_parent_as_id","{$destParent}");
				if (!strlen($sortOrder)) $sortOrder = 'NULL';
				
				array_push($inserts,"
				INSERT INTO assets
					(as_id, as_parent_as_id, as_name, as_last_modified, as_archive, as_type,
					 as_appear_in_menus,as_dev_asset, as_promotion_date,
					 as_reversion_date, as_sort_order, as_layout_serialized, 
					 as_owner_au_id, as_system, as_hidden, as_header_name, as_menu_name, 
					 as_can_use_default, as_can_admin_default, as_child_can_use_default, as_child_can_admin_default
					)
					 VALUES
					 ($maxAst, $destParent,'{$assetName}',  Now(), 1, $as_type,
					$as_appear_in_menus, $as_dev_asset, $as_promotion_date, 
					$as_reversion_date, $sortOrder, $as_layout_serialized,
					{$Q_Asset['as_owner_au_id']}, {$as_system}, {$as_hidden}, $as_header_name, $as_menu_name,
					{$Q_Asset['as_can_use_default']}, {$Q_Asset['as_can_admin_default']}, {$Q_Asset['as_child_can_use_default']}, {$Q_Asset['as_child_can_admin_default']}
					)
				");
				
				// copy all the permissions
				$Q_AssetUserGroups = query("SELECT * FROM asset_user_groups WHERE aug_as_id = {$Q_Asset['as_id']}");
				while ($aPermission = $Q_AssetUserGroups->fetchRow()) {
					
					array_push($insertPermissions,"
					INSERT INTO asset_user_groups
						(aug_ug_id, aug_as_id, aug_can_use, aug_can_administer, aug_child_can_use, aug_child_can_administer)
						 VALUES
						 ({$aPermission['aug_ug_id']}, $maxAst,{$aPermission['aug_can_use']}, {$aPermission['aug_can_administer']}, {$aPermission['aug_child_can_use']}, {$aPermission['aug_child_can_administer']})
					");				
				}
				ss_copyDirectoryWithSub(expandPath(ss_storeForAsset($source)),expandPath(ss_storeForAsset($maxAst)));				
				
				$className = $Q_Asset['as_type']."Asset";
				

				
				requireOnceClass($className);
				
				$assetType = new $className;
			
				$assetType->copy($this);
				
				// Put it into the asset record --->
				$cereal = escape($Q_Asset['as_serialized']);
				array_push($updates,"
					UPDATE assets SET as_serialized = '{$cereal}' WHERE as_id = $maxAst;
				");
				
				// Add children to copiers for the next pass --->			
				if ($this->ATTRIBUTES['Copy'] ==  "Copy Branch") {
					// Find out the children of the source (note we exclude any that we have just added) --->
					$Q_Assets = query("
						SELECT * FROM assets WHERE as_parent_as_id = $source AND as_deleted != 1 AND (as_id <= $lastAssetToSearch)
					");
					
					// Add them to copiers --->
					while($aAsset = $Q_Assets->fetchRow()) {
						$copiers = ListAppend($copiers, $aAsset['as_id']."|".$maxAst);
					}
					//ss_DumpVar($copiers,"children");
				}				
				$maxAst = $maxAst + 1;		
			} else {				
				if (count($entryErrors)) {
					array_push($entryErrors["Exceeded the limit for this asset type"],"{$Q_Asset['at_display']}");
				} else {
					array_push($entryErrors,array("Exceeded the limit for this asset type"));
				}
			}			
		}
		
		// if no error and any new asset are, then run the queres
		if (count($inserts) AND !count($entryErrors)) {
			//print($inserts);
			foreach ($inserts as $insert) {
				$Q_Inserts = query($insert);	
			}
			foreach ($insertPermissions as $insert) {
				$Q_Inserts = query($insert);	
			}
			foreach ($updates as $update) {
				$Q_Updates = query($update);
			}		
			
			// Assign some permissions
			ss_ExecuteRequestOnBranchAssets($this->ATTRIBUTES['as_id'],'Security.CreateAssetPermissions',array(
				'UpdateType'	=>	'NewAsset',
			));
		}
		
		commit();
	}

?>
