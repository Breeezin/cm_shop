<?php
	startAdminPercentageBar('Exporting all pages...');

	$counter = 0;
	while ($asset = $Q_Assets->fetchRow()) {
		$counter++;
		$result = new Request('Asset.PathFromID',array(
			'as_id'	=>		$asset['as_id'],
		));
	
		$result = new Request('Export.Page',array(
			'as_id'	=>		$asset['as_id'],
			'Type'		=>		'All',
		));

		updateAdminPercentageBar($counter/$Q_Assets->numRows());
	}

	stopAdminPercentageBar();
?>
