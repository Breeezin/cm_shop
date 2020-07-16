<?php
requireOnceClass('Administration');
class NewslettersAdministration extends Administration {

	function exposeServices() {
		return array_merge(
			array(
				'Newsletter.BeforeSend'	=>	array('method'	=>	'beforeSend'),
				'Newsletter.Send'	=>	array('method'	=>	'send'),
				'Newsletter.SendOne'	=>	array('method'	=>	'sendOne'),
				'Newsletter.SendPreview'	=>	array('method'	=>	'sendPreview'),
				'Newsletter.Preview'	=>	array('method'	=>	'preview'),
				'Newsletter.Recipients'	=>	array('method'	=>	'recipients'),
			),
			Administration::exposeServicesUsing('Newsletters')
		);
	}

	function sendOne() {
		$rootFolder = str_replace('index.php','',$_SERVER['SCRIPT_FILENAME']);
		$customFolder = $rootFolder.'Custom/Classes/NewslettersAdministration';
	
		foreach(array('query','model','view') as $prefix) {
			$name = $prefix.'_sendOne.php';
			if (file_exists($customFolder.'/'.$name)) {
				include($customFolder."/".$name);
			} else if (file_exists(dirname(__FILE__).'/'.$name)) {
				include($name);
			}
		}				
	}
	
	function beforeSend() {
		$rootFolder = str_replace('index.php','',$_SERVER['SCRIPT_FILENAME']);
		$customFolder = $rootFolder.'Custom/Classes/NewslettersAdministration';
	
		foreach(array('query','model','view') as $prefix) {
			$name = $prefix.'_beforeSend.php';
			if (file_exists($customFolder.'/'.$name)) {
				include($customFolder."/".$name);
			} else if (file_exists(dirname(__FILE__).'/'.$name)) {
				include($name);
			}
		}				
	}
	
	function send() {
		$rootFolder = str_replace('index.php','',$_SERVER['SCRIPT_FILENAME']);
		$customFolder = $rootFolder.'Custom/Classes/NewslettersAdministration';
	
		foreach(array('query','model','view') as $prefix) {
			$name = $prefix.'_send.php';
			if (file_exists($customFolder.'/'.$name)) {
				include($customFolder."/".$name);
			} else if (file_exists(dirname(__FILE__).'/'.$name)) {
				include($name);
			}
		}				
	}

	function sendPreview() {
		require('query_sendPreview.php');
	}
	
	function preview() {
		require('query_preview.php');	
	}

	function recipients() {
	
		$rootFolder = str_replace('index.php','',$_SERVER['SCRIPT_FILENAME']);
		$customFolder = $rootFolder.'Custom/Classes/NewslettersAdministration';
	
		foreach(array('query','model','view') as $prefix) {
			$name = $prefix.'_recipients.php';
			if (file_exists($customFolder.'/'.$name)) {
				include($customFolder."/".$name);
			} else if (file_exists(dirname(__FILE__).'/'.$name)) {
				include($name);
			}
		}	
	}
	
	
	function sendNewsletter($newsletter,$Q_Recipients,$archiveID = null,$redirect = null) {
		require('model_sendNewsletter.php');
	}
	
	function sendOldNewsletter($newsletter,$Q_Recipients,$archiveID = null,$redirect = null) {
		require('model_sendNewsletterOld.php');
	}

	function form($errors,$tableTags = true, $isForm = true, $formTemplate = 'Form') {
		require('query_form.php');		
		return include('view_form.php');		
	}
	
	function create() {		
		if (array_key_exists($this->tablePrimaryKey,$this->ATTRIBUTES)) $this->primaryKey = $this->ATTRIBUTES[$this->tablePrimaryKey];
		require('model_create.php');			
		require('view_create.php');			
	}
	
	function edit() {		
		if (array_key_exists($this->tablePrimaryKey,$this->ATTRIBUTES)) $this->primaryKey = $this->ATTRIBUTES[$this->tablePrimaryKey];
		require('model_edit.php');		
		require('view_edit.php'); 
	}
	
