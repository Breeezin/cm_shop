<?php
require("db.php");
require("db_shared2.php");
require("auth.php");
echo "<style type=\"text/css\">@import url(\"site.css\");</style>";

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

$parent_script = "index.php";

	{
	if( $_POST['force'] > 0 )
		{
		echo "Updating forced exchange rate<br/>";
		$dbS->query( "update ExchangeRates set ForceRate = ".(double)$_POST['force']." where Source = 'EUR' and Dest = 'USD'" );
		}
	else
		if( array_key_exists( 'force', $_POST ) )
			{
			echo "Clearing forced exchange rate<br/>";
			$dbS->query( "update ExchangeRates set ForceRate = NULL where Source = 'EUR' and Dest = 'USD'" );
			}
	}

echo "<head></head><body>";
if( IsSet( $parent_script ) )
	echo "<p><a href=\"$parent_script\">Back</a></p>";

$ExchangeQ = $dbS->query("select * from ExchangeRates where Source = 'EUR' and Dest = 'USD'");
$ExchRow = $dbS->fetch_assoc( $ExchangeQ );

if( !$ExchRow )
	{
	echo "No Exchange rate for EUR/USD";
	die;
	}
?>

<SCRIPT language="Javascript">
	function enable_check(name)
		{
		box = eval("document.UpdateProducts."+name); 
		box.checked = true;
		}


</SCRIPT>

<?php

if( array_key_exists( 'sort', $_GET ) )
	$order = (int)$_GET['sort'];
else
	if( array_key_exists( 'sort', $_POST ) )
		$order = (int)$_POST['sort'];
	else
		$order = 3;

switch( $order )
{
	case -1:
		$OrderBy = "pr_id asc";
		break;
	case -2:
		$OrderBy = "pr_name asc";
		break;
	case -3:
		$OrderBy = "pro_stock_available asc";
		break;
	case -4:
		$OrderBy = "pro_supplier_price asc";
		break;
	case 1:
		$OrderBy = "pr_id desc";
		break;
	case 2:
		$OrderBy = "pr_name desc";
		break;
	case 3:
		$OrderBy = "pro_stock_available desc";
		break;
	case 4:
		$OrderBy = "pro_supplier_price desc";
		break;
}

echo "<input type='hidden' name='sort' value='$order'/>";
echo "Current EUR/USD exchange rate {$ExchRow['Rate']} last updated {$ExchRow['LastUpdated']}, Your override <input type='text' name='force' value='{$ExchRow['ForceRate']}'/>";
$rate = $ExchRow['ForceRate'];
if( !$rate )
	$rate = $ExchRow['Rate'];
echo "<input type='submit' value='Update' name='Submit'/>";


function table_header()
{
	global $comp;
	echo "<tr><td>Select</td>";
	echo "<td><a href=price_dashboard_2.php?sort=".(abs($order)==1?-$order:1).">pr_id</a></td>";
	echo "<td><a href=price_dashboard_2.php?sort=".(abs($order)==2?-$order:2).">Name</a></td>";
	echo "<td><a href=price_dashboard_2.php?sort=".(abs($order)==3?-$order:3).">Stock</a></td>";
	echo "<td><a href=price_dashboard_2.php?sort=".(abs($order)==4?-$order:4).">Purchase<br/> USD</a></td>";
	echo "<td>Retail<br/> USD</td>";
	echo "<td>Special<br/> USD</td>";
	echo "<td>Freight</td>";
	echo "<td>Profit<br/>%</td>";

	foreach( $comp as $r )
		echo "<td>{$r['co_name']}</td>";
}


$ProductQ = $dbC->query("select *, pro_price+IF(Rate IS NULL, 0, Rate)  as products_price, pro_special_price+IF(Rate IS NULL, 0, Rate)  as special_price, IF(pro_special_price IS NOT NULL, pro_special_price, pro_price) as final_price, IF(Rate IS NULL, 0, Rate) as freight from shopsystem_products join shopsystem_product_extended_options on pr_id = pro_pr_id left join ShopSystem_FreightRates on FreightCodeLink = PrExOpFreightCodeLink  where pr_ve_id = 2 and pro_source_currency = 'USD' and pr_offline IS NULL and pro_stock_available > 0 order by $OrderBy");

$comp = array();
$CompQ = $dbC->query("select * from competitor where co_active > 0");
while( $r = $dbC->fetch_assoc( $CompQ ) )
	$comp[] = $r;

echo "<input type='hidden' name='sort' value='$order'/>";
echo "<table border=1>\n";

table_header();
echo "</td></tr>\n";

