<?
	$data = array(
		'Basket'	=>	$_SESSION['Shop']['Basket'],
		'AssetPath'	=>	ss_withoutPreceedingSlash($asset->getPath()),
		'This'		=>	$this,		
	);
	
	ss_customStyleSheet($this->styleSheet);
	$this->useTemplate('MiniBasket',$data);

?>