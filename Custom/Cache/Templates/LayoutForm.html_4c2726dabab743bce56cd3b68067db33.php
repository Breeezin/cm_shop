<TABLE BORDER="0" WIDTH="100%">
<TR><TD>

<FIELDSET TITLE="Layout" STYLE="padding:5px 5px 5px 5px">
	<LEGEND>Layout</LEGEND>
	<P>
		Choose a layout <?php if(ss_optionExists("StyleSheet Picker")){  ?>and a style sheet<?php } ?> that this asset will use for the display action.
	</P>
	<SELECT NAME="LYT_LAYOUT">
	<?php foreach($data['ListLayouts'] as $aLayout){  ?>
			<OPTION VALUE="<?php print(ListFirst($aLayout, ':')); ?>" 
				<?php if ($data['LYT_LAYOUT'] == ListFirst($aLayout, ':') ) { ?>
					<?php print('SELECTED'); ?>
				<?php } ?>>
				<?php print(ListFirst($aLayout, ':')); ?> - <?php print(ListLast($aLayout, ':')); ?>
			</OPTION>
	<?php } ?>
	</SELECT>
	
	<?php if(ss_optionExists("StyleSheet Picker")) { ?>
		&nbsp;and&nbsp;
		<SELECT NAME="LYT_STYLESHEET">
		<?php foreach($data['ListStylesheets'] as $aSheet){  ?>
			<OPTION VALUE="<?php print(ListFirst($aSheet, ':')); ?>" 
				<?php if ($data['LYT_STYLESHEET'] == ListFirst($aSheet, ':') ) { ?>
					<?php print('SELECTED'); ?>
				<?php } ?>>
				<?php print(ListLast($aSheet, ':')); ?>
			</OPTION>
		<?php } ?>
		</SELECT>
	<?php } ?>
	<P>
		You can set the system so that when you create a sub assset of this asset, those assets will use the same layout settings as above.  This is useful if an entire area of your website uses a different layout. If this option is disabled, any new sub assets will be set to use the default layout for this website. To enable this option, please tick this box:
		<?php print($data['fieldSet']['LYT_LAYOUT_APPLY_TO_CHILDREN']->display(FALSE,'AssetForm')); ?>
	</P>
</FIELDSET>	
<FIELDSET TITLE="Titles" STYLE="padding:5px 5px 5px 5px">
	<LEGEND>Titles</LEGEND>
	<P>
		If you wish to override the name of this asset in the menu system or the title of this page, you may enter them below: 
	</P>
	<table width="100%">
		<tr>
			<td width="100">Menu :</td>
			<td><?php $data['assetFieldSet']->displayField('as_menu_name'); ?></td>
		</tr>
		<tr>
			<td>Page Title :</td>
			<td><?php $data['assetFieldSet']->displayField('as_header_name'); ?></td>
		</tr>
		<tr>
			<td>Window Title :</td>
			<td><?php print($data['fieldSet']['LYT_WINDOWTITLE']->display(FALSE,'AssetForm')); ?></td>
		</tr>
	</table>
</FIELDSET>

<FIELDSET TITLE="Images" STYLE="padding:5px 5px 5px 5px">
	<LEGEND>Images</LEGEND>
	<TABLE CELLSPACING="0" CELLPADDING="0" WIDTH="80%">
		<TR>
			<TD WIDTH="20%">Page Title :</TD>
			<TD ALIGN="LEFT" VALIGN="MIDDLE">&nbsp;<?php print($data['fieldSet']['LYT_TITLEIMAGE']->display(FALSE,'AssetForm')); ?></TD></TR>
		<TR>
			<TD>Menu Normal :</TD>
			<TD ALIGN="LEFT" VALIGN="MIDDLE">&nbsp;<?php print($data['fieldSet']['LYT_MENU_NORMALIMAGE']->display(FALSE,'AssetForm')); ?></TD></TR>
		<TR>
			<TD>Menu MouseOver :</TD>
			<TD ALIGN="LEFT" VALIGN="MIDDLE">&nbsp;<?php print($data['fieldSet']['LYT_MENU_MOUSEOVERIMAGE']->display(FALSE,'AssetForm')); ?></TD></TR>			
	</TABLE>
</FIELDSET>

