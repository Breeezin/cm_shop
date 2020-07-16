<?php
requireOnceClass('Administration');
class SubmissionsAdministration extends Administration {

	function exposeServices() {		
		return	Administration::exposeServicesUsing('Submissions');		
	}
	
	function entries() {	
		if (array_key_exists($this->tablePrimaryKey,$this->ATTRIBUTES)) $this->primaryKey = $this->ATTRIBUTES[$this->tablePrimaryKey];
		require('EntriesQuery.php');		
		require('EntriesDisplay.php');	
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
	
	function __construct() {
		$assetID = null;
		
		if (!strlen($this->assetLink)) {
			if (array_key_exists("as_id", $_REQUEST)) {
				$assetID = $_REQUEST['as_id'];			
			} else if (array_key_exists("assetLink", $_REQUEST)) {
				$assetID = $_REQUEST['assetLink'];			
			}			
		}
		
		parent::__construct(array(
			'prefix'					=>	'Submissions',
			'singular'					=>	'Enquiry',
			'plural'					=>	'Enquiries',
			'tableName'					=>	'email_form_submissions',
			'tablePrimaryKey'			=>	'efs_id',
			'tableDisplayFields'		=>	array('efs_id','efs_timestamp', 'efs_name', 'efs_email_address'),
			'tableDisplayFieldTitles'	=>	array('ID','Date Time', 'Name', 'Email Address'),
			'tableOrderBy'				=>	array('efs_timestamp DESC' => 'Date Time'),
			'tableAssetLink'			=>	'efs_as_id',
			'assetLink'					=>	$assetID,
			'hideNewButton'				=>	' ',
			'backButtonText'			=>	'Back',
			'tableSearchFields'			=>	array('efs_text','efs_email_address','efs_timestamp','efs_id','efs_name'),
		));
		
/*
		$this->setParent(new ParentTable(array(
			'tableName'					=>	'assets',
			'tablePrimaryKey'			=>	'as_id',
			'linkField'					=>	'as_parent_as_id',
		)));
*/
	
		$this->addField(new HiddenField(array(
			'name'	=>	'efs_text',
		)));
		$this->addField(new HiddenField(array(
			'name'	=>	'efs_timestamp',
		)));
		$this->addField(new HiddenField(array(
			'name'	=>	'efs_email_address',
		)));
		
/*		$this->addChild(new ChildTable (array(
			'prefix'					=>	'assets',
			'plural'					=>	'Sub assets',
			'singular'					=>	'Sub Asset',
			'tableName'					=>	'assets',
			'tablePrimaryKey'			=>	'as_id',
			'linkField'					=>	'as_parent_as_id'
		)));*/
		
	}

}
?>
