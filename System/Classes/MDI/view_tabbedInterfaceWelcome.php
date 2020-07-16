<?php

	$this->display->title = '';
	$this->display->layout = 'AdministrationTabbedPage';
	
	$data = array();
	
	if ($pageCount['PageCount'] >= $pageAssetType['at_limit'] and strlen($pageAssetType['at_limit'])) {
		$data['limitInfo'] = "You currently have {$pageCount['PageCount']} pages and your website is limited to {$pageAssetType['at_limit']}.  If you would like to create more pages, please contact your website developer.";
	} else {
		$data['limitInfo'] = "";
	}
	
	timerStart('Welcome');
	$welcomeData = $this->processTemplate('Welcome',$data);
	timerFinish('Welcome');
	
	print($welcomeData);

?>