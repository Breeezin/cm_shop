<?php
	
	
	$this->param('Opened', '');	
	$opened = ListToArray($this->ATTRIBUTES['Opened']);	
	
	$displayStats = array();

	$displayStats['DateRanage'] = array('view' => false, 'name'=> '', 'assets' => array("DateRanageView"));
	$displayStats['Pages'] = array('view' => true, 'name'=> 'Page Usage', 'assets' => array("PageSats"));
	$displayStats['Referrals'] = array('view' => true, 'name'=> 'Referrals', 'assets' => array("ReferralsStats"));
	$displayStats['Members'] = array('view' => true, 'name'=> 'Members', 'assets' => array("Members"));
	
	
	// check whether the site has search, shop, datacollection and randaomimages assets or not	
	//foreach(array('Search','Shop','DataCollection','RandomImages',) as $type) {
	foreach(array('RandomImages' => 'Random Image Link','Shop' => 'Shop_System',) as $type => $name) {
		if ($type == 'Shop') {
			$tempType = "Shop";
			$type = "ShopSystem";
		} else {
			$tempType = $type;
		}
		$Q_Assets = query("
						SELECT as_id 
						FROM assets 
						WHERE as_type LIKE '$type'
						AND as_deleted = 0
		");
		if ($Q_Assets->numRows()) {			
			$displayStats[$tempType] = array('view' => true, 'name' => $name, 'assets' => $Q_Assets->columnValuesArray('as_id'));
		} else {
			$displayStats[$tempType] = array('view' => false,'name' => $name, 'assets' => array());
		}
	}
	$displayStats['DiskSpace'] = array('view' => false, 'name' => $name, 'assets' => array("DiskSpaceStats"));	
	

?>