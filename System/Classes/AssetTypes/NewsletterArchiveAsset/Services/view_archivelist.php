<?php
	$data = array(
		'AssetPath'	=>	ss_withoutPreceedingSlash($asset->getPath()),
		'Q_Current'	=>	$Q_Current,
		'Q_Archive'	=>	$Q_Archive,
	);
	$this->useTemplate('ArchiveList',$data);

?>