<?php if ($data['tableTags']) { ?> 
<div class='container'>
<?php } ?>
	<?php foreach ($data['fields'] as $data['field']) { ?>		
	<?php if(!is_subclass_of($data['field'],'hiddenfield') and get_class($data['field']) != 'hiddenfield') {?>
		<div class='row' <?php if ($data['tableTags']) { ?>CLASS="<?php if (array_key_exists($data['field']->name,$data['errors'])) { ?>AdminErrorField<?php } ?>"<?php } ?>>
			<div CLASS="col-md-1 AdminRequired"><?php if ($data['field']->required) { ?>*<?php } ?></div>
			<div class="col-md-4 AdminDisplayName">
				<?php print($data['field']->displayName) ?>
				<?php if (strlen($data['field']->note)) { ?>
					<br/><div CLASS="AdminNote"><?php print($data['field']->note) ?></div>
				<?php } ?>
			</div>
			<div class="col-md-7">
				<?php ss_log_message_r( 'Log:'.__FILE__.':'.__LINE__, $data['field'] ); ?>
				<?php $formName ='adminForm'; if (array_key_exists('formName', $data))  $formName = $data['formName']; if( method_exists( $data['field'], 'display' ) ) print($data['field']->display(false, $formName)); ?>
			</div>
		</div>
		<?php if ($data['field']->verify) { ?>
			<div class='row' <?php if ($data['tableTags']) { ?>CLASS="<?php if (array_key_exists($data['field']->name.'_V',$data['errors'])) { ?>AdminErrorField<?php } ?>"<?php } ?>>
				<div CLASS="col-md-1 AdminRequired"><?php if ($data['field']->required) { ?>*<?php } ?></div>
				<div class="col-md-4 AdminDisplayName">
					Verify <?php print($data['field']->displayName) ?>
					<?php if (strlen($data['field']->note)) { ?>
					<br />
					<div CLASS="AdminNote"><?php print($data['field']->note) ?></div>
					<?php } ?>
				</div>
				<div class="col-md-7">
					<?php $formName ='adminForm'; if (array_key_exists('formName', $data))  $formName = $data['formName'];  print($data['field']->display(true, $formName)); ?>
				</div>
			</div>
		<?php } ?>	
		<?php } else { ?>
		<?php $formName ='adminForm'; if (array_key_exists('formName', $data))  $formName = $data['formName']; print($data['field']->display(false, $formName)); ?>						
		<?php } ?>
	<?php } ?>
<?php if ($data['tableTags']) { ?> 	
</div>
<?php } ?>
