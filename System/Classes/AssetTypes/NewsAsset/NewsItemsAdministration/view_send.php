<?
	startAdminPercentageBar('Sending news item to '.$Q_Recipients->numRows()." ".ss_pluralize($Q_Recipients->numRows(),'person','people')."...");

	$this->display->title = '';
	
	$display = true;
	if ($Q_Recipients->numRows() > 1) {
		print("<script language=\"Javascript\">recipients = new Array(");
		$comma = '';
		while ($row = $Q_Recipients->fetchRow()) {
			print($comma.$row['us_id']);
			$comma = ',';
		}
 		print(");</script>");
	} else if ($Q_Recipients->numRows() == 1) {
		$row = $Q_Recipients->fetchRow();
		print ("<script language=\"Javascript\">recipients = new Array();recipients[0]={$row['us_id']};</script>");
	} else {
		$display = false;
?>
	<script language="Javascript">
		document.location = '<?=ss_JSStringFormat($this->ATTRIBUTES['BackURL']);?>';			
	</script>
<?
	}

	if ($display) {
?>

<iframe id="theLoader" width="300" height="300" style="display:none;"></iframe>

<script language="Javascript">
	loader = document.getElementById('theLoader');
	currentRecipient = 0;
	recipientsPerBlock = 10;
	function doNextRecipient() {
		if (currentRecipient < recipients.length) {
			
			recipientsList = '';
			count = 0;
			while (count < recipientsPerBlock && currentRecipient < recipients.length) {
				if (recipientsList.length > 0) recipientsList += ',';
				recipientsList += recipients[currentRecipient];
				currentRecipient++;	count++;
			}
			sw(currentRecipient/recipients.length);
			//alert('sending to '+recipientsList);
			//doNextRecipient();
			loader.src = 'index.php?act=News.SendOne&nei_id=<?=$this->ATTRIBUTES['nei_id']?>&UsersList='+recipientsList;
		} else {
			sw(1);	
			alert('Complete!');
			document.location = '<?=ss_JSStringFormat($this->ATTRIBUTES['BackURL']);?>';			
		}
	}
	doNextRecipient();
</script>
<?	}	?>