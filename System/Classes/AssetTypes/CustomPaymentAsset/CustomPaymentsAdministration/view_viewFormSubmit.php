<?php 
	$this->param('CuPaID');
	$this->param('BreadCrumbs');
	
	$theForm = getRow("SELECT CuPaEmailContent FROM CustomPayments WHERE CuPaID = {$this->ATTRIBUTES['CuPaID']}");
	$this->display->title = $this->ATTRIBUTES['BreadCrumbs'].': View Detail';
	
	print $theForm['CuPaEmailContent'];
?>