<?php 
	
	ss_paramKey($asset->cereal, "AST_PRINTPAGE_BUTTONIMAGE", '');
	ss_paramKey($asset->cereal, "AST_PRINTPAGE_BUTTONIMAGEOVER", '');
	ss_paramKey($asset->cereal, "AST_PRINTPAGE_POPUP_WINDOW_WIDTH", '660');
	ss_paramKey($asset->cereal, "AST_PRINTPAGE_POPUP_WINDOW_HEIGHT", '500');	
	
?>
<SCRIPT language="Javascript">
	function showMore(){
		theForm = document.forms.AssetForm;
		
		if (theForm.AST_PRINTPAGE_WINDOW.options[theForm.AST_PRINTPAGE_WINDOW.selectedIndex].value  == "popup") {
			document.getElementById('PopupTitle').style.display = '';
			document.getElementById('PopupDetails').style.display = '';			
		} else {
			document.getElementById('PopupTitle').style.display = 'none';
			document.getElementById('PopupDetails').style.display = 'none';	
		}
	}
</SCRIPT>
<TABLE>
<TR><TD>
<FIELDSET TITLE="">
	<TABLE CELLSPACING="0" CELLPADDING="5" WIDTH="400">	
	<TR>
		<TH ALIGN="LEFT">Popup Window Size :</TH>
		<TD>Width&nbsp;<INPUT type="text" size="3" name="AST_PRINTPAGE_POPUP_WINDOW_WIDTH" value="<?=$asset->cereal['AST_PRINTPAGE_POPUP_WINDOW_WIDTH']?>">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Height&nbsp;<INPUT type="text" size="3" name="AST_PRINTPAGE_POPUP_WINDOW_HEIGHT" value="<?=$asset->cereal['AST_PRINTPAGE_POPUP_WINDOW_HEIGHT']?>"></TD>
	</TR>
	</TABLE>
</FIELDSET>
</TD></TR>
<TR><TD>
<FIELDSET TITLE="Button Image">
	<LEGEND>Button Image</LEGEND>

	<TABLE CELLSPACING="0" CELLPADDING="5" WIDTH="400">
	<!-----
	<TR><TD COLSPAN="3">
		<STRONG>Macintosh Internet Explorer users :</STRONG><BR>
		&nbsp;&nbsp;&nbsp;&nbsp;Please note that Internet Explorer on the 
		Macintosh may have problems with uploading files, for best results use
		<A HREF="http://www.mozilla.org/releases/">Mozilla</A>.<BR>
	</TD></TR>
	------>
	<TR><TH ALIGN="LEFT">Normal :</TH>
		<TD><?=$this->fieldSet->displayField('AST_PRINTPAGE_BUTTONIMAGE');?></TD>
	</TR>
	<TR><TH ALIGN="LEFT">Mouse Over :</TH>		
		<TD><?=$this->fieldSet->displayField('AST_PRINTPAGE_BUTTONIMAGEOVER');?></TD>	
	</TR></TABLE>
</FIELDSET></TD></TR>

</TABLE>
<SCRIPT>

</SCRIPT>