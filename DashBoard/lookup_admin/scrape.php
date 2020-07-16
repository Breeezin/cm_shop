<?php
require("db.php");
require("auth.php");
echo "<style type=\"text/css\">@import url(\"site.css\");</style>";

// TODO, this needs refactoring.  Alternate return values depending on method, not just failure or zero bytes.
// Rex

$parent_script = "index.php";
ignore_user_abort( 1 );
$enabled = 0;

$debug = false;
if( array_key_exists( 'debug', $_GET ) and (int)$_GET['debug'] > 0 )
	$debug = true;

$reset = false;
if( array_key_exists( 'reset', $_GET ) and (int)$_GET['reset'] > 0 )
	$reset = true;

//ini_set("user_agent", "Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 5.1; GTB6; .NET CLR 1.1.4322; .NET CLR 2.0.50727)" );
ini_set("user_agent", "Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 6.1; WOW64; Trident/6.0)" );
@apache_setenv('no-gzip', 1);
@ini_set('zlib.output_compression', 0);
@ini_set('implicit_flush', 1);
for ($i = 0; $i < ob_get_level(); $i++) { ob_end_flush(); }
ob_implicit_flush(1);

echo "<head></head><body>";
if( IsSet( $parent_script ) )
	echo "<p><a href=\"$parent_script\">Back</a></p>";

?>

<SCRIPT language="Javascript">
	function enable_check(name)
		{
		box = eval("document.UpdateProducts."+name); 
		box.checked = true;
		}
</SCRIPT>

<?php

$last_URL = "";
$URL_contents = "";
$comp = "";
if( array_key_exists( "co_id", $_GET ) )
	{
	$c = (int) $_GET['co_id'];
	if( $c > 0 )
		$comp = " and co_id = $c";
	}

$dbC->query( "drop table if exists back_in_stock_check" );
if( $reset )
	$dbC->query( "create table back_in_stock_check as select co_id, cp_pr_id, max( cs_id ) as bs_id from competitor_scraper join competitor on cs_co_id = co_id join competitor_prices on cp_co_id = cs_co_id and cs_pr_id = cp_pr_id where co_scrape = true and cs_active = false $comp group by co_id, cp_pr_id" );
else
	$dbC->query( "create table back_in_stock_check as select co_id, cp_pr_id, max( cs_id ) as bs_id from competitor_scraper join competitor on cs_co_id = co_id join competitor_prices on cp_co_id = cs_co_id and cs_pr_id = cp_pr_id where co_scrape = true and cs_active = false and (cp_price > 0 or cp_special_price > 0 ) $comp group by co_id, cp_pr_id" );

if( $debug )
	$qry = "select * from competitor_scraper join competitor on cs_co_id = co_id join shopsystem_products on cs_pr_id = pr_id where co_scrape = true and cs_active = false $comp and cs_scrape IS NOT NULL order by cs_url";
else
//	if( array_key_exists( "co_id", $_GET ) )
//		$qry = "select * from competitor_scraper join competitor on cs_co_id = co_id join shopsystem_products on cs_pr_id = pr_id where co_scrape = true and cs_active = true $comp order by cs_url";
//	else
		$qry = "select * from competitor_scraper join competitor on cs_co_id = co_id join shopsystem_products on cs_pr_id = pr_id where co_scrape = true and (cs_active = true $comp) or cs_id in (select bs_id from back_in_stock_check) order by cs_url";

echo $qry."<br/>";
$Q = $dbC->query( $qry );

