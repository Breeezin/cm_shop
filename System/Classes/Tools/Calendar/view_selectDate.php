<SCRIPT LANGUAGE="Javascript">
	function openMonth(date) {
		<?php print($this->ATTRIBUTES['OnClick']); ?>
	}
</SCRIPT>
<?php

	$lastMonth = dateAdd("m",-1,$startDate);
	$lastYear = dateAdd("y",-1,$startDate);
	$nextMonth = dateAdd("m",1,$startDate);
	$nextYear = dateAdd("y",1,$startDate);

	$startOfLink = 'index.php?act=Calendar.SelectDate&OnClick='.ss_URLEncodedFormat($this->ATTRIBUTES['OnClick']).'&Format='.ss_URLEncodedFormat($this->ATTRIBUTES['Format']);
	
	$lmLink = $startOfLink.'&Month='.month($lastMonth).'&Year='.year($lastMonth);
	$lyLink = $startOfLink.'&Month='.month($lastYear).'&Year='.year($lastYear);
	$nmLink = $startOfLink.'&Month='.month($nextMonth).'&Year='.year($nextMonth);
	$nyLink = $startOfLink.'&Month='.month($nextYear).'&Year='.year($nextYear);
?>
<DIV ALIGN="CENTER" STYLE="width:100%">
<TABLE BORDER="0" WIDTH="100%" ALIGN="CENTER" CELLPADDING="2" CELLSPACING="4">
	<TR ALIGN="CENTER" VALIGN="MIDDLE">
		<TD COLSPAN="1" CLASS="arrow"><DIV ALIGN="LEFT"><A HREF="<? print($lmLink);?>" CLASS="arrow">&lt;</A></DIV></TD>
		<TD COLSPAN="5" CLASS="dateyeartext"><? print(monthAsString($startDate)); ?></TD>
		<TD COLSPAN="1" CLASS="arrow"><DIV ALIGN="RIGHT"><A HREF="<? print($nmLink);?>" CLASS="arrow">&gt;</A> </DIV></TD>
	</TR>
	<TR ALIGN="CENTER" VALIGN="MIDDLE">
		<TD COLSPAN="1" CLASS="arrow"><DIV ALIGN="LEFT"><A HREF="<? print($lyLink);?>" CLASS="arrow">&lt;&lt;</A></DIV></TD>
		<TD COLSPAN="5" CLASS="dateyeartext"><? print(year($startDate)); ?></TD>
		<TD COLSPAN="1" CLASS="arrow"><DIV ALIGN="RIGHT"><A HREF="<? print($nyLink);?>" CLASS="arrow">&gt;&gt;</A></DIV></TD>
	</TR>
	<TR ALIGN="CENTER" VALIGN="MIDDLE">
		<TD CLASS="boxDay">S</TD>
		<TD CLASS="boxDay">M</TD>
		<TD CLASS="boxDay">T</TD>
		<TD CLASS="boxDay">W</TD>
		<TD CLASS="boxDay">T</TD>
		<TD CLASS="boxDay">F</TD>
		<TD CLASS="boxDay">S</TD>
	</TR>

<?php
	// Print some empty boxes
	for ($i=0; $i < dayOfWeek($startDate); $i++) {
		if ($i == 0) print("<tr>");
		print("<TD class=\"boxblank\">&nbsp;</TD>");
	}
	$currentDate = $startDate;
	$rows = 0;
	while (month($currentDate) == $this->ATTRIBUTES['Month']) {
		if (dayOfWeek($currentDate) == 0) print("<TR>");
		print("<TD ONMOUSEOVER=\"this.className='boxhover';\" ONMOUSEOUT=\"this.className='box';\" class=\"box\" ONCLICK=\"");
		print("openMonth('".date($this->ATTRIBUTES['Format'],$currentDate)."');");
		print("\">".day($currentDate)."</TD>");
		if (dayOfWeek($currentDate) == 6) {
			print("</TR>");
			$rows++;
		}
		$currentDate = addOneDay($currentDate);
	}
	if (dayOfWeek($currentDate) != 0) {
		print("</tr>");
		$rows++;
	}
	
?>

</TABLE>
</DIV>
