<?php
requireOnceClass('Administration');
class MediaReleasesAdministration extends Administration {

	function exposeServices() {		
		return	Administration::exposeServicesUsing('MediaReleases');		
	}
	
	function __construct($isAdmin=true) {
		$assetID = null;
		
		if (!strlen($this->assetLink)) {
			if (array_key_exists("as_id", $_REQUEST)) {
				$assetID = $_REQUEST['as_id'];			
			} else if (array_key_exists("assetLink", $_REQUEST)) {
				$assetID = $_REQUEST['assetLink'];			
			}			
		}
		
		parent::__construct(array(
			'prefix'					=>	'MediaReleases',
			'singular'					=>	'Media Release',
			'plural'					=>	'Media Releases',
			'tableName'					=>	'media_releases_releases',
			'tablePrimaryKey'			=>	'rel_id',
			'tableDisplayFields'		=>	array('rel_date', 'rel_title', 'rel_approved'),
			'tableDisplayFieldTitles'	=>	array('Date', 'Title','Online'),
			'tableOrderBy'				=>	array('rel_date DESC, rel_id DESC' => 'Date'),
			'tableAssetLink'			=>	'rel_as_id',
			'assetLink'					=>	$assetID,
		));
		
/*
		$this->setParent(new ParentTable(array(
			'tableName'					=>	'assets',
			'tablePrimaryKey'			=>	'as_id',
			'linkField'					=>	'as_parent_as_id',
		)));
*/
		
		$this->addField(new TextField (array(
			'name'			=>	'rel_title',
			'displayName'	=>	'Title',
			'note'			=>	null,
			'required'		=>	true,
			'verify'		=>	false,
			'unique'		=>	false,
			'size'	=>	'45',	'maxLength'	=>	'255',
			'rows'	=>	'6',	'cols'		=>	'40',
		)));
		

		$this->addField(new MemoField (array(
			'name'			=>	'rel_description',
			'displayName'	=>	'Description',
			'note'			=>	NULL,
			'required'		=>	TRUE,
			'verify'		=>	FALSE,
			'unique'		=>	false,
			'default'		=>	null,
			'size'	=>	'50',	'maxLength'	=>	'255',
			'rows'	=>	'10',	'cols'		=>	'47',
			'width'	=>	'document.body.clientWidth-35',
		)));
				
		$directroy = ss_secretStoreForAsset($assetID,"MediaReleases"); 
		//print($directroy);		
		$this->addField(new FileField (array(
			'name'			=>	'rel_file',
			'displayName'	=>	'Upload File',
			'directory'		=>	$directroy,
			'required'		=>	true,
		)));					
		
		$this->addField(new DateField (array(
			'name'			=>	'rel_date',
			'displayName'	=>	'Date',
			'note'			=>	NULL,			
			'required'		=>	TRUE,
			'class'			=>	'formborder',
			'verify'		=>	FALSE,
			'unique'		=>	FALSE,
			'defaultValue'	=>	'Now',
			'showCalendar'	=> 	TRUE,
			'size'	=>	'10',	'maxLength'	=>	'10',
			'rows'	=>	'6',	'cols'		=>	'47',			
		)));

		if ($isAdmin) {
			$this->addField(new CheckBoxField (array(
				'name'			=>	'rel_approved',
				'displayName'	=>	'Approved',
				'displayValueYes'	=>	'Yes',
				'displayValueNo'	=>	'',
			)));
		}

		
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
