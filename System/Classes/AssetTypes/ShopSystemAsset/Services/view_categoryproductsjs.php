<?php
	$data = array(
		'Q_Products'	=>	$Q_Products,
		'CallBack'	=>	$this->ATTRIBUTES['CallBack'],
	);
	//$Q_Products->prefetch();
	//ss_DumpVar($Q_Products);
	$asset->display->layout = 'none';
	if( ss_adminCapability( ADMIN_PRODUCT_ENTRY ) )
		$this->useTemplate('CategoryProductsAdminJS',$data);
	else
		$this->useTemplate('CategoryProductsJS',$data);
	
	//ss_DumpVar($GLOBALS['sql']);
	
?>