<FIELDSET TITLE="Meta Tags">
	<LEGEND>Meta Tags</LEGEND>
	<TABLE CELLSPACING="0" CELLPADDING="5" WIDTH="100%">
		<TR><TD>Main Keywords :</TD></TR>
		<TR><TD><TEXTAREA NAME="as_search_keywords" ROWS="5"  STYLE="width:100%" wrap="soft"><?php print(ss_HTMLEditFormat($data['as_search_keywords'])); ?></TEXTAREA></TD></TR>
		<TR><TD>Main Description :</TD></TR>
		<TR><TD><TEXTAREA NAME="as_search_description" ROWS="5"  STYLE="width:100%" wrap="soft"><?php print(ss_HTMLEditFormat($data['as_search_description'])); ?></TEXTAREA></TD></TR>
	<?php 
//			ss_log_message_r(  'Log:'.__FILE__.':'.__LINE__, $data['assetFieldSet']->fields );
		  if( strlen( $data['assetFieldSet']->fields['as_name']->as_id ) )
		  {
			$Q_lang = query( "select * from languages left join asset_descriptions on ad_language = lg_id and ad_as_id = ".$data['assetFieldSet']->fields['as_name']->as_id." where lg_id > 0" );
			  while( $lrow = $Q_lang->fetchRow() )
			  { ?>
				<TR><TD>Title for <?php echo $lrow['lg_name'];?> :</TD></TR>
				<TR><TD><TEXTAREA NAME="AssetWindowTitle<?php echo $lrow['lg_id'];?>" ROWS="5"  STYLE="width:100%" wrap="soft"><?php echo $lrow['ad_window_title'];?></TEXTAREA></TD></TR>
				<TR><TD>Keywords for <?php echo $lrow['lg_name'];?> :</TD></TR>
				<TR><TD><TEXTAREA NAME="as_search_keywords<?php echo $lrow['lg_id'];?>" ROWS="5"  STYLE="width:100%" wrap="soft"><?php echo $lrow['ad_metadata_keywords'];?></TEXTAREA></TD></TR>
				<TR><TD>Description for <?php echo $lrow['lg_name'];?> :</TD></TR>
				<TR><TD><TEXTAREA NAME="as_search_description<?php echo $lrow['lg_id'];?>" ROWS="5"  STYLE="width:100%" wrap="soft"><?php echo $lrow['ad_metadata_description'];?></TEXTAREA></TD></TR>
		<?php }
		  }
	?>
	</TABLE>
</FIELDSET>
<?php if (ss_optionExists('Layout Subcontent Page')) {?> 
<FIELDSET TITLE="Sub Content">
	<LEGEND>Sub Content</LEGEND>
	<?php if (ss_HasPermission('IsDeployer')) { ?>	
	<div align="right">
		<input name="subimport" type="IMAGE" src="Images/but-import.gif" alt="Import Asset" title="Import" style="border:none 1px;" id="Image116" onMouseOver="MM_swapImage('label','','Images/but-import-lb.gif','Image116','','Images/but-import-on.gif',1)" onMouseOut="MM_swapImgRestore()" onClick="document.getElementById('SaveStarting').innerHTML = 'Importing SubContent... please wait';disableButtons();">
		&nbsp;<input name="subexport" type="IMAGE" src="Images/but-export.gif" alt="Export Asset" title="Export" style="border:none 1px;" id="Image115" onMouseOver="MM_swapImage('label','','Images/but-export-lb.gif','Image115','','Images/but-export-on.gif',1)" onMouseOut="MM_swapImgRestore()" onClick="document.getElementById('SaveStarting').innerHTML = 'Exporting SubContent... please wait';disableButtons();">
		</DIV>
	<?php } ?>
	
	<TABLE CELLSPACING="0" CELLPADDING="5" WIDTH="100%">
    	<TR><td><?php print($data['fieldSet']['LYT_LAYOUT_SUBPAGECONTENT']->display(FALSE,'AssetForm')); ?></td></TR>
	</TABLE>
</FIELDSET>
<?php } ?>
<?php if (ss_optionExists('Advanced Security Permission')) {?>
<FIELDSET TITLE="Sub Content">
	<LEGEND>Security Failure Page</LEGEND>
	<TABLE CELLSPACING="0" CELLPADDING="5" WIDTH="100%">
		<TR><td><?php print($data['fieldSet']['LYT_LAYOUT_SECURITYPAGE']->display(FALSE,'AssetForm')); ?></td></TR>
	</TABLE>
</FIELDSET>
<?php } ?>
</TD></TR>
</TABLE>
