<?php 
	$this->param("as_id");
	
	$Q_CanDelete = query("SELECT * FROM assets	WHERE (as_id = {$this->ATTRIBUTES['as_id']}) AND (NOT as_system = 1)");

	
	if ($Q_CanDelete->numRows()) {
	
		$delList = $this->ATTRIBUTES['as_id'];
				
		$delledItems = "";
		
		while(ListLen($delList)) {
			//Grab the first item from the list
			$ID = ListFirst($delList);
								
			//Get any children 
			$Q_Children = query("SELECT * FROM assets WHERE as_parent_as_id = $ID");
						
			// Add them to the list 
			while($child = $Q_Children->fetchRow()) {
				$delList = ListAppend($delList,$child['as_id']);
			}
			
			
			$delList = ListRest($delList);			
			
			// Add the ID to the delledItems list 
			$delledItems = ListAppend($delledItems, $ID);
		}
		
		// Delete all delledItems 
		$Q_Assets = query("SELECT * FROM assets WHERE as_id IN ($delledItems) ORDER BY as_id DESC");
		
		//ss_DumpVar($delledItems);
		
		while($aAsset = $Q_Assets->fetchRow()) {
	/*
			// Include and instantiate the class type
			$className = $aAsset['as_type'].'Asset';
			requireOnceClass($className);
			$temp = new $className;
	
			// Call the display handler for the specific type
			$temp->delete(&$this);			
	*/
			//<CFMODULE TEMPLATE="#CFG.TopLevel#" FUSEACTION="#as_type#_Delete" CEREAL="#as_serialized#" ASSETID="#as_id#" /; 
			/*Dont delete the asset for the asset recycling system*/
			$Q_DeleteAsset = query("UPDATE assets SET as_deleted = 1 WHERE (as_id = {$aAsset['as_id']}) AND (NOT as_system = 1)");
			
			//  delete the content directory 
			//ss_deleteFilesWithSub(expandPath(ss_storeForAsset(as_id)));
		}	
		
	}
	
?>