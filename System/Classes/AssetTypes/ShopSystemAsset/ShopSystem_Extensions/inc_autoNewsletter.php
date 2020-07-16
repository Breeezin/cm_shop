<?php
	
	$Q_TopRated = query("
		SELECT pr_id, pr_name, pr_customer_rating, pr_customer_rating_count, pr_image1_normal FROM shopsystem_products
		WHERE pr_customer_rating IS NOT NULL AND
			pr_customer_rating_count IS NOT NULL
		ORDER BY pr_customer_rating DESC
		LIMIT 5
	");
	$Q_TopRated->preFetch();


	$Q_TopSold = query("
		SELECT pr_name, pr_image1_normal, pr_id, SUM(op_quantity) AS AmountSold
		FROM ordered_products, shopsystem_products
		WHERE op_pr_id = pr_id AND op_price_paid > 0
		GROUP BY pr_id
		ORDER BY AmountSold DESC
		LIMIT 5
	");
	$Q_TopSold->preFetch();

	// find a news asset
	$news = getRow("
		SELECT * FROM assets
		WHERE as_type LIKE 'News'
	");	
	
	$oneWeekAgo = ss_TimeStampToSQL(time()-60*60*24*7);
	$Q_LatestNews = query("
		SELECT * FROM news_items 
		WHERE nei_as_id = 521
		ORDER BY nei_timestamp DESC, nei_id DESC
		LIMIT 4
	");
	$Q_LatestNews->preFetch();
	
	$LatestStock = getRow("
		SELECT * FROM news_items
		WHERE nei_headline LIKE 'Latest Stock'
		ORDER BY nei_timestamp DESC
		LIMIT 1
	");
	if ($LatestStock !== null) {
		$LatestStock = ss_parseText($LatestStock['nei_body']);
	}

	// http://www.acmerockets.com/Shop_System/Service/Engine/Template/AutoNewsletterSpecials/Specials/1/MainLayout/none
	$result = new Request('Asset.Display',array(
		'AssetPath'	=>	'Shop_System',
		'Service'	=>	'Engine',
		'Template'	=>	'AutoNewsletterSpecials',
		'Specials'	=>	1,
		'MainLayout'	=>	'none',
		'NoApprox'	=>	1,
	));

	$currentSpecials = $result->display;
	str_replace('<link rel="stylesheet" href="sty_shop.css" type="text/css">','',$currentSpecials);
	

	$result = new Request('Asset.Embed',array(
		'as_id'	=>	677,
	));
	$topText = $result->display;
	
	if (date('Y') == 2004) {
		$num = date('W')-46;
	} else {
		$num = date('W');
	}

	$data = array(
		'Points'	=>	'60',
		'last_name'	=>	'Twain',
		'first_name'	=>	'Mark',
		'Q_TopRated'	=>	$Q_TopRated,
		'Q_TopSold'	=>	$Q_TopSold,
		'Q_LatestNews'	=>	$Q_LatestNews,
		'LatestStock'	=>	$LatestStock,
		'AssetPath'	=>	'Shop_System',
		'CurrentSpecials'	=>	$currentSpecials,
		'TopText'	=>	$topText,
		'Quote'	=>	$quote,
		'Vol'	=>	date('Y')-2003,
		'Num'	=>	$num,
		
	);

	$ThisWeeks = getRow("
		SELECT * FROM lottery_winners, shopsystem_orders, users
		WHERE lotw_this_week = 1
			AND lotw_or_id = or_id
			AND or_us_id = us_id
	");
	if ($ThisWeeks !== null) {
		$data['GotPrize'] = true;
		$data['Winner'] = $ThisWeeks['or_purchaser_firstname'];
		$data['WinnerState'] = $ThisWeeks['us_0_50A2'];
		$country = getRow("
			SELECT * FROM countries
			WHERE cn_id = ".ListFirst($ThisWeeks['us_0_50A4'],'&')."
		");
		$data['WinnerCountry'] = $country['cn_name'];
		$thisWeekBox = getRow("
			SELECT pr_name, pr_image1_normal FROM shopsystem_products
			WHERE pr_id = {$ThisWeeks['lotw_pr_id']}
		");	
		$data['ThisWeekBox'] = $thisWeekBox['pr_name'];
		$data['CigarImage'] = '';
		if ($thisWeekBox['pr_image1_normal'] !== null) {
			$data['CigarImage'] = '<img src="index.php?act=ImageManager.get&Image=Custom/ContentStore/Assets/5/14/ProductImages/'.$thisWeekBox['pr_image1_normal'].'&Size=160x160&Rotate=270">';			
		}
		
		$NextWeeks = getRow("
			SELECT * FROM lottery_winners 
			WHERE lotw_draw_date IS NULL
				AND lotw_upcoming = 1
		");
		if ($NextWeeks !== null) {
			$nextWeekBox = getRow("
				SELECT pr_name FROM shopsystem_products
				WHERE pr_id = {$NextWeeks['lotw_pr_id']}
			");	
			$data['NextWeekBox'] = $nextWeekBox['pr_name'];
		} else {
			$data['NextWeekBox'] = null;
		}	
		
	} else {
		$data['GotPrize'] = false;	
	}
	
	
?>
