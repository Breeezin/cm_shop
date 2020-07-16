<?php 	
	if ($Q_CanDelete->numRows() and !array_key_exists('AsService',$this->ATTRIBUTES)) {
		$this->display->layout = "None";
?>	
	<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
	<HTML>
	<HEAD>
	<TITLE>Deleting Asset</TITLE>
	<META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=<?=$GLOBALS['cfg']['Web_Charset']?>">
	<SCRIPT language="JavaScript">
		var delledAssets = [<?=$delledItems?>];
		window.parent.deleteCallback(delledAssets);
		//window.close();
	</SCRIPT>
	</HEAD>
	
	<BODY>
	Please Wait Deleting File
	</BODY>
	</HTML>
<?php } ?>
