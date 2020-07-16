<?php
class Review extends Plugin {

	function inputFilter() {
		parent::inputFilter();
		$result = new Request('Security.Authenticate',array(
			'Permission'	=>	'CanAdministerAtLeastOneAsset',
		));	
	}
	
	function exposeServices() {
		return array(
			'Review.List'	=>	array('method'	=>	'entries'),
			'Review.ReviewAsset'	=>	array('method'	=>	'reviewAsset'),
		);
	}

	function entries() {
		$this->display->layout = 'Administration';
		require('query_entries.php');
		require('view_entries.php');
	}
	
	function reviewAsset() {
		$this->display->layout = 'Administration';
		require('query_reviewAsset.php');
		require('model_reviewAsset.php');
		require('view_reviewAsset.php');
	}

}
?>
