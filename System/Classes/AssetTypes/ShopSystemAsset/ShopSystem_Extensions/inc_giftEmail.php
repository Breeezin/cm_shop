<?

	// this stuff is from inc_newsletter...
	
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
		'Q_TopRated'	=>	$Q_TopRated,
		'Q_TopSold'	=>	$Q_TopSold,
		'Q_LatestNews'	=>	$Q_LatestNews,
		'LatestStock'	=>	$LatestStock,
		'AssetPath'	=>	'Shop_System',
		'CurrentSpecials'	=>	$currentSpecials,
	);

?>
