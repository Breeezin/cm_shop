<?php
requireOnceClass('AssetTypes');
requireOnceClass('FieldSet');

class FolderAsset extends AssetTypes {
	
	var $fieldPrefix = 'AST_FOLDER_';
	
	function getClassName() {
		return ListLast(dirname(__FILE__),"/");
	}
	
	function displaySubAssets($parentPath,$parent,$appearsInMenusSQL,$maxDepth,$depth = 0) {
		
		$whereSQL = '';
		
		if (ss_optionExists("Schedule assets")) {
			$whereSQL .= " AND (AssetOnline IS NULL OR AssetOnline = '' OR (AssetOnline = 'Date' AND AssetOnlineDate < NOW()) )";
			$whereSQL .= " AND (AssetOffline IS NULL OR AssetOffline = '' OR (AssetOffline = 'Date' AND AssetOfflineDate > NOW()) )";
		}
	
		
		$Q_SubAssets = query("
			SELECT * FROM assets
			WHERE as_parent_as_id = $parent
				AND (as_deleted = 0 OR as_deleted IS NULL)
			$appearsInMenusSQL
			ORDER BY as_sort_order, as_name
		");
		
		$folderLinks = "<ul>";
		while ($row = $Q_SubAssets->fetchRow()) {
			$link = ss_EscapeAssetPath(ss_withoutPreceedingSlash($parentPath)."/{$row['as_name']}");
			$assetName = $row['as_name'];
			if ($row['as_menu_name'] !== null) $assetName = $row['as_menu_name'];
			$folderLinks .= "<li><a  class=\"folderLinks\" href=\"$link\">".ss_HTMLEditFormat($assetName)."</a></li>";
			if ($depth < $maxDepth) {
				$folderLinks .= $this->displaySubAssets("{$parentPath}/{$row['as_name']}",$row['as_id'],$appearsInMenusSQL,$maxDepth,$depth+1);
			}
		}
		$folderLinks .= "</ul>";	
		return $folderLinks;
	}
	
	function embed(&$asset) {
		$this->display($asset);
	}

	function display(&$asset) {
		ss_paramKey($asset->cereal,$this->fieldPrefix.'DEFAULT');
		ss_paramKey($asset->cereal,$this->fieldPrefix.'ONLY_APPEARS_IN_MENUS');
		ss_paramKey($asset->cereal,$this->fieldPrefix.'CONTENT');
		ss_paramKey($asset->cereal,$this->fieldPrefix.'SUB_LEVELS',0);
		
		if (array_key_exists('multiSiteHomes',$GLOBALS['cfg']) and $asset->getID() == 1) {
			// This is a multi site and we're in the index.php folder so redirect to the
			// correct folder based on settings in GlobalSettings.php
			//ss_DumpVar($GLOBALS['cfg']);
			
			locationRelative(ss_EscapeAssetPath($GLOBALS['cfg']['multiSiteHomes'][rtrim($GLOBALS['cfg']['currentSiteFolder'],'/')]));
			
		} else if ($asset->cereal[$this->fieldPrefix.'DEFAULT'] != null) {
		
			$result = new Request('Asset.PathFromID',
				array('as_id' => $asset->cereal[$this->fieldPrefix.'DEFAULT']));
			
			if ($result->value != NULL) {
				// Send the browser off to the default asset for this folder
				locationRelative(ss_EscapeAssetPath(ltrim($result->value,'/')));
			} else {
				print ('Could not find default asset for folder.');
			}
		} else {
			$display = ss_parseText($asset->cereal[$this->fieldPrefix.'CONTENT']);
			
			$appearsInMenusSQL = "";
			if ($asset->cereal[$this->fieldPrefix.'ONLY_APPEARS_IN_MENUS']) {
				$appearsInMenusSQL = "AND as_appear_in_menus = 1";	
			}
			
			$folderLinks = $this->displaySubAssets($asset->getPath(),$asset->getID(),$appearsInMenusSQL,$asset->cereal[$this->fieldPrefix.'SUB_LEVELS']);
			
			/*$Q_SubAssets = query("
				SELECT * FROM assets
				WHERE as_parent_as_id = ".$asset->getID()."
					AND (as_deleted = 0 OR as_deleted IS NULL)
				$appearsInMenusSQL
				ORDER BY as_sort_order, as_name
			");
			
			$folderLinks = "<ul>";
			while ($row = $Q_SubAssets->fetchRow()) {
				$link = ss_EscapeAssetPath(ss_withoutPreceedingSlash($asset->getPath())."/{$row['as_name']}");
				$assetName = $row['as_name'];
				if ($row['as_menu_name'] !== null) $assetName = $row['as_menu_name'];
				$folderLinks .= "<li><a href=\"$link\">".ss_HTMLEditFormat($assetName)."</a></li>";
			}
			$folderLinks .= "</ul>";*/
			
			if (strpos(strtolower($display),strtolower('[FolderLinks]')) !== false) {
				$display = stri_replace('[FolderLinks]',$folderLinks,$display);
			} else {
				$display.= $folderLinks;	
			}
			print($display);
		}
	}
	
	function defineFields(&$asset) {
		$this->fieldSet = new FieldSet(array(
			'formName'	=>	'AssetForm',
		));
/*		
		$this->fieldSet->addField(new IntegerField(array(
					'name'			=>	$this->fieldPrefix.'DEFAULT',
					'displayName'	=>	'Target',
					'size'	=>	'60',	'maxLength'	=>	'255',
		)));
*/		
		$this->fieldSet->addField(new AssetTreeField(array(
						'name'			=>	$this->fieldPrefix.'DEFAULT',
						'displayName'	=>	'Target',
						'required'		=>	false,
						'size'	=>	'60',	'maxLength'	=>	'255',
						'onFocus'		=> 'onFocus="this.blue()"',			
						'treeProperty'   => array('openerFormName'=>'AssetForm',
												  'treeDescription'=>'Please select the root asset to redirect.',
												  'treeAssetRootID'=>'1',
												  'treeStyle'=>'width:260;height:300; overflow:auto;border:solid black 1px;',
												  'appearsInMenus'=>'No',
												  'includeChildrenOf'=>array(ss_systemAsset('index.php') => 1),
												  'excludeAssets'=>array(),
												  'excludeChildrenOf'=>array(),
												  'appearsInMenus'=>'No',),
						'treePopWindowProperty' => 'width=300,height=350,scrollbar=1',
					)));

		$this->fieldSet->addField(new CheckBoxField(array(
					'name'			=>	$this->fieldPrefix.'ONLY_APPEARS_IN_MENUS',
					'displayName'	=>	'Only Appears In Menus assets?',
		)));

		$this->fieldSet->addField(new IntegerField(array(
					'name'			=>	$this->fieldPrefix.'SUB_LEVELS',
					'displayName'	=>	'Sub Levels',
					'maxLength'		=>	5,
					'size'	=>	5,
		)));

		$this->fieldSet->addField(new HtmlMemoField2(array(
					'name'			=>	$this->fieldPrefix.'CONTENT',
					'displayName'	=>	'Message',
					'note'			=>	NULL,
					'required'		=>	FALSE,
					'verify'		=>	FALSE,
					'unique'		=>	FALSE,
					'size'	=>	'30',	'maxLength'	=>	'127',
					'rows'	=>	'6',	'cols'		=>	'40',
					'linkQueryAction'	=>	NULL,
					'linkQueryValueField'	=>	NULL,
					'linkQueryDisplayField'	=>	NULL,
					'Directory' => "Custom/ContentStore/Layouts/Images/",
		)));
	}
		
	function edit(&$asset) {
		print("<p>Select the asset that this folder should redirect to: ");
		$this->fieldSet->displayField($this->fieldPrefix.'DEFAULT');
		print("</p>");
		print("<p>If you do not specify an asset above, this folder asset will list all sub assets of this folder.  You can select whether to display all sub assets or only those assets that appear in the menus. If you wish to only display assets that appear in menus, please tick this box: ");
		$this->fieldSet->displayField($this->fieldPrefix.'ONLY_APPEARS_IN_MENUS');
		print("</p>");
		if (ss_optionExists('Folder Sub Levels')) {
			print("<p>Enter the number of levels of sub assets that should be displayed. The default is 0, which means only display the direct sub items of this folder: ");
			$this->fieldSet->displayField($this->fieldPrefix.'SUB_LEVELS');
			print("</p>");
		}
		print("<p>Please enter the text that should be displayed above the list of assets. If you would like the links to appear inside your content, please type [FolderLinks] (including the square brackets) where you would like the links to be displayed.</p>");
		$this->fieldSet->displayField($this->fieldPrefix.'CONTENT');
	}
	
}


?>
