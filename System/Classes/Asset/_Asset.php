<?php

class Asset extends Plugin {

	var $id = NULL;
	var $path = NULL;
	var $pathFixed = FALSE;

	var $layout = array();
	
	var $fieldSet = array();
	
	function __construct() {
		$this->pluginDirectory = dirname(__FILE__);
		parent::__construct();
		//$this->Plugin();
	}

	function loadAsset($loadForEdit = false) {
		require('query_loadAsset.php');
	}
	
	function openercloser() {
		require('model_openercloser.php');
	}
	
	function threestate() {
		require('model_threestate.php');
	}
	
	function twostate() {
		require('model_twostate.php');
	}	
	
	function security() {
		require('model_security.php');
	}
	
	function defineLayoutFields() {
		require('query_defineLayoutFields.php');
	}

	function layoutForm() {
		require('model_layoutForm.php');
	}	
	
	function getID() {
		// If the ID is already known.. return it.
		if ($this->id != NULL) {
			return $this->id;
		}
		// If the id is not known, then query for the id
		$result = new Request('Asset.IDFromPath',array('AssetPath' => $this->path));
		$this->id = $result->value;

		return $result->value;
	}

	function getIDFromPath() {
		$this->cache = 'Application';
		$this->display->layout = 'None';
		return include('query_getIDFromPath.php');
	}
	
	function getPath() {
		// If the path is already known.. return it.
		if ($this->path != NULL) {
		
			// Fix up the path if it's missing stuff
			if (!$this->pathFixed) {
				if ($this->path{0} != '/') {
					$this->path = '/'.$this->path;
				}
				$this->path = rtrim($this->path,'/');
				while (strpos($this->path,'//') !== FALSE) {
					$this->path = str_replace('//','/',$this->path);
				}
				$this->pathFixed = TRUE;
			}
		
			return $this->path;
		}
		// If the path is known, then query for the path
		if ($this->id != NULL) {
			$result = new Request('Asset.PathFromID',array('as_id' => $this->id));
			$this->path = $result->value;
			return $this->path;
		} else {
			// Otherwise.. return NULL
			return NULL;
		}
	}

	function getPathFromID() {
		$this->cache = 'Application';
		$this->display->layout = 'None';
		return include('query_getPathFromID.php');
	}
	
	function getAncestorsFromID() {
		$this->cache = 'Application';
		$this->display->layout = 'None';
		return include('query_getAncestorsFromID.php');
	}
	function query() {
		
	}
	
	function treeStructure() {
//		$this->cache = 'Application';
		$this->display->layout = 'None';
		return include('query_treeStructure.php');
	}
	
	function tree() {
		$this->display->layout = 'None';
		
		if (array_key_exists('Layout', $this->ATTRIBUTES)) $this->display->layout = $this->ATTRIBUTES['Layout'];
		
		ss_disableDebugOutput();
		require('query_tree.php');
		require('view_tree.php');
	}

	function treePart() {
		$this->display->layout = 'None';
		require('query_treePart.php');
		require('view_treePart.php');
	}

	
	function generateTree($root,$path,$currentDepth = 0) {
		return include('query_generateTree.php');
	}
	
	function display() {
		$this->cache = 'Application';
		require('query_display.php');
		require('view_display.php');		
	}
	function getAssetInfo () {
		$this->cache = 'Application';
		$this->display->layout = 'None';		
		require('query_getAssetInfo.php');		
		require('view_getAssetInfo.php');		
	}
	function embed() {
		$this->cache = 'Application';
		$this->display->layout = 'None';
		require('query_embed.php');
		require('view_embed.php');		
	}
	function embedImage() {
		$this->cache = 'Application';
		$this->display->layout = 'None';
		require('query_embedImage.php');
		require('view_embedImage.php');		
	}

	// Add a field into this Administration items field set
	function addField(&$field) {
		$this->fieldSet[$field->name] = $field;
	}
	
