<?php
	$this->display->title = 'Import users';

	if (!array_key_exists('DoAction',$this->ATTRIBUTES)) {
		$data = array(
			'Q_UserGroups'	=>	$Q_UserGroups,
		);
	
		$this->useTemplate('ImportUsersPrompt',$data);
	}
?>
