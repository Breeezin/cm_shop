<?
	startAdminPercentageBar('Importing '.$Q_ImportUsers->numRows()." ".ss_pluralize($Q_ImportUsers->numRows(),'product','products')."...");

	$this->display->title = '';
	
	if ($Q_ImportUsers->numRows() > 1) {
		print("<script language=\"Javascript\">recipients = new Array(");
		$comma = '';
		while ($row = $Q_ImportUsers->fetchRow()) {
			print($comma.$row['imu_id']);
			$comma = ',';
		}
 		print(");</script>");
	} else {
		$row = $Q_ImportUsers->fetchRow();
		print ("<script language=\"Javascript\">recipients = new Array();recipients[0]={$row['imu_id']};</script>");
	}
	
?>
<iframe id="theLoader" width="100%" height="300"></iframe>

<script language="Javascript">
	loader = document.getElementById('theLoader');
	currentRecipient = 0;
	recipientsPerBlock = 200;
	var errorCount = 0;
	function updateErrorCount(amount) {
		errorCount += amount;
	}
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
			loader.src = 'index.php?act=Import.CarsList&Code=<?=ss_URLEncodedFormat($this->ATTRIBUTES['Code']);?>&ImportCarsList='+recipientsList;
		} else {
			sw(1);	
			if (errorCount == 0) {
				alert('Import Complete!\n\nPlease close this tab.');	
			} else {
				alert('Complete. '+errorCount+' error(s) occured.\n\nPlease scroll down to view.\n\nClose this tab once you have finished.');
			}
			//document.location = 'index.php?act=NewslettersAdministration.List';			
		}
	}
	doNextRecipient();
</script>
<div id="errors"></div>