	function edit() {
		$this->display->layout = 'AdministrationTabbedPage';
		require('query_edit.php');
		require('model_edit.php');
		require('view_edit.php');
	}
	
	function add() {
		require('query_add.php');
		require('model_add.php');
		return include('view_add.php');
	}
	function addMany() {
		require('query_addMany.php');
		require('model_addMany.php');
		require('view_addMany.php');
	}
	function move() {
		require('query_move.php');
		require('model_move.php');
		require('view_move.php');
	}
	function copy() {
		require('query_copy.php');
		require('model_copy.php');
		require('view_copy.php');
	}
	function copyTree() {	
		require('model_copyTree.php');	
	}
	
	function createTree() {	
		require('model_createTree.php');	
	}
	
	function updateOrder() {
		require('model_updateOrder.php');	
	}
	
	function delete() {		
		require('model_delete.php');
		require('view_delete.php');
	}
	
	function displayType() {
		// This method should be overridden by the asset type
		// so the asset type knows how to display itself
	}
	
	function childrenRefresh() {
		require('query_childrenRefresh.php');
	}
	
	function propertiesPanel() {
		require('query_propertiesPanel.php');	
	}

	function updateNameAndAppearsInMenus() {
		require('model_updateNameAndAppearsInMenus.php');	
	}
	
	function addFromTree() {
		require('model_addFromTree.php');
	}

	function deleteFromTree() {
		require('model_deleteFromTree.php');
	}	
	
	function exposeServices() {
		$prefix = 'Asset';
		return array(
			"$prefix.Display"		=>	array('method'	=>	'display'),
			"$prefix.Embed"			=>	array('method'	=>	'embed'),
			"$prefix.GetInfo"		=>	array('method'	=>	'getAssetInfo'),
			"$prefix.EmbedImage"	=>	array('method'	=>	'embedImage'),
			"$prefix.IDFromPath"	=>	array('method'	=>	'getIDFromPath'),
			"$prefix.PathFromID"	=>	array('method'	=>	'getPathFromID'),
			"$prefix.AncestorsFromID"	=>	array('method'	=>	'getAncestorsFromID'),
			"$prefix.TreeStructure"	=>	array('method'	=>	'treeStructure'),
			"$prefix.Tree"		=>	array('method'	=>	'tree'),
			"$prefix.TreePart"	=>	array('method'	=>	'treePart'),
			"$prefix.Edit"		=>	array('method'	=>	'edit'),
			"$prefix.Add"		=>	array('method'	=>	'add'),
			"$prefix.Move"		=>	array('method'	=>	'move'),
			"$prefix.CopyTree"		=>	array('method'	=>	'copyTree'),
			"$prefix.CreateTree"		=>	array('method'	=>	'createTree'),
			"$prefix.Copy"		=>	array('method'	=>	'copy'),
			"$prefix.AddMany"		=>	array('method'	=>	'addMany'),
			"$prefix.Delete"		=>	array('method'	=>	'delete'),
			"$prefix.OpenerCloser" => array('method'	=>	'openercloser'),
			"$prefix.ThreeState" => array('method'	=>	'threestate'),
			"$prefix.TwoState" => array('method'	=>	'twostate'),
			"$prefix.Security" => array('method'	=>	'security'),
			"$prefix.LayoutForm" => array('method'	=>	'layoutForm'),
			"$prefix.ChildrenRefresh" => array('method'	=>	'childrenRefresh'),
			"$prefix.UpdateOrder" => array('method'	=>	'updateOrder'),
			"$prefix.PropertiesPanel" => array('method'	=>	'propertiesPanel'),
			"$prefix.UpdateNameAndAppearsInMenus" => array('method' => 'updateNameAndAppearsInMenus'),
			"$prefix.AddFromTree"	=>	array('method'	=>	'addFromTree'),
			"$prefix.DeleteFromTree" =>	array('method'	=>	'deleteFromTree'),
		);
	}
}


?>
