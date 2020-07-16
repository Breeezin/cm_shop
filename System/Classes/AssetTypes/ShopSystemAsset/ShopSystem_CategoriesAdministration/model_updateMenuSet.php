<?php 
	// temporary store for ca_id
	$this->param('ca_id','');
	$caID = $this->ATTRIBUTES['ca_id'];	
	$this->ATTRIBUTES['ca_id'] = '';
	
	// grep all categories
	$tempCategories = $this->queryAllArray(true);
	
	$firstCaID = null;
	$isFirst = true;
	$assetPath = '';
	// get all 1st and 2nd level categories in a tree structure
	$categories = array();	
	foreach ($tempCategories as $aCat) {
		if ($isFirst) {
			// get asset path for the menu item link
			$result = new Request("Asset.PathFromID", array( 'as_id' => $aCat['ca_as_id'],));
			$assetPath = $result->value;
			$firstCaID = $aCat['ca_id'];
		}
		$isFirst = false;
		
		// check whether the category is first level or second			
		if (strlen($aCat['ca_parent_ca_id'])) {
			// second level category add into the parent category
			if (array_key_exists($aCat['ca_parent_ca_id'], $categories) and array_key_exists('children',$categories[$aCat['ca_parent_ca_id']])) {
				array_push($categories[$aCat['ca_parent_ca_id']]['children'],$aCat);			
			}				
		} else {
			// first level category
			
			// prepare for all details needed for menu
			$categories[$aCat['ca_id']] = array('details' => $aCat, 'children' => array());
			
			// check the menu set field for menu divisions and width
			if (strlen($aCat['ca_menu_set'])) {
				//eg) A-D,E-Z:190
				$menuSet = ListToArray($aCat['ca_menu_set'], ':');				
				
				if (count($menuSet) == 2) {
					$divisions = ListToArray(trim($menuSet[0]));
				
					// reformat the menu division
					$menuStru = array();
					foreach ($divisions as $div) {				
						$menuStru[$div] = ListLast($div, '-');				
					}
					$categories[$aCat['ca_id']]['divisions'] = $menuStru;
					
					if (strlen($menuSet[1])) {
						$categories[$aCat['ca_id']]['menuwidth'] = $menuSet[1];
					} else {
						$categories[$aCat['ca_id']]['menuwidth'] = 190;
					}
				} else {	
					if (substr($aCat['ca_menu_set'], 0, 1) == ":") {
						$categories[$aCat['ca_id']]['menuwidth'] = $menuSet[0];						
					} else {
						$categories[$aCat['ca_id']]['menuwidth'] = 190;						
					}								
				}
			} else {
				$categories[$aCat['ca_id']]['divisions'] = array();
				$categories[$aCat['ca_id']]['menuwidth'] = 190;
			}
		}
	}		
	$this->ATTRIBUTES['ca_id'] = $caID;
	
	$displayCategoreis = array();
	
	// get the sort order of sub-categories in a first level category
	foreach ($categories as $aCat) {
		$tempOrder = array();					
		if (array_key_exists('divisions', $aCat)) {
			if (count($aCat['divisions'])) {						
				$tempIndex = 0;	
				
				foreach ($aCat['children'] as $child) {
					// compare the division with the sub-category name
					// if the name is less then the division then store the index number
					foreach ($aCat['divisions'] as $key => $compare) {					
						$tempChars = strtolower(substr($child['ca_name'], 0, strlen($compare)));
						//print($child['ca_name']." detail ".$tempChars.' vs '.$compare.' = '.strnatcmp ($tempChars, strtolower($compare))."<BR>");
						if (strnatcmp ($tempChars, strtolower($compare)) <= 0) {
							if (!array_key_exists($key, $tempOrder)) {
								$tempOrder[$key] = array();
							}
							array_push($tempOrder[$key],$tempIndex);
							break;
						}
					}
					$tempIndex++;
				}			
			}
		}
		$displayCategoreis[$aCat['details']['ca_id']] = $tempOrder;
	}	
	
	//ss_DumpVar($displayCategoreis);
	//ss_DumpVarDie($categories);
	
	$temp = array();
	$newMenuJS = $this->processTemplate('Menu_FirstLevel', $temp);	
	$newMenuItemJS = $this->processTemplate('Menu_SecondLevel', $temp);
	$newMenuItemPropertyJS = $this->processTemplate('Menu_ItemProperty', $temp);
	$newMenuRootPropertyJS = $this->processTemplate('Menu_RootProperty', $temp);
	
	$content = "function mmLoadCategoryMenus() {\n";
	$isFirst = true;
	foreach ($categories as $caID => $aCat) {
		if ($isFirst) {
			$content .= "if (window.mm_menu_{$caID}) return;\n";
		}	
						
		if (array_key_exists('divisions', $aCat) and count($aCat['divisions'])) {
			$tempcounter = 1;
			$addItemsJS = '';
			foreach ($aCat['divisions'] as $key => $compare) {
							
				$temp = str_replace("[MenuID]", $caID.'_'.$tempcounter, $newMenuJS.$newMenuItemPropertyJS);
				$temp = str_replace("[MenuName]", ss_JSStringFormat($key), $temp);
				$temp = str_replace("[MenuWidth]",$aCat['menuwidth'], $temp);
				$content .= $temp."\n";
				
				foreach ($displayCategoreis[$caID][$key] as $childIndex) {
					//$aCat['children'][$childIndex]
					$itemTemp = str_replace("[MenuID]", $caID.'_'.$tempcounter, $newMenuItemJS);					
					$itemTemp = str_replace("[ItemID]", $aCat['children'][$childIndex]['ca_id'], $itemTemp);
					$itemTemp = str_replace("[ItemName]", ss_JSStringFormat($aCat['children'][$childIndex]['ca_name']), $itemTemp);									
					//ss_DumpVar($itemTemp, $child['ca_name']);
					$content .= $itemTemp."\n";				
				}				
				
				$addItemsJS .= "mm_menu_{$caID}.addMenuItem(mm_menu_{$caID}_{$tempcounter});\n";
				
				$tempcounter++;
			}
			
			$temp = str_replace("[MenuID]", $caID, $newMenuJS);
			//$temp = str_replace("[MenuName]", ss_JSStringFormat($aCat['details']['ca_name']), $temp);
			$temp = str_replace("[MenuName]", 'root', $temp);
			$temp = str_replace("[MenuWidth]",'60' , $temp);
			$content .= $temp."\n";
			$content .= $addItemsJS."\n";
					
		} else {
			
			$temp = str_replace("[MenuID]", $caID, $newMenuJS);
			//$temp = str_replace("[MenuName]", ss_JSStringFormat($aCat['details']['ca_name']), $temp);
			$temp = str_replace("[MenuName]", 'root', $temp);
			$temp = str_replace("[MenuWidth]",$aCat['menuwidth'] , $temp);
			$content .= $temp."\n";
			
			foreach ($aCat['children'] as $child){								
				
				$itemTemp = str_replace("[MenuID]", $caID, $newMenuItemJS);
				$itemTemp = str_replace("[ItemID]", $child['ca_id'], $itemTemp);
				$itemTemp = str_replace("[ItemName]", ss_JSStringFormat($child['ca_name']), $itemTemp);				
				$content .= $itemTemp."\n";
			}
		}
		$content .= str_replace("[RootID]", $caID, $newMenuRootPropertyJS)."\n\n\n";				
		
		$isFirst = false;
	}
	$content .= "mm_menu_$firstCaID.writeMenus();";
	$content .= "}";
	
	$content = str_replace("[AssetPath]", $assetPath, $content);				
	$fileName = expandPath("Custom/ContentStore/Layouts/Scripts/category_menus.js");
	$fh = fopen($fileName, 'w');		
	fwrite($fh, $content) or die("Could not write to file"); 
	// close file 
	fclose($fh);
	if (!strlen($this->ATTRIBUTES['ca_id'])) {	
		$this->display->title = "Dynamic Drop-down Menu ";
		$this->display->layout = "nolink";
		print ("<p><BR>Menu for the categories on your site has been updated to \"Custom/ContentStore/Layouts/Scripts/category_menus.js\". <BR><BR>Thank you.<p>");
	}	
	//ss_DumpVarDie($content,$fileName);

?>