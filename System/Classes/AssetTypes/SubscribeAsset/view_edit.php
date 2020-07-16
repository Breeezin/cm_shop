<table cellpadding="0" cellspacing="5" width="100%"><tr><td valign="top" width="50%">
<FIELDSET id="mailingListFieldset" TITLE="Mailing Lists" style="padding:5px;">
	<LEGEND>Mailing Lists</LEGEND>
	<p>
		Select the mailing list users can join through this item:
	</p>
	<p>
		<?=$this->fieldSet->displayField('AST_SUBSCRIBE_USERGROUPS');?>
	</p>	
	<p />&nbsp;&nbsp;
	<?php 
	if (ss_optionExists('Newsletter Advanced Subscribe Form')) {
	?>
	<p>
		Set Subscribe Form Fields<BR>
		<?=$this->fieldSet->displayField($this->fieldPrefix.'FORMFIELDS');?>
	<p>
	<?php 
		}
	?>
</FIELDSET>
</td><td valign="top" width="50%">
<FIELDSET id="buttonImageFieldset" TITLE="Button Images" style="padding:5px;">
	<LEGEND>Button Images</LEGEND>

	<TABLE CELLSPACING="0" CELLPADDING="5" WIDTH="100%">
	<TR><TH ALIGN="LEFT">Normal :</TH>
		<TD><?=$this->fieldSet->displayField($this->fieldPrefix.'BUTTONIMAGE');?></TD>
	</TR>
	<TR><TH ALIGN="LEFT">Mouse Over :</TH>		
		<TD><?=$this->fieldSet->displayField($this->fieldPrefix.'BUTTONIMAGEOVER');?></TD>	
	</TR></TABLE>
</FIELDSET>
</td>
</tr>

</table>
<p>
<STRONG>After Subscribing:</STRONG><br />
<?=$this->fieldSet->displayField($this->fieldPrefix.'SUBSCRIBE_CONTENT');?>
</p>
<p>
<STRONG>After Unsubscribing:</STRONG><br />
<?=$this->fieldSet->displayField($this->fieldPrefix.'UNSUBSCRIBE_CONTENT');?>
</p>