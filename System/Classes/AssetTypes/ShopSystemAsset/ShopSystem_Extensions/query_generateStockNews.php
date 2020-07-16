<?php

	$lastItem = getRow("
		SELECT * FROM news_items WHERE nei_headline LIKE 'Latest Stock'		
	");
	if ($lastItem === null) {
		// a week ago
		$lastDate = date("Y-m-d H:i:s",time()-(60*60*24*7));
	} else {
		$lastDate = $lastItem['nei_timestamp'];
		$Q_Delete = query("
			DELETE FROM news_items WHERE nei_id = {$lastItem['nei_id']}
		");
	}
	
	// find the updated products
	$Q_Products = query("
		SELECT * FROM shopsystem_products, shopsystem_product_extended_options
		WHERE pro_pr_id = pr_id
			AND pro_date_in_stock IS NOT NULL
			AND pro_date_in_stock > '$lastDate'
		ORDER BY pro_date_in_stock ASC
	");
	
	// create the description text
	$text = 'The following products are now in stock:<ul>';
	while ($prod = $Q_Products->fetchRow()) {
		$text .= '<li>';
		$text .= date('d M - ',ss_SQLtoTimeStamp($prod['pro_date_in_stock'])).' <a href="Shop_System/Service/Detail/Product/'.$prod['pr_id'].'">'.(ss_HTMLEditFormat($prod['pr_name'])).'</a> - ';
		

		$text .= '</li>';
	}
	$text .= '</ul>';
	
	// find a news asset
	$news = getRow("
		SELECT * FROM assets
		WHERE as_type LIKE 'News'
	");

	/*if ($lastItem === null) {
	} else {
	//	location("index.php?act=
	}*/
		location("index.php?act=NewsItemsAdministration.New&PushValues=1&nei_timestamp=".ss_URLEncodedFormat(date('Y-m-d H:i:00'))."&nei_headline=Latest%20Stock&nei_body=".ss_URLEncodedFormat($text)."&as_id=".$news['as_id']."&BackURL=".ss_URLEncodedFormat("index.php?act=ShopSystem.AcmeComplete").'&user_groups'.ss_URLEncodedFormat('[]').'=6');
	
?>
