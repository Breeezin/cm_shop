<?php 
	$this->display->title = "";
	$this->display->layout ="AdministrationTabbedPage";
	
	
	$data = array();
	$data['HasOnlineShop'] = false; //$this->ATTRIBUTES['HasOnlineShop'];
	$data['Script_Name'] = $_SERVER['SCRIPT_NAME'];
	$data['RFA'] = "{$_SERVER['SCRIPT_NAME']}?act=TabbedInterfaceConfiguration";
	$data['BreadCrumbs'] = "<A HREF=\"{$_SERVER['SCRIPT_NAME']}?act=TabbedInterfaceConfiguration\">configuration</A>";
	/*$data['ShopSystemAssetID'] = $this->ATTRIBUTES['ShopSystemAssetID'];
	$data['CurAssetPath'] = ListLast($this->ATTRIBUTES['ShopSystemAssetPath'], "/");
	$data['ShopSystemAssetPath'] = $this->ATTRIBUTES['ShopSystemAssetPath'];
	$data['ShopSystemAssetParentLink'] = $this->ATTRIBUTES['ShopSystemAssetParentLink'];
	$data['ShopSystemAssetParentPath'] = $this->ATTRIBUTES['ShopSystemAssetParentPath'];*/
	
	//ss_log_message_r("this", $this);
	
	print $this->processTemplate('Configuration',$data);

?>
