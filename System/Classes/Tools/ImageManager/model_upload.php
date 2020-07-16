<?php
	$this->param("NewFile", "");
	$this->param("Directory", "");
	if (is_uploaded_file($_FILES['NewFile']['tmp_name'])) {
		move_uploaded_file($_FILES['NewFile']['tmp_name'], expandPath("{$this->ATTRIBUTES['Directory']}/{$_FILES['NewFile']['name']}"));
	}
	rfaReturn();
?>