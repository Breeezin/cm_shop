<?php 
	if (!array_key_exists("AST_TELLAFRIEND_LIMIT", $asset->cereal))
		$asset->cereal['AST_TELLAFRIEND_LIMIT'] = "5";		
	
	if (!array_key_exists("AST_TELLAFRIEND_POPUP_WINDOW_HEIGHT", $asset->cereal))
		$asset->cereal['AST_TELLAFRIEND_POPUP_WINDOW_HEIGHT'] = "500";
	
	if (!array_key_exists("AST_TELLAFRIEND_POPUP_WINDOW_WIDTH", $asset->cereal))
		$asset->cereal['AST_TELLAFRIEND_POPUP_WINDOW_WIDTH'] = "660";
	
?>
<SCRIPT language="Javascript">
	function showMore(){
		theForm = document.forms.AssetForm;
		
		if (theForm.AST_TELLAFRIEND_WINDOW.options[theForm.AST_TELLAFRIEND_WINDOW.selectedIndex].value  == "popup") {
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
	<!-----
		<TR><TH ALIGN="LEFT">Open Window :</TH>
		<TD><select name="AST_TELLAFRIEND_WINDOW" onchange="showMore(this.form)">
				<option value="same" <?php  //$asset->cereal['AST_TELLAFRIEND_WINDOW'] == "same"? print "selected": print "" ?>>Same Window</OPTION>
				<option value="popup" <?php  //$asset->cereal['AST_TELLAFRIEND_WINDOW'] == "popup"? print "selected": print "" ?>>Popup Window</OPTION>				
			</SELECT>
		</TD>		
	</TR> ---->
	<TR>
		<TH ALIGN="LEFT">Popup Window Size :</TH>
		<TD>Width&nbsp;<INPUT type="text" size="3" name="AST_TELLAFRIEND_POPUP_WINDOW_WIDTH" value="<?=$asset->cereal['AST_TELLAFRIEND_POPUP_WINDOW_WIDTH']?>">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Height&nbsp;<INPUT type="text" size="3" name="AST_TELLAFRIEND_POPUP_WINDOW_HEIGHT" value="<?=$asset->cereal['AST_TELLAFRIEND_POPUP_WINDOW_HEIGHT']?>"></TD>
	</TR>
	<TR>
		<TH ALIGN="LEFT">Maximum number of friends :</TH>
		<TD><INPUT type="text" size="3" name="AST_TELLAFRIEND_LIMIT" value="<?=$asset->cereal['AST_TELLAFRIEND_LIMIT']?>"></TD>
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
		<TD><?=$this->fieldSet->displayField('AST_TELLAFRIEND_BUTTONIMAGE');?></TD>
	</TR>
	<TR><TH ALIGN="LEFT">Mouse Over :</TH>		
		<TD><?=$this->fieldSet->displayField('AST_TELLAFRIEND_BUTTONIMAGEOVER');?></TD>	
	</TR></TABLE>
</FIELDSET></TD></TR>

</TABLE>
<SCRIPT>

</SCRIPT>