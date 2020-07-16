<?php
class NewsletterArchive extends Plugin {

 
	function inputFilter() {
		parent::inputFilter();
		$this->param('as_id');
		$this->param('BreadCrumbs','Administration');
		$this->display->layout = 'Administration';

		// Must be able to Administer it to access these Actions
		ss_RestrictPermission('CanAdministerAsset',$this->ATTRIBUTES['as_id']);
		
	}	
	
	function exposeServices() {
		return array(
			'NewsletterArchive.List'	=>	array('method'	=>	'list_'),
			'NewsletterArchive.ViewRecipients'	=>	array('method'	=>	'viewRecipients'),
			'NewsletterArchive.UpdateArchiveStatus'	=>	array('method'	=>	'updateArchiveStatus'),
		);
	}

	function viewRecipients() {
		require('query_viewRecipients.php');
		require('view_viewRecipients.php');	
	}

	function updateArchiveStatus() {
		require('model_updateArchiveStatus.php');
		require('view_updateArchiveStatus.php');
	}
	
	function list_() {
		require('query_list.php');
		require('view_list.php');
	} 
}
?>