while( $row = $dbC->fetch_assoc( $Q ) )
	{
//	print_r( $row );
	echo "<br/>\n";
	if( $row['cs_url'] != $last_url )
		{
		$last_url = $row['cs_url'];

		$opts = array( 'http'=>array( 'method'=>"GET",
										'header'=>"Accept-language: en\r\n" .
										"Referer: http://'.{$row['co_base_url']}\r\n"
					  ));

		if( strlen( $row['co_cookies'] ) )
			$opts['http']['header'] .= "Cookie: ".$row['co_cookies']."\r\n";

		echo "grabbing $last_url <br/>\n";
		flush();
		$context = stream_context_create($opts);
		if( $debug )
			$page = gzinflate($row['cs_scrape']);
		else
			{
			//$page = file_get_contents( $last_url, false, $context );
			// 127.0.0.1:8123
			$ch = curl_init( );
			curl_setopt($ch, CURLOPT_URL, str_replace( ' ', '%20', $last_url) );
			curl_setopt($ch, CURLOPT_PROXY, 'http://127.0.0.1');
			curl_setopt($ch, CURLOPT_PROXYPORT, 8123);
			curl_setopt($ch, CURLOPT_REFERER, 'http://'.$row['co_base_url']);
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_AUTOREFERER, true);
			curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
			//curl_setopt($ch, CURLOPT_COOKIEJAR, '-');
			curl_setopt($ch, CURLOPT_COOKIESESSION, true);
			curl_setopt($ch, CURLOPT_COOKIEFILE, '/tmp/cookie'.$row['co_id']);
			curl_setopt($ch, CURLOPT_COOKIEJAR, '/tmp/cookie'.$row['co_id']);
			if( strlen( $row['co_cookies'] ) )
				{
				curl_setopt($ch, CURLOPT_COOKIE, $row['co_cookies']);
				echo "Setting cookie '{$row['co_cookies']}'<br/>";
				}

			if( $row['cs_post'] )
				{
				curl_setopt($ch, CURLOPT_POST, true);
				curl_setopt($ch, CURLOPT_POSTFIELDS,  $row['cs_post_data']);
				curl_setopt($ch, CURLOPT_LOW_SPEED_TIME, 120);
				curl_setopt($ch, CURLOPT_TIMEOUT, 120);

				echo "posting {$row['cs_post_data']} to above URL<br/>";
				}

			if( strlen( $row['co_session_call_url'] ) )
			{
				curl_setopt($ch, CURLOPT_URL, str_replace( ' ', '%20', $row['co_session_call_url']) );
				$page = curl_exec($ch);
				echo "retrieved ".strlen( $page )." bytes from URL<br/>";
				curl_setopt($ch, CURLOPT_URL, str_replace( ' ', '%20', $last_url) );
			}

			$page = curl_exec($ch);
			echo "retrieved ".strlen( $page )." bytes from URL<br/>";
			curl_close( $ch );

			if( strlen( $page ) <= 7000 )
				{
				echo "Tor retrieve failed<br/>";
				echo htmlentities( $page );
				echo "Trying direct<br/>";
				$page = file_get_contents( str_replace( ' ', '%20', $last_url), false, $context );
				if( $page === false )
					{
					echo "file_get_contents( $last_url ) failed<br/>";
					}
				else
					{
					echo "retrieved ".strlen( $page )." bytes from URL<br/>";
					$search = array( '/\s+/' );
					$replace =  array( ' ' );
					$page = preg_replace( $search, $replace, $page );
					}
				}
			}

		// remove all newlines
		$search = array( '/\r/', '/\n/' );
		$replace =  array( ' ', ' ' );
		$URL_contents = preg_replace( $search, $replace, $page );
		echo "Squashed to ".strlen( $URL_contents )." bytes from URL<br/>";
		}

	$found = false;
	$out_of_stock = 0;
	$start = str_replace(array('/', '$'), array('\/', '\$'), $row['cs_start_delimiter']);
	$end = str_replace('/', '\/', $row['cs_end_delimiter']);

	if( strlen($row['cs_end_pattern']) ) 					// reduce page to haystack defined by start and end pattern, for those all in one pages.
		{
		if( strlen($row['cs_start_pattern']) )
			{
			$block1_start = str_replace(array('/', '$'), array('\/', '\$'), $row['cs_start_pattern']);
			if( $pos = strpos( $block1_start, ".*" ) )
				$block1_start = substr( $block1_start, 0, $pos );

			$block2_start = $block1_start;
			}
		else
			{
			$block1_start = str_replace(array('/', '$'), array('\/', '\$'), $row['cs_start_delimiter']);
			if( $pos = strpos( $block1_start, ".*" ) )
				$block1_start = substr( $block1_start, 0, $pos );

			$block2_start = str_replace(array('/', '$'), array('\/', '\$'), $row['cs_start_delimiter2']);
			if( $pos = strpos( $block2_start, ".*" ) )
				$block2_start = substr( $block2_start, 0, $pos );
			}

		$block_end = str_replace('/', '\/', $row['cs_end_pattern'] );
		echo "block start is $block1_start or $block2_start end pattern is $block_end";
		$pattern1 = "/$block1_start.*?$block_end/";
		$pattern2 = "/$block2_start.*?$block_end/";
		preg_match( $pattern1, $URL_contents, $block1 );
		preg_match( $pattern2, $URL_contents, $block2 );
		echo "pattern1 = $pattern1, pattern2 = $pattern2, block1 length = ".strlen( $block1[0] ).", block2 length = ".strlen( $block2[0] ).", page length = ".strlen($URL_contents)."<br/>";
		$haystack = $block1[0];
		if( strlen( $block2[0] ) > $haystack )
			$haystack = $block2[0];
		}
	else												// otherwise haystack is the whole page
		$haystack = $URL_contents;

	echo "Haystack is ".strlen( $haystack )." bytes<br/>";

	if( strlen( $haystack ) )
		{

		if( strlen( $row['cs_out_of_stock'] ) )				// out of stock?
			{
			$oos = str_replace('/', '\/', $row['cs_out_of_stock']);
			$out_of_stock = preg_match( "/$oos/", $haystack );
			}

		if( !( $out_of_stock >= 0 ) )
			$out_of_stock = 0;

		preg_match( "/$start.*?$end/", $haystack, $matches );
		$instance = $matches[$row['cs_instance']-1];

		$pattern = "/.*$start\\s*([0-9\\.,]+?)\\s*$end.*/";
		echo "search pattern1 is ".htmlentities($pattern)."<br/>\n";
		$pr = '\1';
		$price = trim(preg_replace( $pattern, $pr, $instance ));
		$price = str_replace( ",", "", $price );
		echo "replace returned '$price'<br/>";
		if( $price[0] == '$' )
			$price = substr( $price, 1 );
		if( (double) $price > 0 )
			{
			$found = true;
			$p = (double) $price;
			$dbC->query( "insert into competitor_prices (cp_co_id, cp_pr_id, cp_price, cp_out_of_stock) values ({$row['co_id']}, {$row['cs_pr_id']}, $p, $out_of_stock)" );
			echo "insert into competitor_prices (cp_co_id, cp_pr_id, cp_price, cp_out_of_stock) values ({$row['co_id']}, {$row['cs_pr_id']}, $p, $out_of_stock);<br/>\n";
			if( !$row['cs_active'] )
				{
				echo "re-enabling scraper id ".$row['cs_id']."<br/>";
				$dbC->query( "update competitor_scraper set cs_active = true where cs_id = {$row['cs_id']}" );
				$enabled++;
				}
			}
		else
			{
			if( strlen( $row['cs_start_delimiter2'] ) )
				{
				$start = str_replace(array('/', '$'), array('\/', '\$'), $row['cs_start_delimiter2']);
				if( strlen( $row['cs_end_delimiter2']) )
					$end = str_replace('/', '\/', $row['cs_end_delimiter2']);

				preg_match( "/$start.*?$end/", $haystack, $matches );
				$instance = $matches[$row['cs_instance']-1];

				$pattern2 = "/.*$start\\s*([0-9\\.,]+?)\\s*$end.*/";
				echo "search pattern2 is ".htmlentities($pattern2)."<br/>\n";
				$pr = '\1';
				$price = trim(preg_replace( $pattern2, $pr, $instance ));
				$price = str_replace( ",", "", $price );
				echo "replace returned '$price'<br/>";
				if( (double) $price > 0 )
					{
					$found = true;
					$p = (double) $price;
					$dbC->query( "insert into competitor_prices (cp_co_id, cp_pr_id, cp_price, cp_out_of_stock) values ({$row['co_id']}, {$row['cs_pr_id']}, $p, $out_of_stock)" );
					echo "insert into competitor_prices (cp_co_id, cp_pr_id, cp_price, cp_out_of_stock) values ({$row['co_id']}, {$row['cs_pr_id']}, $p, $out_of_stock);<br/>\n";
					if( !$row['cs_active'] )
						{
						echo "re-enabling scraper id ".$row['cs_id']."<br/>";
						$dbC->query( "update competitor_scraper set cs_active = true where cs_id = {$row['cs_id']}" );
						$enabled++;
						}
					}
				}
			}

		if( !$found )
			{
			echo "scrape for {$row['pr_name']} failed from {$row['cs_url']} disabling this rule {$row['cs_id']}<br/>\n";
			$dbC->query( "insert into competitor_prices (cp_co_id, cp_pr_id, cp_price, cp_out_of_stock) values ({$row['co_id']}, {$row['cs_pr_id']}, 0, 1)" );

			echo "<a href='show_scrape.php?id={$row['cs_id']}'>Show Scrape</a><br/>\n";
			if( strlen( $price ) < 10 )
				{
				echo "replace returned ".htmlentities( $price )."<br/>\nAll matches is ";
				echo htmlentities( print_r( $matches, true ) );
				echo "<br/>\n";
				}
			else
				echo "replace simply failed<br/>\n";

			if( !$debug )
				{
				$dbC->query( "update competitor_scraper set cs_active = false, cs_scrape = '".mysql_real_escape_string(gzdeflate($URL_contents))."' where cs_id = {$row['cs_id']}" );
				}
			}
		}
	else
		{
		echo "scrape for {$row['pr_name']} failed from {$row['cs_url']} disabling this rule {$row['cs_id']}<br/>\n";
		$dbC->query( "insert into competitor_prices (cp_co_id, cp_pr_id, cp_price, cp_out_of_stock) values ({$row['co_id']}, {$row['cs_pr_id']}, 0, 1)" );

		echo "<a href='show_scrape.php?id={$row['cs_id']}'>Show Scrape</a><br/>\n";
		if( strlen( $price ) < 10 )
			{
			echo "replace returned ".htmlentities( $price )."<br/>\nAll matches is ";
			echo htmlentities( print_r( $matches, true ) );
			echo "<br/>\n";
			}
		else
			echo "replace simply failed<br/>\n";

		if( !$debug )
			{
			$dbC->query( "update competitor_scraper set cs_active = false, cs_scrape = '".mysql_real_escape_string(gzdeflate($URL_contents))."' where cs_id = {$row['cs_id']}" );
			}
		}
	}

	echo "Done.  Re-enabled $enabled rules";
?>