$counter = 0;
while( $r = $dbC->fetch_assoc( $ProductQ ) )
	{
	if( ++$counter > 20 )
	{
		table_header();
		$counter = 0;
	}
	$usd_price = number_format($r['products_price'], 2, '.', '');
	$lowest = $usd_price;
	$lowest_id = -1;
	$usd_special_price = number_format($r['special_price'], 2, '.', '');
	if( ( $usd_special_price > 0 ) && ($usd_special_price < $lowest ) )
		$lowest = $usd_special_price;
	if( $r['pro_price'] > 0 )
		if( $r['pro_special_price'] )
			$profit = number_format( 100*($r['pro_special_price']-$r['pro_supplier_price'])/$r['pro_special_price'], 2, '.', '');
		else
			$profit = number_format( 100*($r['pro_price']-$r['pro_supplier_price'])/$r['pro_price'], 2, '.', '');
	else
		$profit = 'NaN';
	echo "<tr>";
	echo "<td><input type='CheckBox' name='Update_{$r['pr_id']}' value='1'/></td>";
	echo "<td>{$r['pr_id']}</td>";
	echo "<td>{$r['pr_name']}</td>";
	echo "<td>{$r['pro_stock_available']}</td>";
	echo "<td>{$r['pro_supplier_price']}</td>";
	echo "<td>{$usd_price}</td>";
//	echo "<td><input type='text' name='USD_{$r['pr_id']}' value='$usd_price' size=7 onchange=enable_check('Update_{$r['pr_id']}') /></td>";
	if( $r['pro_special_price'] )
		//echo "<td><input type='text' name='USD_special_{$r['pr_id']}' value='$usd_special_price' size=7 onchange=enable_check('Update_{$r['pr_id']}') /></td>";
		echo "<td>{$usd_special_price}</td>";
	else
		echo "<td></td>";
		//echo "<td><input type='text' name='USD_special_{$r['pr_id']}' value='' size=7 onchange=enable_check('Update_{$r['pr_id']}') /></td>";
	echo "<td>{$r['freight']}</td>";
	if( $profit < 25 )
		{
		echo "<td><span style='color:orange'>$profit</span></td>";
		}
	else
		echo "<td>$profit</td>";
	$prices = array();
	$no_stock = array();

	foreach( $comp as $c )
		{
		$prices[$c['co_id']] = array();
		$no_stock[$c['co_id']] = array();

		$id = 0;
		$cpq = $dbC->query( "select cs_id from competitor_scraper where cs_pr_id = {$r['pr_id']} and cs_co_id = {$c['co_id']} limit 1" );
			if( $cp = $dbC->fetch_assoc( $cpq ) )
				$id = $cp['cs_id'];

		$cpq = $dbC->query( "select * from competitor_prices join competitor on co_id = cp_co_id where cp_pr_id = {$r['pr_id']} and cp_co_id = {$c['co_id']} order by cp_id desc limit 1" );
		if( $cp = $dbC->fetch_assoc( $cpq ) )
			{
			$prices[$c['co_id']][$id] = getRealExchangeRate($cp['co_currency'], 'USD')*$cp['cp_price'];
			$no_stock[$c['co_id']][$id] = $cp['cp_out_of_stock'];
			if( !$cp['cp_out_of_stock'] && ($prices[$c['co_id']][$id] < $lowest) && ( $prices[$c['co_id']][$id] > 0 ))
				{
				$lowest = $prices[$c['co_id']][$id];
				$lowest_id = $id;
				}
			}
		}

	foreach( $comp as $c )
		{
		$id = 0;
		$url = '';
		$cpq = $dbC->query( "select cs_id, cs_url from competitor_scraper where cs_pr_id = {$r['pr_id']} and cs_co_id = {$c['co_id']} limit 1" );
		if( $cp = $dbC->fetch_assoc( $cpq ) )
			{
			$id = $cp['cs_id'];
			$url = $cp['cs_url'];
			}
		else
			{
			// insert it and ask again...
			$dbC->query( "insert into competitor_scraper (cs_pr_id, cs_co_id, cs_start_delimiter, cs_end_delimiter, cs_start_delimiter2, cs_end_delimiter2, cs_out_of_stock)"
				. " select {$r['pr_id']}, df_co_id, df_start_delimiter, df_end_delimiter, df_start_delimiter2, df_end_delimiter2, df_out_of_stock from "
				. " default_scraper where df_co_id = {$c['co_id']}" );
			$cpq = $dbC->query( "select cs_id, cs_url from competitor_scraper where cs_pr_id = {$r['pr_id']} and cs_co_id = {$c['co_id']} limit 1" );
				if( $cp = $dbC->fetch_assoc( $cpq ) )
					{
					$id = $cp['cs_id'];
					$url = $cp['cs_url'];
					}
			}

		echo "<td><span style='text-align:left;'>";
		if( array_key_exists( $id, $prices[$c['co_id']] ) )
			{
			if( $lowest_id == $id )
				echo "<span style='color:red'>";
			if( $no_stock[$c['co_id']][$id] )
				echo "<del>";
			echo number_format( $prices[$c['co_id']][$id] ,2);
			if( $no_stock[$c['co_id']][$id] )
				echo "</del>";
			if( $lowest_id == $id )
				echo "</span>";
			}
		else
			echo "&nbsp;";
		echo "</span>";
		echo "</td>";
		}

	echo "</tr>\n";
	}
table_header();

echo "</table>";
echo "<input type='submit' name='Submit'/>";