	function __construct() {
		
		parent::__construct(array(
			'prefix'					=>	'Newsletters',
			'singular'					=>	'Newsletter',
			'plural'					=>	'Newsletters',
			'tableName'					=>	'newsletters',
			'tablePrimaryKey'			=>	'nl_id',
			'tableDisplayFields'		=>	array('nl_last_modified', 'nl_subject'),
			'tableDisplayFieldTitles'		=>	array('Last Modified', 'Subject'),
			'tableOrderBy'				=>	array('nl_last_modified DESC' => 'Last Modified'),
			'tableTimeStamp'			=>	'nl_last_modified',
		));
		
/*		$this->setParent(new ParentTable(array(
			'tableName'					=>	'assets',
			'tablePrimaryKey'			=>	'as_id',
			'linkField'					=>	'as_parent_as_id',
		)));*/

		$this->addField(new EmailField (array(
			'name'			=>	'nl_sender_email',
			'displayName'	=>	'From',
			'note'			=>	null,
			'required'		=>	true,
			'verify'		=>	false,
			'unique'		=>	false,
			'defaultValue'	=>	$GLOBALS['cfg']['EmailAddress'],
			'size'	=>	'30',	'maxLength'	=>	'255',
			'rows'	=>	'6',	'cols'		=>	'40',
		)));


		$this->addField(new MultiCheckField (array(
			'name'			=>	'Recipients',
			'displayName'	=>	'To',
			'note'			=>	null,
			'required'		=>	ss_optionExists('Newsletter User Group Not Required')?false:true,
			'verify'		=>	false,
			'unique'		=>	false,
			'size'	=>	'30',	'maxLength'	=>	'40',
			'rows'	=>	'6',	'cols'		=>	'40',
			'columns'	=>	1,
			'linkQueryAction'	=>	'UserGroupsAdministration.Query',
			'linkQueryValueField'	=>	'ug_id',
			'linkQueryDisplayField'	=>	'ug_name',
			'linkQueryParameters'	=>	array('FilterSQL'	=>	'AND ug_mailing_list IS NOT NULL'),
			'linkTableName'		=>	'newsletter_recipient_user_groups',
			'linkTableOurKey'	=>	'nrug_nl_id ',
			'linkTableTheirKey'	=>	'aug_ug_id',
		)));
			
		$rootFolder = str_replace('index.php','',$_SERVER['SCRIPT_FILENAME']);
		$customFolder = $rootFolder.'Custom/Classes/NewslettersAdministration';		
		$name = 'inc_extraFields.php';
		if (file_exists($customFolder.'/'.$name)) {			
			include($customFolder."/".$name);
		}
		
		// This should really scan the newsletter templates folder....... ;-)
		//"Custom/ContentStore/Templates/{$GLOBALS['cfg']['currentSiteFolder']}NewslettersAdministration/
		$templates = array();	
		$dir = expandPath("Custom/ContentStore/Templates/{$GLOBALS['cfg']['currentSiteFolder']}NewslettersAdministration/");
		$dh=opendir($dir);		
		while ($file=readdir($dh)){
			if($file!="." && $file!=".."){
				$fullpath=$dir."/".$file;
				if(!is_dir($fullpath)){
					if (ListLast($file, ".") == 'html') 
					array_push($templates, ListFirst($file, "."));
				}
			}
		}
		closedir($dh);
									
		$this->addField(new RestrictedTextField (array(
			'name'			=>	'nl_template',
			'displayName'	=>	'Template',
			'note'			=>	NULL,
			'required'		=>	TRUE,
			'verify'		=>	FALSE,
			'unique'		=>	FALSE,
			'size'	=>	'30',	'maxLength'	=>	'255',
			'rows'	=>	'6',	'cols'		=>	'40',
			'options'	=>	$templates,
		)));
		
		$this->addField(new TextField (array(
			'name'			=>	'nl_subject',
			'displayName'	=>	'Subject',
			'note'			=>	NULL,
			'required'		=>	TRUE,
			'verify'		=>	FALSE,
			'unique'		=>	TRUE,
			'size'	=>	'50',	'maxLength'	=>	'255',
			'rows'	=>	'6',	'cols'		=>	'40',
			'linkQueryAction'	=>	NULL,
			'linkQueryValueField'	=>	NULL,
			'linkQueryDisplayField'	=>	NULL,
		)));

		$this->addField(new MemoField (array(
			'name'			=>	'nl_textmessage',
			'displayName'	=>	'Text Only Message',
			'note'			=>	NULL,
			'required'		=>	TRUE,
			'verify'		=>	FALSE,
			'unique'		=>	false,
			'defaultValue'	=>	'---
You have indicated you only wish to view text based emails.
To view this newsletter online, please copy the link below into your web browser.
[NewsletterLink]',
			'size'	=>	'70',	'maxLength'	=>	'255',
			'rows'	=>	'8',	'cols'		=>	'70',
			'style'	=>	'width:100%',
		)));

		$this->addField(new HTMLMemoField2 (array(
			'name'			=>	'nl_html_message',
			'displayName'	=>	'HTML Message',
			'note'			=>	'If you archive your newsletter, it is the HTML message that will appear in the archive, not the "Text Only" message',
			'required'		=>	true,
			'verify'		=>	false,
			'unique'		=>	false,
			'default'		=>	null,
			'size'	=>	'50',	'maxLength'	=>	'255',
			'rows'	=>	'6',	'cols'		=>	'40',
			'width'	=>	'document.body.clientWidth-35',
		)));
		
		if (ss_optionExists('Newsletter Two Content Areas')) {
			$this->addField(new HTMLMemoField2 (array(
				'name'			=>	'nl_html_message2',
				'displayName'	=>	'HTML Message (Secondary Content)',
				'note'			=>	null,
				'required'		=>	false,
				'verify'		=>	false,
				'unique'		=>	false,
				'default'		=>	null,
				'size'	=>	'50',	'maxLength'	=>	'255',
				'rows'	=>	'6',	'cols'		=>	'40',
				'width'	=>	'document.body.clientWidth-35',
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
