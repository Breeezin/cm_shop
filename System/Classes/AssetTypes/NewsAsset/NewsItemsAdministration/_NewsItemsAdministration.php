<?php
requireOnceClass('Administration');
class NewsItemsAdministration extends Administration {

	function exposeServices() {		
		return array_merge(array(
			'News.Send'	=>	array('method'=>'send'),
			'News.SendOne'	=>	array('method'=>'sendOne'),
		),
		Administration::exposeServicesUsing('NewsItems'));		
	}
	
	function sendOne() {
		require('query_sendOne.php');
		require('view_sendOne.php');	
	}
	
	/*function beforeSend() {
		require('query_beforeSend.php');	
		require('model_beforeSend.php');
		require('view_beforeSend.php');
	}*/
	
	function send() {
		require('query_send.php');	
		require('view_send.php');	
	}
		
	function create() {
		if (ss_optionExists('News Can Spam')) {		
			if (array_key_exists($this->tablePrimaryKey,$this->ATTRIBUTES)) $this->primaryKey = $this->ATTRIBUTES[$this->tablePrimaryKey];
			if (array_key_exists('as_id',$this->ATTRIBUTES)) $this->assetLink = $this->ATTRIBUTES['as_id'];
			require('model_create.php');			
			require('view_create.php');			
		} else {
			parent::create();	
		}
	}
	
	function edit() {		
		if (ss_optionExists('News Can Spam')) {
			if (array_key_exists($this->tablePrimaryKey,$this->ATTRIBUTES)) $this->primaryKey = $this->ATTRIBUTES[$this->tablePrimaryKey];
			if (array_key_exists('as_id',$this->ATTRIBUTES)) $this->assetLink = $this->ATTRIBUTES['as_id'];
	
			require('model_edit.php');		
			require('view_edit.php'); 
		} else {
			parent::edit();	
		}
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
			'prefix'					=>	'NewsItems',
			'singular'					=>	'News Item',
			'plural'					=>	'News Items',
			'tableName'					=>	'news_items',
			'tablePrimaryKey'			=>	'nei_id',
			'tableDisplayFields'		=>	array('nei_timestamp', 'nei_headline'),
			'tableDisplayFieldTitles'	=>	array('Date Time', 'Headline'),
			'tableOrderBy'				=>	array('nei_timestamp DESC, nei_id DESC' => 'Date Time'),
			'tableAssetLink'			=>	'nei_as_id',
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
			'name'			=>	'nei_headline',
			'displayName'	=>	'Headline',
			'note'			=>	NULL,
			'required'		=>	TRUE,
			'verify'		=>	FALSE,
			'unique'		=>	false,
			'size'	=>	'50',	'maxLength'	=>	'255',
			'rows'	=>	'6',	'cols'		=>	'40',
			'linkQueryAction'	=>	NULL,
			'linkQueryValueField'	=>	NULL,
			'linkQueryDisplayField'	=>	NULL,
		)));
		

		$this->addField(new HTMLMemoField2 (array(
			'name'			=>	'nei_body',
			'displayName'	=>	'News Item',
			'note'			=>	NULL,
			'required'		=>	TRUE,
			'verify'		=>	FALSE,
			'unique'		=>	false,
			'default'		=>	null,
			'size'	=>	'50',	'maxLength'	=>	'255',
			'rows'	=>	'6',	'cols'		=>	'40',
			'width'	=>	'document.body.clientWidth-150',
		)));
				
		$directroy = ss_secretStoreForAsset($assetID,"NewsImages"); 
		//print($directroy);		
		$this->addField(new PopupUniqueImageField (array(
			'name'			=>	'nei_image',
			'displayName'	=>	'News Image',
			'directory'		=>	$directroy,
		)));					
		
		if (ss_optionExists('News Date Time')) {
			$this->addField(new DateTimeField (array(
				'name'			=>	'nei_timestamp',
				'displayName'	=>	'News Date',
				'note'			=>	NULL,			
				'required'		=>	TRUE,
				'class'			=>	'formborder',
				'verify'		=>	FALSE,
				'unique'		=>	FALSE,
				'defaultValue'	=>	date('Y-m-d H:i:00',time()),
				'showCalendar'	=> 	TRUE,
				'size'	=>	'6',	'maxLength'	=>	'10',
				'rows'	=>	'6',	'cols'		=>	'40',			
			)));
		} else {
			$this->addField(new DateField (array(
				'name'			=>	'nei_timestamp',
				'displayName'	=>	'News Date/Time',
				'note'			=>	NULL,			
				'required'		=>	TRUE,
				'class'			=>	'formborder',
				'verify'		=>	FALSE,
				'unique'		=>	FALSE,
				'defaultValue'	=>	'Now',
				'showCalendar'	=> 	TRUE,
				'size'	=>	'6',	'maxLength'	=>	'10',
				'rows'	=>	'6',	'cols'		=>	'40',			
			)));
		}

		if (ss_optionExists('News Can Hide')) {
			array_push($this->tableDisplayFields,'nei_hidden');
			array_push($this->tableDisplayFieldTitles,'Hidden?');
			$this->addField(new CheckBoxField(array(
				'name'			=>	'nei_hidden',
				'displayName'	=>	'Hide?',
			)));
		}

		if (ss_optionExists('News Can Spam')) {
			$this->addField(new MultiCheckField (array(
				'name'			=>	'user_groups',
				'displayName'	=>	'Send to selected user groups',
				'note'			=>	NULL,
				'required'		=>	FALSE,
				'verify'		=>	FALSE,
				'unique'		=>	FALSE,
				'size'	=>	'30',	'maxLength'	=>	'40',
				'rows'	=>	'6',	'cols'		=>	'40',
				'linkQueryAction'	=>	'UserGroupsAdministration.Query',
				'linkQueryValueField'	=>	'ug_id',
				'linkQueryDisplayField'	=>	'ug_name',
				'linkTableName'		=>	'news_items_user_groups',
				'linkTableOurKey'	=>	'niu_nei_id',
				'linkTableTheirKey'	=>	'aug_ug_id',
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
