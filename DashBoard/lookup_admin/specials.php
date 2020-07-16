<?php
require("db.php");
require("db_shared2.php");
require("auth.php");

function getRealExchangeRate( $from, $to )
{
	global $dbS;

	if( $from != $to )
		{
		$rate = 0;

		$ExchangeQ = $dbS->query("select * from ExchangeRates where Source = '$from' and Dest = '$to'");
		$ExchRow = $dbS->fetch_assoc( $ExchangeQ );

		if( $ExchRow )
			$rate = $ExchRow['Rate'];
		}
	else
		$rate = 1;

	return $rate;
}

function short_number_format( $in )
{
	if( ($in - (int)$in) == 0 )
		return $in;
	else
		return number_format( $in, 2 );
}

// we want products where
//  1 lots of stock
//  2 lots of profit
//  3 cheapest out of all competitors
//  

$dbC->query("drop table if exists best_specials" );
$dbC->query("create table best_specials as select pr_id, pr_name, pr_image1_normal, pro_special_price, pro_price, IF(Rate IS NULL, 0, Rate) as Freight, pro_supplier_price, pro_stock_available from shopsystem_products join shopsystem_product_extended_options on pro_pr_id = pr_id join ShopSystem_FreightRates on FreightCodeLink = PrExOpFreightCodeLink where pro_special_price IS NOT NULL and pro_deleted IS NULL and pr_offline IS NULL and pr_ve_id = 2 and pro_stock_available > 10" );

$q = $dbC->query("select * from competitor");
$competitors = $dbC->fetch_all( $q );

$skipped = array();

echo "<h1>Cheapest</h1><br />";

$Q = $dbC->query("select * from best_specials order by pro_stock_available*(pro_special_price + Freight-pro_supplier_price) desc");
while( $row = $dbC->fetch_assoc( $Q ) )
	{
	extract( $row );
	
	$skip = false;
	foreach ( $competitors as $competitor )
		{
		$cpq = $dbC->query( "select * from competitor_prices join competitor on co_id = cp_co_id where co_active > 0 and cp_pr_id = $pr_id and cp_co_id = {$competitor['co_id']} order by cp_id desc limit 1" );
		if( $cp = $dbC->fetch_assoc( $cpq ) )
			{
			if( !$cp['cp_out_of_stock'] )
				{
				if( ($cp['cp_price'] > 0) and getRealExchangeRate($cp['co_currency'], 'EUR')*$cp['cp_price'] < ($pro_special_price+$Freight) )
					$skip = true;
				if( ($cp['cp_special_price'] > 0) and getRealExchangeRate($cp['co_currency'], 'EUR')*$cp['cp_special_price'] < ($pro_special_price+$Freight) )
					$skip = true;
				}
			}
		}

	if( !$skip )
		{
		echo "<a href='http://www.acmerockets.com/Shop_System/Service/Detail/Product/$pr_id'><IMG border='0' src='http://www.acmerockets.com/index.php?act=ImageManager.get&ProductThumb=$pr_id' alt='$pr_name' /></a><br />";
		echo "<strong>$pr_name</strong> was <del>&euro;".short_number_format($pro_price+$Freight)."</del> now &euro;".short_number_format($pro_special_price+$Freight)."<br /><br />\n";
		}
	else
		{
		$skipped[] = "<a href='http://www.acmerockets.com/Shop_System/Service/Detail/Product/$pr_id'><IMG border='0' src='http://www.acmerockets.com/index.php?act=ImageManager.get&ProductThumb=$pr_id' alt='$pr_name' /></a><br />";
		$skipped[] = "<strong>$pr_name</strong> was <del>&euro;".short_number_format($pro_price+$Freight)."</del> now &euro;".short_number_format($pro_special_price+$Freight)."<br /><br />\n";
		}

	}

if( count($skipped) > 0 )
	{
	echo "<h1>Not Cheapest</h1><br />";
	foreach( $skipped as $skip )
		echo $skip;
	}
?>
