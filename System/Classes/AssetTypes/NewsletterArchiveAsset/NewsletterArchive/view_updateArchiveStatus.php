<?php
	$this->display->title = 'Done';
?>

<html>
<body>
<script language="Javascript">
	alert('Status Updated!');
	document.location='<?=ss_JSStringFormat($this->ATTRIBUTES['BackURL'])?>';
</script>
</body>
</html>