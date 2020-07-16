<?php

	$this->param('as_id');
	$this->param('UpdateType');		// either 'NewAsset' or 'NewUserGroup'

	$assetID = $this->ATTRIBUTES['as_id'];
	
	// Check if we can find the asset
	$Q_Asset = query("
		SELECT * FROM assets
		WHERE as_id = $assetID
	");

	if ($Q_Asset->numRows()) {
		$asset = $Q_Asset->fetchRow();
		
		startTransaction();
		
		// Get an array of all user groups
		$Q_UserGroups = query("
			SELECT * FROM user_groups
		");
		$userGroups = $Q_UserGroups->columnValuesArray('ug_id');

		// Get an array of all user groups that already have permissions
		$Q_AssetPermissions = query("
			SELECT * FROM asset_user_groups
			WHERE aug_as_id = $assetID
				AND aug_can_use IS NOT NULL
				AND aug_can_administer IS NOT NULL
				AND aug_child_can_use IS NOT NULL
				AND aug_child_can_administer IS NOT NULL
		");
		$permissionUserGroups = $Q_AssetPermissions->columnValuesArray('aug_ug_id',true);
		
				
		switch ($this->ATTRIBUTES['UpdateType']) {
			case 'Propagate':
				// In the case of propagating permissions, we will look to the parent
				// for permissions
			case 'NewAsset':
				// In the case of a new asset, we will look to our parent for 
				// permissions		
			
				// 'NewAsset' should never be called for the index.php assset
				// as it will already have permissions.
				if ($asset['as_parent_as_id'] == null) {
					die("Asset $assetID is an orphan. Cannot do 'NewAsset' permission update");
				}
				
				// Find the default permissions from our parent
				$parentAsset = getRow("
					SELECT * FROM assets
					WHERE as_id = {$asset['as_parent_as_id']}
				");
				
				// In the case of propagating.. we want to assign
				// default permissions on all subassets
				$updateAllPermissions = false;
				if ($this->ATTRIBUTES['UpdateType'] == 'Propagate') {
					$updateAllPermissions = true;	
				}
				
				$Q_SetAssetPermissions = query("
					UPDATE assets
					SET as_id = as_id
						".(($updateAllPermissions or $asset['as_can_use_default']==null)?',as_can_use_default = '.$parentAsset['as_child_can_use_default']:'')."
						".(($updateAllPermissions or $asset['as_can_admin_default']==null)?',as_can_admin_default = '.$parentAsset['as_child_can_admin_default']:'')."
						".(($updateAllPermissions or $asset['as_child_can_use_default']==null)?',as_child_can_use_default = '.$parentAsset['as_child_can_use_default']:'')."
						".(($updateAllPermissions or $asset['as_child_can_admin_default']==null)?',as_child_can_admin_default = '.$parentAsset['as_child_can_admin_default']:'')."
					WHERE as_id = $assetID
				");
				

				// Now loop thru all the user groups and if it is not in the 
				// permission user groups array, we insert some permissions!
				foreach ($userGroups as $userGroup) {
					if ($updateAllPermissions or !array_key_exists($userGroup,$permissionUserGroups)) {
						
						// Grab the permissions for the parent.
						// The parent asset will ALWAYS have permissions for this group,
						$Q_ParentAssetUserGroupPermissions = query("
							SELECT * FROM asset_user_groups
							WHERE aug_as_id = {$asset['as_parent_as_id']}
								AND aug_ug_id = $userGroup
						");
						
						// if not.. then die
						if ($Q_ParentAssetUserGroupPermissions->numRows() == 0) {
							die("Permissions missing for user group $userGroup on asset {$asset['as_parent_as_id']}.");
						} else {
							// Parent Asset User Group Permissions = paugp :)
							$paugp = $Q_ParentAssetUserGroupPermissions->fetchRow();
						}
	
						// Incase there are permissions, but they are null values
						$Q_CleanPermissions = query("
							DELETE FROM asset_user_groups
							WHERE aug_as_id = $assetID	
								AND aug_ug_id = $userGroup
						");
						
						// Now insert a record based on the parent "child" permissions
						// The new assets' child permissions are assumed to be the same as
						// those for the new asset.
						$Q_InsertPermissions = query("
							INSERT INTO asset_user_groups
								(aug_ug_id, aug_as_id, 
								CanUse, aug_can_administer, 
								aug_child_can_use, aug_child_can_administer)
							VALUES
								($userGroup, $assetID, 
								{$paugp['aug_child_can_use']}, {$paugp['aug_child_can_administer']}, 
								{$paugp['aug_child_can_use']}, {$paugp['aug_child_can_administer']})
						");
					}
				}
				break;
			case 'NewUserGroup':
				// In the case of a new user group added to the system,
				// we will simply use our default permissions

				foreach ($userGroups as $userGroup) {
					if (!array_key_exists($userGroup,$permissionUserGroups)) {
						
						// Incase there are permissions, but they are null values
						$Q_CleanPermissions = query("
							DELETE FROM asset_user_groups
							WHERE aug_as_id = $assetID	
								AND aug_ug_id = $userGroup
						");						
						
						$Q_InsertPermissions = query("
							INSERT INTO asset_user_groups
								(aug_ug_id, aug_as_id, 
								CanUse, aug_can_administer, 
								aug_child_can_use, aug_child_can_administer)
							VALUES
								($userGroup, $assetID, 
								{$asset['as_can_use_default']}, {$asset['as_can_admin_default']}, 
								{$asset['as_child_can_use_default']}, {$asset['as_child_can_admin_default']})
						");
					}
				}
				
				break;
		}
		
		commit();
		
	}
	
?>
