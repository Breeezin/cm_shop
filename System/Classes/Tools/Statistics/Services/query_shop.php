<?php 
		
	$this->param("ShopPrParam", '');
	$this->param("ShopCatParam", '');
	$this->param("ShopOrderParam", '');
	$this->param("ShopWishListParam", '');
	
	$allproducthitsDefined = '';
	$allwishhitsDefined = '';
	$whereSQL = '';
	$orderWhereSQL = '';
	if (array_key_exists('SpecificResult', $this->ATTRIBUTES)){
		if (strlen($this->ATTRIBUTES['DateFrom']) or strlen($this->ATTRIBUTES['DateTo'])) {
			if (strlen($this->ATTRIBUTES['DateFrom']) and !strlen($this->ATTRIBUTES['DateTo'])) {
				$whereSQL =  " AND sst_timestamp  >= '{$this->ATTRIBUTES['DateFrom']} 00:00:00'";
				$orderWhereSQL =  " AND orpr_timestamp  >= '{$this->ATTRIBUTES['DateFrom']} 00:00:00'";
				$allproducthitsDefined = 'The statistics for all the product views since '.$this->ATTRIBUTES['DateFrom'];
				$allcathitsDefined = 'The statistics for all the category views since '.$this->ATTRIBUTES['DateFrom'];
				$allorderhitsDefined = 'The statistics for all the product views since '.$this->ATTRIBUTES['DateFrom'];
			} else if (strlen($this->ATTRIBUTES['DateTo']) and !strlen($this->ATTRIBUTES['DateFrom'])) {
				$whereSQL =  " AND sst_timestamp  <= '{$this->ATTRIBUTES['DateTo']} 00:00:00'";
				$orderWhereSQL =  " AND orpr_timestamp  <= '{$this->ATTRIBUTES['DateTo']} 00:00:00'";
				$allproducthitsDefined = 'The statistics for all the product views until '.$this->ATTRIBUTES['DateTo'];
				$allcathitsDefined = 'The statistics for all the category views until '.$this->ATTRIBUTES['DateTo'];
				$allorderhitsDefined = 'The statistics for all the product views until '.$this->ATTRIBUTES['DateTo'];
			} else {
				$whereSQL =  " AND sst_timestamp  BETWEEN '{$this->ATTRIBUTES['DateFrom']} 00:00:00' AND '{$this->ATTRIBUTES['DateTo']} 23:59:59'";
				$orderWhereSQL =  " AND orpr_timestamp  BETWEEN '{$this->ATTRIBUTES['DateFrom']} 00:00:00' AND '{$this->ATTRIBUTES['DateTo']} 23:59:59'";
				$allproducthitsDefined = 'The statistics for all the product views between '.$this->ATTRIBUTES['DateFrom']." and ".$this->ATTRIBUTES['DateTo'];
				$allcathitsDefined = 'The statistics for all the category views between '.$this->ATTRIBUTES['DateFrom']." and ".$this->ATTRIBUTES['DateTo'];
				$allorderhitsDefined = 'The statistics for all the product views between '.$this->ATTRIBUTES['DateFrom']." and ".$this->ATTRIBUTES['DateTo'];
			}
		}
	}
	//ss_DumpVar($this->ATTRIBUTES);
	$result = new Request("Asset.PathFromID", array('as_id'=> $this->ATTRIBUTES['ShopAssets'][0]));				
	$shopAssetPath = ss_withoutPreceedingSlash($result->value);
	$Q_ProductHits = query("
			SELECT pr_name AS ShStProductName, sst_pr_id,Count(sst_id) AS Hits, sst_ca_id
			FROM shopsystem_statistics, shopsystem_products		
			WHERE 
				sst_pr_id > 0
				AND
				sst_pr_id = pr_id
				$whereSQL
			GROUP BY 
				sst_pr_id
			ORDER BY 
				Hits DESC
	");	
	
	$hits = $Q_ProductHits->columnValuesArray('Hits');
	
	$totalHits = 0;
	
	foreach ($hits as $hit) {
		$totalHits += $hit;
	}
	if (!array_key_exists('Service', $this->ATTRIBUTES)) {			
		if (strlen($this->ATTRIBUTES["ShopPrParam"])) {
			$this->showAllParameters .= "&AllShopStats=";
			$allproducthitsDefined = 'Below are the statistics for all the product views on your website.  To return to the details of the top ten product views, <A HREF="javascript:document.StatsForm.ShopPrParam.value=\'\';document.StatsForm.submit();">click here</A>.';
		} else {
			$this->showAllParameters .= "&AllShopStats=Yes";
			$allproducthitsDefined = 'Below are the statistics for the top ten product views on your website.  For details of the hits on all your product views <A HREF="javascript:document.StatsForm.ShopPrParam.value=\'Yes\';document.StatsForm.submit();">click here</A>.';
		}	
	}
	$Q_CategoryHits = query("
			SELECT Count(sst_id) AS Hits, sst_ca_id
			FROM shopsystem_statistics							
			WHERE 1 = 1
			$whereSQL
			GROUP BY 
				sst_ca_id	
			ORDER BY 
				Hits DESC
	");	
	
	$cathits = $Q_CategoryHits->columnValuesArray('Hits');
	
	$totalCatHits = 0;
	
	foreach ($cathits as $hit) {
		$totalCatHits += $hit;
	}
	if (!array_key_exists('Service', $this->ATTRIBUTES)) {		
		if (strlen($this->ATTRIBUTES["ShopCatParam"])) {
			$this->showAllParameters .= "&AllShopStats=";
			$allcathitsDefined = 'Below are the statistics for all the category views on your website.  To return to the details of the top ten category views, <A HREF="javascript:document.StatsForm.ShopCatParam.value=\'\';document.StatsForm.submit();">click here</A>.';
		} else {
			$this->showAllParameters .= "&AllShopStats=Yes";
			$allcathitsDefined = 'Below are the statistics for the top ten category views on your website.  For details of the hits on all your category views <A HREF="javascript:document.StatsForm.ShopCatParam.value=\'Yes\';document.StatsForm.submit();">click here</A>.';
		}
	}
	
	$Q_OrderHits = query("
			SELECT Sum( orpr_qty ) AS Hits, orpr_pr_name
			FROM shopsystem_order_products
			WHERE 1 = 1
			$orderWhereSQL
			GROUP BY orpr_pr_name
			ORDER BY Hits DESC
	");	
	
	$orderhits = $Q_OrderHits->columnValuesArray('Hits');
	
	$totalOrderHits = 0;
	
	foreach ($orderhits as $hit) {
		$totalOrderHits += $hit;
	}
	if (!array_key_exists('Service', $this->ATTRIBUTES)) {		
		if (strlen($this->ATTRIBUTES["ShopOrderParam"])) {
			$this->showAllParameters .= "&AllShopStats=";
			$allorderhitsDefined = 'Below are the statistics for all the product orders on your website.  To return to the details of the top ten product orders, <A HREF="javascript:document.StatsForm.ShopOrderParam.value=\'\';document.StatsForm.submit();">click here</A>.';
		} else {
			$this->showAllParameters .= "&AllShopStats=Yes";
			$allorderhitsDefined = 'Below are the statistics for the top ten product orders on your website.  For details of the hits on all your product orders <A HREF="javascript:document.StatsForm.ShopOrderParam.value=\'Yes\';document.StatsForm.submit();">click here</A>.';
		}
	}
	
	if (ss_optionExists("Shop Acme Rockets")) {
	
		$Q_WishHits = query("
				SELECT COUNT(stn_email)AS Hits , pr_name
				FROM shopsystem_stock_notifications, shopsystem_products, shopsystem_product_extended_options
				WHERE 1 = 1
					AND stn_stock_code = pro_stock_code
					AND pro_pr_id = pr_id
				GROUP BY stn_stock_code
				ORDER BY Hits DESC
		");	
		
		$wishHits = $Q_WishHits->columnValuesArray('Hits');
		
		$totalWishHits = 0;
		
		foreach ($wishHits as $hit) {
			$totalWishHits += $hit;
		}
		if (!array_key_exists('Service', $this->ATTRIBUTES)) {		
			if (strlen($this->ATTRIBUTES["ShopWishListParam"])) {
				$this->showAllParameters .= "&AllShopStats=";
				$allwishhitsDefined = 'Below are the statistics for all the products wished for on your website.  To return to the details of the top ten product wishes, <A HREF="javascript:document.StatsForm.ShopWishListParam.value=\'\';document.StatsForm.submit();">click here</A>.';
			} else {
				$this->showAllParameters .= "&AllShopStats=Yes";
				$allwishhitsDefined = 'Below are the statistics for the top ten products wished for on your website.  For details all products wished for <A HREF="javascript:document.StatsForm.ShopWishListParam.value=\'Yes\';document.StatsForm.submit();">click here</A>.';
			}
		}
	}
	
?>