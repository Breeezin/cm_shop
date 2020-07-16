<?php 

	ss_RestrictPermission('CanAdministerAtLeastOneAsset');

	//This stuff goes into the head 
	$this->display->layout ="Admin";
	$maxWindows = 10;
	$RelativeHere = $this->classDirectory."/";
		
	/*A vertical position that will be offscreen for all 
		- web browsers in the near future */
		
	$HidePosition = 10000;
		
	//Width for the holder image in the panel bars 
	$BarWidth = 153;
		
	//Position and size the asset panel --->
	$AssetPanelLeft = 130;
	$AssetPanelTop = 162;
	$AssetPanelWidth = 149;
	$AssetPanelBottomMargin = 225;
		
	//Size of the shop panel --->
	$ShopPanelHeight = 100;

	//Size of the newsletter panel --->
	$NewsletterPanelHeight = 50;

	//Size of the newsletter panel --->
	$PropertiesPanelHeight = 150;
	
	//Position and size the tabbed pages --->
	$TabbedPageLeft = 191;
	$TabbedPageTop = 142;
	$TabbedPageRightMargin = 11;
	$TabbedPageBottomMargin = 11;  //<!--- 74 with footer image --->
	
	$PageHeaderHeight = 200;	//<!---  Logo, Pages menus --->
	$AssetHeaderHeight = 180;		//<!--- Asset tabs, save, delete etc --->
	$AssetFooterHeight = 3 * 50;	 	//<!--- Layout, SubAssets, Security tabs --->
		
	$headData = array();
	$headData['maxWindows'] = $maxWindows;
	$headData['HidePosition'] = $HidePosition;
	$headData['RelativeHere'] = $RelativeHere;
	$headData['Script_Name'] = $_SERVER['SCRIPT_NAME'];
	$headData['TabbedPageLeft'] = $TabbedPageLeft;
	$headData['TabbedPageRightMargin'] = $TabbedPageRightMargin;
	$headData['TabbedPageTop'] = $TabbedPageTop;
	$headData['TabbedPageBottomMargin'] = $TabbedPageBottomMargin;
	$headData['HidePosition'] = $HidePosition;
	$headData['ShopPanelHeight'] = $ShopPanelHeight;
	$headData['NewsletterPanelHeight'] = $NewsletterPanelHeight;
	$headData['PropertiesPanelHeight'] = $PropertiesPanelHeight;
	$headData['PageHeaderHeight'] = $PageHeaderHeight;
	$headData['AssetHeaderHeight'] = $AssetHeaderHeight;
	$headData['AssetFooterHeight'] = $AssetFooterHeight;
	$headData['Authencated'] = ss_HasPermission('CanAdministerAsset',2);

	$headData['HasOnlineShop'] = false;
	$headData['HasNewsletter'] = false;
	
	
	$this->display->head = $this->processTemplate('Head',$headData);
	
	$startData = array();
	$startData['AssetPanelTop'] = $AssetPanelTop;
	$startData['AssetPanelLeft'] = $AssetPanelLeft;
	$startData['AssetPanelWidth'] = $AssetPanelWidth;
	$startData['Script_Name'] =  $_SERVER['SCRIPT_NAME'];
	$startData['TabbedPageLeft'] = $TabbedPageLeft;
	$startData['TabbedPageTop'] = $TabbedPageTop;
	$startData['BarWidth'] = $BarWidth;
	
	$startData['Index'] = 0;
	$startData['MaxWindows'] = $maxWindows;
	$startData['RelativeHere'] = $RelativeHere;
		
	$startData['Authencated'] = ss_HasPermission('CanAdministerAsset',2);
	$startData['HasOnlineShop'] = true;
	$startData['HasNewsletter'] = true;
	
	//This stuff goes just after the body tag --->
	$this->display->bodyStart = $this->processTemplate('BodyStart',$startData);
	
	
	//Insert our variables into the husk
	/*
	<CFLOOP LIST="Head,BodyStart,BodyEnd" INDEX="tag">
		<CFSET layout = Replace(layout,"[#tag#]",Evaluate(tag))>
	</CFLOOP>
	*/
	//<!--- Don't want a layout or debugging information --->
	//$this->display->layout = "Admin";
	
	
	//#layout#

?>