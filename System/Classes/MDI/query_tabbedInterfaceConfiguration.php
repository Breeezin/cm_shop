<?php 

	//Check if this website has any shops or mailing lists
	$Q_Shops = query("SELECT * FROM assets WHERE as_type LIKE 'ShopSystem'");
	$Q_Mailing = query("SELECT * FROM assets WHERE as_type LIKE 'MailingList'");
	
	if ($Q_Shops->numRows() > 0) {
		/*- Only let them see the shop panel if they have access to 
		 	- administer the shop asset*/
		 	
		//<CFMODULE TEMPLATE="#CFG.TopLevel#" FUSEACTION="authenticate" ASSETID="#Q_AssetTypeCheck.as_id#" PERMISSION="Administer" LOGINONFAIL="No" /);
		// if authenticate ok
		while($aAsset = $Q_Shops->fetchRow()) {
			$this->param("Has{$aAsset['as_type']}", "Yes");
			$this->param("{$aAsset['as_type']}as_id", "{$aAsset['as_id']}");
			$this->param("{$aAsset['as_type']}as_parent_as_id", "{$aAsset['as_parent_as_id']}");
			
			//<CFMODULE TEMPLATE="#CFG.TopLevel#" FUSEACTION="AssetPathFromID" ASSETID="#Q_AssetTypeCheck.as_parent_as_id#");
			$parentPath =  new Request("Asset.PathFromID", array('as_id'	=> $aAsset['as_parent_as_id'],));
						
			$this->param("{$aAsset['as_type']}AssetParentPath", $parentPath->value);
			
			//<CFMODULE TEMPLATE="#CFG.TopLevel#" FUSEACTION="AssetPathFromID" ASSETID="#Q_AssetTypeCheck.as_id#");
			$path =  new Request("Asset.PathFromID", array('as_id'	=> $aAsset['as_id'],));
			$this->param("{$aAsset['as_type']}AssetPath", $path->value);
			// end if
			
			//Default to No 
			$this->param("Has{$aAsset['as_type']}", "No");
			$this->param("{$aAsset['as_type']}as_id", -1);
			$this->param("{$aAsset['as_type']}AssetPath", "/index.cfm/System assets/404 Error");
		}
	}
	
	if ($Q_Mailing->numRows() > 0) {
		/*- Only let them see the shop panel if they have access to 
		 	- administer the shop asset*/
		 	
		//<CFMODULE TEMPLATE="#CFG.TopLevel#" FUSEACTION="authenticate" ASSETID="#Q_AssetTypeCheck.as_id#" PERMISSION="Administer" LOGINONFAIL="No" /);
		// if authenticate ok
		while($aAsset = $Q_Mailing->fetchRow()) {
			$this->param("Has{$aAsset['as_type']}", "Yes");
			$this->param("{$aAsset['as_type']}as_id", "{$aAsset['as_id']}");
			$this->param("{$aAsset['as_type']}as_parent_as_id", "{$aAsset['as_parent_as_id']}");
			
			//<CFMODULE TEMPLATE="#CFG.TopLevel#" FUSEACTION="AssetPathFromID" ASSETID="#Q_AssetTypeCheck.as_parent_as_id#");
			$parentPath =  new Request("Asset.PathFromID", array('as_id'	=> $aAsset['as_parent_as_id'],));
						
			$this->param("{$aAsset['as_type']}AssetParentPath", $parentPath);
			
			//<CFMODULE TEMPLATE="#CFG.TopLevel#" FUSEACTION="AssetPathFromID" ASSETID="#Q_AssetTypeCheck.as_id#");
			$path =  new Request("Asset.PathFromID", array('as_id'	=> $aAsset['as_id'],));
			$this->param("{$aAsset['as_type']}AssetPath", $path);
			// end if
			
			//Default to No 
			$this->param("Has{$aAsset['as_type']}", "No");
			$this->param("{$aAsset['as_type']}as_id", -1);
			$this->param("{$aAsset['as_type']}AssetPath", "System assets/404 Error");
		}
	}
	
		
?>