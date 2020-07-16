<?php
//	$Q_Count = getRow("SELECT Count(*) AS Count FROM ScheduledEvents");
	$Q_Count2 = getRow("SELECT Count(*) AS Count FROM Events");
	$Q_Count3 = getRow("SELECT Count(*) AS Count FROM EventTypes");
	
	
?>
<table cellpadding="0" cellspacing="2" width="100%">
	<tr>
		<td class="propertiesLabel">Total Scheduled Events :</td>
		<td><?=$Q_Count2['Count']?></td>
	</tr>	
	<tr>
		<td class="propertiesLabel">Total Event Types :</td>
		<td><?=$Q_Count3['Count']?></td>
	</tr>	
</table>