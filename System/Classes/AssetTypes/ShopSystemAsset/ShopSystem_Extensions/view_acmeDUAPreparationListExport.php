<?php
	$data = array(
		'Q_Orders'	=>	$Q_Orders
	);
	$output = $this->processTemplate('AcmeDUAPreparationListExport',$data);
	
	$this->display->layout = 'none';
	
	// Hardcode headers to force the download under an SSL connection :)
	header("Pragma: public");
	header("Expires: 0");
	header("Cache-Control: must-revalidate, post-check=0, pre-check=0"); 
	header("Content-Type: application/force-download");
	header("Content-Type: application/octet-stream");
	header("Content-Type: application/download");
	header("Content-Disposition: attachment; filename=DUAReport-".date('YmdHis').".txt;");
	header("Content-Transfer-Encoding: binary");
	header("Content-Length: ".strlen($output));
/*	header('Content-Type: application/download',true);
	header('Content-Disposition: attachment; filename=DUAReport.txt',true);	*/
	print($output);
	
?>