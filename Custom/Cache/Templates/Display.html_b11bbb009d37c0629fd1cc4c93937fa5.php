<TABLE WIDTH="100%" CELLSPACING="0" CELLPADDING="5">		
<?php foreach ($data['fields'] as $data['field']) { ?>
	<?php if (!$data['IsForm']) { ?>		
		<?php print($data['field']->displayValue($data['field']->value)) ?><BR>	
	<?php } else { ?>
		<TR>
		<TD VALIGN="TOP" ALIGN="Middle" WIDTH="5" CLASS="AdminRequired"><?php if ($data['field']->required) { ?>*<?php } ?></TD>
		<TH VALIGN="TOP" align=left width="33%"><SPAN CLASS="AdminDisplayName">
			<?php print($data['field']->displayName) ?></SPAN>
			<?php if (strlen($data['field']->note)) { ?>
				<BR><SPAN CLASS="AdminNote"><?php print($data['field']->note) ?></SPAN>
			<?php } ?>
		</TH>
		<TD>
		<?php if ($data['IsForm']) { ?>
			<?php print($data['field']->display(false, 'adminForm')) ?>
		<?php } else { ?>
			<?php print($data['field']->displayValue($data['field']->value)) ?>
		<?php } ?>
		</TD>
		</TR>
		<?php if ($data['field']->verify) { ?>
		<TR>
			<TD VALIGN="TOP" ALIGN="middle" WIDTH="5" CLASS="AdminRequired"><?php if ($data['field']->required) { ?>*<?php } ?></TD>
			<TH VALIGN="TOP" align=left width="33%"><SPAN CLASS="AdminDisplayName">
				Verify <?php print($data['field']->displayName) ?></SPAN>
				<?php if (strlen($data['field']->note)) { ?>
					<BR><SPAN CLASS="AdminNote"><?php print($data['field']->note) ?></SPAN>
				<?php } ?>
			</TH>
			<TD><?php print($data['field']->display(true, 'adminForm')) ?></TD>
		</TR>
		<?php } ?>
<?php } ?>
<?php } ?>
</TABLE>
