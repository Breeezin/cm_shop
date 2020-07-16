<?php

	$this->param('test','');
	//ss_log_message_r($this->ATTRIBUTES['test'],'test');
?>
<html>
	<body>
		<form method="post" action="index.php?act=TestMaori">
			<textarea name="test"><?=ss_htmlEditFormat($this->ATTRIBUTES['test']);?></textarea>
			<input type="submit" name="submit" value="submit">
		</form>
	</body>
</html>
