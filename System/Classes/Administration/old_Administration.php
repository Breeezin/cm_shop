<?php
requireOnceClass('FieldSet');
class Administration extends FieldSet {

	var $children = array();
	var $prefix;
	var $plural;
	var $singular;
	var $tableDeleteFlag = NULL;
	var $tablePrefix = '';
	var $tableOrderBy = array();
	var $tableSpecialOrderBy = array();
	var $tableDisplayFields;
	var $tableDisplayFieldTitles = NULL;
	var $tablePrimaryMinValue = null;
	var $linkedTables = array();	
	var $tableSearchFields = array();
	var $tableSearchFieldsFromOption = array();
	var $listManageOptions = array();
	var $hideNewButton = null;
	var $tableSortOrderField = null;
	var $hiddenValues = null;
	var $backButtonText = null;
	var $filterByMulti = array();
	var $querySQLFilter = array();
	var $joinTables = null;
	var $joinConditions = null;
	var $dateFieldsFormat = array('Fields'=>array(), 'Format'=>'');
	
	function Administration($settings) {
		$this->Plugin();
		foreach($settings as $property => $value) $this->{$property} = $value;
		
	}

	function inputFilter() {
		parent::inputFilter();
		$this->param('BreadCrumbs','Administration');
		$this->display->layout = 'Administration';
		// Must be able to Administer something to access these Actions
			
		$result = new Request('Security.Authenticate',array(
				'Permission'	=>	'CanAdministerAtLeastOneAsset',
		));
		
	}
	
	// Set the parent table for this Administration plugin
	function setParent($parent) {
		$this->parentTable = $parent;
	}
	
	// Add a child table into this Administration items children set
	function addChild($child) {
		$this->children[$child->prefix] = $child;
	}
	
	function query($params = array()) {
		return include('QueryQuery.php');
	}
	
	function create() {	
		//ss_DumpVarDie($this, "create");	
		if (array_key_exists($this->tablePrimaryKey,$this->ATTRIBUTES)) $this->primaryKey = $this->ATTRIBUTES[$this->tablePrimaryKey];
		if ($this->parentTable !== null and array_key_exists($this->parentTable->linkField,$this->ATTRIBUTES)) $this->parentKey = $this->ATTRIBUTES[$this->parentTable->linkField];
		if (array_key_exists('as_id',$this->ATTRIBUTES)) $this->assetLink = $this->ATTRIBUTES['as_id'];
		require('CreateAction.php');			
		require('CreateDisplay.php');			
	}
	
	function entries() {	
		
		if (array_key_exists($this->tablePrimaryKey,$this->ATTRIBUTES)) $this->primaryKey = $this->ATTRIBUTES[$this->tablePrimaryKey];
		require('EntriesQuery.php');		
		require('EntriesDisplay.php');	
	
	}
	function sorting() {	
		
		if (array_key_exists($this->tablePrimaryKey,$this->ATTRIBUTES)) $this->primaryKey = $this->ATTRIBUTES[$this->tablePrimaryKey];
		
		require('SortingQuery.php');		
		require('SortingAction.php');		
		require('SortingDisplay.php');	
	}
	
	function form($errors, $tableTags=true, $isForm = true, $formTemplate = 'Form') {
		require('FormQuery.php');		
		return include('FormDisplay.php');
	}
		
	function edit() {		
		if (array_key_exists($this->tablePrimaryKey,$this->ATTRIBUTES)) $this->primaryKey = $this->ATTRIBUTES[$this->tablePrimaryKey];		
		if (array_key_exists('as_id',$this->ATTRIBUTES)) $this->assetLink = $this->ATTRIBUTES['as_id'];
		
		require('EditAction.php');		
		require('EditDisplay.php'); 
	}
	
	function delete() {
		require('DeleteAction.php');
	}
	
	function insertAction() {
		$this->loadFieldValuesFromForm($this->ATTRIBUTES);
		$errors = $this->insert();

		if (count($errors) != 0) {
			return $errors;
		} else {
			return $this->primaryKey;
		}
	}
	function updateAction() {
		if (array_key_exists($this->tablePrimaryKey,$this->ATTRIBUTES)) $this->primaryKey = $this->ATTRIBUTES[$this->tablePrimaryKey];		
		if (array_key_exists('as_id',$this->ATTRIBUTES)) $this->assetLink = $this->ATTRIBUTES['as_id'];
		
		$this->loadFieldValuesFromForm($this->ATTRIBUTES);
		$errors = $this->update();

		if (count($errors) != 0) {
			return $errors;
		} else {
			return null;
		}
	}
	
	function exposeServicesUsing($prefix) {
		return array(
			"${prefix}Administration.List"		=>	array('method'	=>	'entries'),
			"${prefix}Administration.Insert"	=>	array('method'	=>	'insertAction'),
			"${prefix}Administration.Update"	=>	array('method'	=>	'updateAction'),
			"${prefix}Administration.New"		=>	array('method'	=>	'create'),
			"${prefix}Administration.Edit"		=>	array('method'	=>	'edit'),
			"${prefix}Administration.Delete"	=>	array('method'	=>	'delete'),
			"${prefix}Administration.Form"		=>	array('method'	=>	'form'),
			"${prefix}Administration.Query"		=>	array('method'	=>	'query'),
			"${prefix}Administration.Sorting"		=>	array('method'	=>	'sorting'),
		);
	}
	
	function addLinkedTable($link) {
		$this->linkedTables[] = $link;
	}
	
	function processTemplate($template,&$data,$custom = array(), $fileType = 'html') {
		
		$templateFile = "{$this->classDirectory}/Templates/{$template}.$fileType";
		//ss_DumpVarDie($this,$templateFile, true);
		$className = strtoupper(substr(get_class($this), 0,1)).substr(get_class($this),1,(strlen(get_class($this))-1));
		$className = str_replace('administration', 'Administration',$className);
		
		//ss_DumpVarDie($className);
		$customTemplate = 'Custom/ContentStore/Templates/'.$GLOBALS['cfg']['currentSiteFolder'].$className.'/'.$template;
		if (file_exists(expandPath($customTemplate.'.'.$fileType))) {
			$templateFile = $customTemplate.'.'.$fileType;									
		}
		// Use the template from System/Classes/Administration/Templates if 
		// there is no custom ones.
		if (!file_exists(expandPath($templateFile))) {
			$templateFile = "System/Classes/Administration/Templates/{$template}.$fileType";
		}		
		
		$useCustomImagesFolder = null;
		if (file_exists(expandPath($customTemplate.'.php'))) $useCustomImagesFolder = get_class($this);							

		return processTemplate($templateFile,$data,$custom,$useCustomImagesFolder);
	}	
	
}

class ParentTable {

	var $tableName;
	var $tablePrimaryKey;
	var $linkField;

	function ParentTable($settings) {
		foreach($settings as $property => $value) $this->{$property} = $value;
	}

}

class LinkedTable {

	var $tableName;
	var $ourKey;
	
	function LinkedTable($settings) {
		foreach($settings as $property => $value) $this->{$property} = $value;
	}
	
}

class ChildTable {

	var $prefix;
	var $plural;
	var $singular;
	var $tableName;
	var $tablePrimaryKey;
	var $linkField;
	var $tableAssetLink = null;
		
	function ChildTable($settings) {
		foreach($settings as $property => $value) $this->{$property} = $value;
	}

}

?>