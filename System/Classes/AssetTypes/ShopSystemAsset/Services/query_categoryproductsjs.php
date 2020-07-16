<?php
	$this->param('ca_id',-1,-1);
	$this->param('CallBack','parent.updateProducts(p);','parent.updateProducts(p);');
	$this->param('OfflineProducts',0,0);

	$StockLevelSQL = 'AND (pro_stock_available IS NULL OR pro_stock_available > 0)';
	if (array_key_exists('OutOfStockOnly',$this->ATTRIBUTES)) {
		$StockLevelSQL = 'AND (pro_stock_available <= 0)';
	}
	
	$offlineSQL = 'AND pr_offline IS NULL';
	if ($this->ATTRIBUTES['OfflineProducts']) {
		$offlineSQL = '';
	}

	if( ss_AuthdCustomer( ) )
		$zonefield = 'pr_authd_sales_zone';
	else
		$zonefield = 'pr_sales_zone';

	if( strlen($_SESSION['ForceCountry']['cn_sales_zones']) )
		$sales_zone = "and $zonefield in (".$_SESSION['ForceCountry']['cn_sales_zones'].")";
	else
		$sales_zone = '';

	if( array_key_exists('us_admin_level', $_SESSION['User'] ) && ( $_SESSION['User']['us_admin_level'] !== NULL ) && ( $_SESSION['User']['us_admin_level'] == 0 ) )
		$sales_zone = '';

/*
	if( ss_isAdmin() )
		$sales_zone = "";
*/

	$sql = "SELECT ve_name, pr_id, pro_id, pr_name, pro_stock_code, pr_offline, pro_stock_available FROM shopsystem_products, shopsystem_product_extended_options, vendor
		WHERE ((pr_deleted IS NULL) OR (pr_deleted = 0))
			$offlineSQL
			AND pr_id = pro_pr_id
			AND pr_ve_id = ve_id
			AND pr_ca_id = ".safe($this->ATTRIBUTES['ca_id'])."
			$sales_zone
		ORDER BY pr_ve_id, pr_name, pro_id";	

	ss_log_message( "Product selection sql : $sql" );

	$Q_Products = query( $sql );

?>
