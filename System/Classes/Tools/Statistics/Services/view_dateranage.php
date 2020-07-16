<?php 
		$this->param("DateFrom","");
      	$this->param("DateTo","");
      	$dateFromValue = date('Y-m-d',mktime (0,0,0,date("m")-1,date("d"),  date("Y")));
		if (strlen($this->ATTRIBUTES['DateFrom'])) {
			$dateFromValue = "{$this->ATTRIBUTES['DateFrom']}";
		} 
	
		$dateFrom = new DateField (array(
			'name'			=>	'DateFrom',
			'displayName'	=>	'From',
			'note'			=>	NULL,
			'required'		=>	TRUE,
			'class'			=>	'formborder',
			'formName'		=>	'StatsForm',
			'verify'		=>	FALSE,
			'value'			=>	$dateFromValue,
			'unique'		=>	FALSE,
			'showCalendar'	=> 	TRUE,
			'size'	=>	'8',	'maxLength'	=>	'10',			
		));
		
		$dateToValue = date('Y-m-d');
		if (strlen($this->ATTRIBUTES['DateTo'])) {
			$dateToValue = "{$this->ATTRIBUTES['DateTo']}";
		}
		$dateTo = new DateField (array(
			'name'			=>	'DateTo',
			'displayName'	=>	'To',
			'note'			=>	NULL,
			'required'		=>	TRUE,
			'class'			=>	'formborder',
			'formName'		=>	'StatsForm',
			'verify'		=>	FALSE,
			'value'			=>	$dateToValue,
			'unique'		=>	FALSE,
			'showCalendar'	=> 	TRUE,
			'size'	=>	'8',	'maxLength'	=>	'10',						
		));
		$typeOptions = '';		
		foreach ($displayStats as $type => $details) {
			if ($details['view']) 
				$typeOptions .= "<option value='$type'>{$details['name']}</option>";
		}
				
?>
<TABLE width="95%" cellpadding="5" cellspacing="0" align="center">
	<TR>
		<TD>
		<a href="javascript:void(0)" onClick="showhide('DateRanageStats', true)"><img border="0" src="<?=$this->classDirectory?>/Templates/Images/h-stats1.gif" alt="Disk Space">
		</td>
		</tr>
		<tr>
		    <td>		    
		   <!--- <span id="DateRanageStatsShowText">View Date Range</SPAN><span id="DateRanageStatsHideText" style="display:none">Hide Date Range</SPAN><BR>--->
		<TABLE cellpadding="4" cellspacing="0" class="border-stats" id="DateRanageStats" style="display:none">
			<TR>	
				<TD>
					<SELECT name="SearchType"><?=$typeOptions?></SELECT>
				</TD>
				<TD>
					<?=$dateFrom->display(FALSE, 'StatsForm')?>
				</TD>
				<TD>
					<?=$dateTo->display(FALSE, 'StatsForm')?>
				</TD>
				<td><input name="Search" type="button" onclick="searchType();" value="Submit"></td>
			</TR>
		</TABLE>
		</TD>
	</TR>
</TABLE>	
<script language="javascript">	
	
	function searchType() {
		url = "index.php?act=statistics.Display&SpecificResult=1&Service="+document.StatsForm.SearchType.options[document.StatsForm.SearchType.selectedIndex].value;
		url += "&DateFrom="+document.StatsForm.DateFrom.value+"&DateTo="+document.StatsForm.DateTo.value;
		url += "&"+document.StatsForm.SearchType.options[document.StatsForm.SearchType.selectedIndex].value+"Param=Yes";
		//alert(url);
		openWindow(url,'Search', 700, 600);
		/*
		w = 700;
		h = 600;
		x = Math.round((screen.availWidth-w)/2); //center the top edge
		y = Math.round((screen.availHeight-h)/2); //center the left edge
		//alert(url);
		popupWin = window.open(url, name, "width="+w+",height="+h+",toolbar=0,location=0,scrollbars=1,statusbar=1,menubar=0,resizable=1,top="+y+",left="+x+",screeenY="+y+",screenX="+x);		
		popupWin.creator=self;	     
		*/
	}
</script>	