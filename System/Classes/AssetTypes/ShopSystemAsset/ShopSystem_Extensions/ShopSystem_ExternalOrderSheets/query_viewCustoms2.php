<?php

	$this->param('vendor');
	$vendor = (int)$this->ATTRIBUTES['vendor'];
	$vendorRow = query( "select * from vendor where ve_id = $vendor" );

	$customsReport = array();
	$unallocatedShipments = array();

	$sql = "select unshipped_count.*, pr_name from unshipped_count join shopsystem_products on pr_id = cu_pr_id where cu_stock_available > 0 order by pr_name";
	ss_log_message( $sql );
	$q1 = query( $sql );

	while( $ucr = $q1->fetchRow() )
	{
		$customsReport[$ucr['cu_pr_id']] = array();
		$customsReport[$ucr['cu_pr_id']]['Unshipped'] = $ucr['cu_stock_available'];
		$customsReport[$ucr['cu_pr_id']]['SupplierItems'] = array();
		$sql = "select supplier_invoice.*, supplier_invoice_line.*, pr.pr_location, pr.pr_name, pr.pr0_883_f, pre.pro_net_weight, pre.pro_weight, countries.cn_name
					from supplier_invoice
					join supplier_invoice_line on sin_id = sil_sin_id
					join shopsystem_products pr on pr.pr_id = sil_pr_id
					join shopsystem_product_extended_options pre on pr_id = pro_pr_id
					left join countries on sin_from_cn_id = cn_id
					where sil_pr_id = {$ucr['cu_pr_id']} and sil_qty_put_in_stock - sil_shipped_count > 0";
		ss_log_message( $sql );
		$q2 = query( $sql );
		while( $silr = $q2->fetchRow() )
		{
			$customsReport[$ucr['cu_pr_id']]['SupplierItems'][$silr['sil_id']] = array();
			$customsReport[$ucr['cu_pr_id']]['SupplierItems'][$silr['sil_id']]['Details'] = $silr;
			$customsReport[$ucr['cu_pr_id']]['SupplierItems'][$silr['sil_id']]['Shipments'] = array();
		}
	}

	$distributors = array();
	$distCountries = array();
	if( $dq = query( 'select * from users where us_vendor_distributor = '.$vendor ) )
	{
		while( $distributor = $dq->fetchRow( ) )
		{
			$distributors[$distributor['us_id']] = array();
			$distributors[$distributor['us_id']]['countries'] = array();
			$distributors[$distributor['us_id']]['OrderRows'] = array();
			$distributors[$distributor['us_id']]['OrderServices'] = array();
			$distributors[$distributor['us_id']]['User'] = $distributor;
			$distributors[$distributor['us_id']]['Country'] = getRow( "select * from countries where cn_id = ".((int)$distributor['us_0_50A4']) );
			if( $dcn = query( "select cn_id from countries where cn_ship_via_distributor = {$distributor['us_id']}" ) )
				while( $dcnrw = $dcn->fetchRow( $dcn ) )
				{
					$distributors[$distributor['us_id']]['countries'][] = $dcnrw['cn_id'];
					$distCountries[$dcnrw['cn_id']] = $distributor['us_id'];
				}
		}

		ss_log_message( "There is are distributors for this vendor, struct follow" );
		ss_log_message_r(  'Log:'.__FILE__.':'.__LINE__, $distributors );
		ss_log_message_r(  'Log:'.__FILE__.':'.__LINE__, $distCountries );
	}

	$sql = "select shopsystem_order_sheets_items.*, shopsystem_order_sheets.*, countries.cn_name as DestCountry, or_country from shopsystem_order_sheets_items
					  join shopsystem_order_sheets on ors_id = orsi_ors_id
					  join shopsystem_orders on orsi_or_id = or_id
					  join countries on or_country = cn_id
					  where orsi_sil_id in (select distinct sil_id from supplier_invoice_line where sil_qty_put_in_stock - sil_shipped_count > 0)";

	ss_log_message( $sql );
	$q3 = query( $sql );
	while( $shipr = $q3->fetchRow() )
	{
		// figure out where this goes
		ss_log_message( "looking for sil_id:{$shipr['orsi_sil_id']}" );

		if( array_key_exists( $shipr['or_country'], $distCountries ) )	// this is sent via a distributor
		{
			ss_log_message( "Swapping out cn_id:".$shipr['or_country']." for " );
			$d = $distributors[	$distCountries[$shipr['or_country']] ];

			if( is_array( $d ) && array_key_exists( 'cn_name', $d['Country'] ) )
				 $shipr['DestCountry'] .= ' via '.$d['Country']['cn_name'];

			ss_log_message( $shipr['DestCountry'] );
		}

		if( $shipr['orsi_sil_id'] > 0 )
		{
			$found = false;
			foreach( $customsReport as $pr_id => $ProductLevel )
			{
				if( array_key_exists( $shipr['orsi_sil_id'], $ProductLevel['SupplierItems'] ) )
				{
					// found it
					ss_log_message( "Found it:$pr_id" );
					// hijack empty orsi_qty
					$shipr['orsi_qty'] = 1;
					$foundShip = false;
					foreach( $customsReport[$pr_id]['SupplierItems'][$shipr['orsi_sil_id']]['Shipments'] as $ix => $shipment )
						if( ( $shipment['orsi_stock_code'] == $shipr['orsi_stock_code'] )
						 && ( $shipment['ors_id'] == $shipr['ors_id'] )
						 && ( $shipment['DestCountry'] == $shipr['DestCountry'] ) )
						{
							$customsReport[$pr_id]['SupplierItems'][$shipr['orsi_sil_id']]['Shipments'][$ix]['orsi_qty']++;
							$foundShip = true;
						}

					if( !$foundShip )
						$customsReport[$pr_id]['SupplierItems'][$shipr['orsi_sil_id']]['Shipments'][] = $shipr;

					$found = true;
				}
			}
			if( !$found )
			{
				// load that into customsReport and add it to that element
				$sql = "select supplier_invoice.*, supplier_invoice_line.*, pr.pr_location, pr.pr_name, pr.pr0_883_f, pre.pro_net_weight, pre.pro_weight, countries.cn_name
					from supplier_invoice
					join supplier_invoice_line on sin_id = sil_sin_id
					join shopsystem_products pr on pr.pr_id = sil_pr_id
					join shopsystem_product_extended_options pre on pr_id = pro_pr_id
					left join countries on sin_from_cn_id = cn_id
					where sil_id = {$shipr['orsi_sil_id']}";
				ss_log_message( "Not found: searching : $sql" );
				$newRow = getRow( $sql );
				if( !array_key_exists( $newRow['sil_pr_id'], $customsReport ) )
				{
					ss_log_message( "New Product ID :{$newRow['sil_pr_id']}" );
					$customsReport[$newRow['sil_pr_id']] = array();
					$customsReport[$newRow['sil_pr_id']]['Unshipped'] = 0;
					$customsReport[$newRow['sil_pr_id']]['SupplierItems'] = array();
				}
				ss_log_message( "Adding to pr_id:{$newRow['sil_pr_id']}" );
				$customsReport[$newRow['sil_pr_id']]['SupplierItems'][$newRow['sil_id']] = array();
				$customsReport[$newRow['sil_pr_id']]['SupplierItems'][$newRow['sil_id']]['Details'] = $newRow;
				$customsReport[$newRow['sil_pr_id']]['SupplierItems'][$newRow['sil_id']]['Shipments'] = array();
				$customsReport[$newRow['sil_pr_id']]['SupplierItems'][$newRow['sil_id']]['Shipments'][] = $shipr;
				// hijack empty orsi_qty
				$shipr['orsi_qty'] = 1;
				$foundShip = false;
				foreach( $customsReport[$newRow['sil_pr_id']]['SupplierItems'][$newRow['sil_id']]['Shipments'] as $ix => $shipment )
					if( ( $shipment['orsi_stock_code'] == $shipr['orsi_stock_code'] )
					 && ( $shipment['ors_id'] == $shipr['ors_id'] )
					 && ( $shipment['DestCountry'] == $shipr['DestCountry'] ) )
					{
						$customsReport[$newRow['sil_pr_id']]['SupplierItems'][$newRow['sil_id']]['Shipments'][$ix]['orsi_qty']++;
						$foundShip = true;
					}

				if( !$foundShip )
					$customsReport[$newRow['sil_pr_id']]['SupplierItems'][$newRow['sil_id']]['Shipments'][] = $shipr;
			}
		}
		else
		{
			// hijack empty orsi_qty
			$shipr['orsi_qty'] = 1;
			$foundShip = false;
			foreach( $unallocatedShipments as $ix => $shipment )
				if( ( $shipment['orsi_stock_code'] == $shipr['orsi_stock_code'] )
					 && ( $shipment['ors_id'] == $shipr['ors_id'] )
				 && ( $shipment['DestCountry'] == $shipr['DestCountry'] ) )
				{
				 	$unallocatedShipments[$ix]['orsi_qty']++;
					$foundShip = true;
				}

			if( !$foundShip )
				$unallocatedShipments[] = $shipr;
		}
	}

	return;

//	ss_log_message( "Customs report for sheet {$this->ATTRIBUTES['ors_id']}" );
//	ss_log_message_r(  'Log:'.__FILE__.':'.__LINE__, $customsReport );
	foreach( $customsReport as $pr_id => $ProductLevel )
	{
		ss_log_message( "Product ID:$pr_id Unshipped Total is {$ProductLevel['Unshipped']}" );
		foreach( $ProductLevel['SupplierItems'] as $InvoiceLine => $InvoiceDetails )
		{
			ss_log_message( "Supplier Invoice Line : $InvoiceLine for Product ID:$pr_id, customs reference {$InvoiceDetails['Details']['sin_customs_reference']}" );
			foreach( $InvoiceDetails['Shipments'] as $Shipment )
			{
				ss_log_message_r(  'Log:'.__FILE__.':'.__LINE__, $Shipment );
			}
		}
	}

	ss_log_message_r(  'Log:'.__FILE__.':'.__LINE__, $unallocatedShipments );


/*
	$Q_OrderSheet = query("SELECT shopsystem_order_sheets.*, count(orsi_box_number) as Boxes, sum( pr0_883_f ) as Llamas,
		sum(orsi_total/orsi_usd_rate) as USDSalesPrice, sum(orsi_cost_price/orsi_usd_rate) as USDCostPrice FROM shopsystem_order_sheets
		  join shopsystem_order_sheets_items on orsi_ors_id = ors_id
		  join shopsystem_product_extended_options on orsi_stock_code = pro_stock_code
		  join shopsystem_products on pr_id = pro_pr_id
		WHERE ors_id = ".safe($this->ATTRIBUTES['ors_id'])."
		  and orsi_date_shipped IS NOT NULL
	");
*/

/*
	$Q_OrderSheetItems = query("
		SELECT * FROM shopsystem_order_sheets_items, shopsystem_orders
		WHERE orsi_ors_id = ".safe($this->ATTRIBUTES['ors_id'])."
		  AND orsi_or_id = or_id
		ORDER BY orsi_id
	");

	table supplier_invoice (
sin_id integer           NOT NULL auto_increment,
sin_sp_id                integer not null,
sin_invoice_number       varchar(64),
sin_invoice_date         date not null,
sin_instock_date         date,
sin_entered_date         timestamp not null default now(),
sin_entered_currency     varchar(3) not null default 'EUR',
sin_paid_currency        varchar(3) not null default 'EUR',
sin_paid_amount          decimal(10,2) not null default 0.0,
sin_discount             decimal(10,2) not null default 0.0,
sin_fixed_costs          decimal(10,2) not null default 0.0,
sin_dest_ve_id           integer,
sin_from_cn_id           integer,
sin_customs_reference    varchar(255),
sin_forwarder_name       varchar(255),

table supplier_invoice_line (
sil_id integer   NOT NULL auto_increment,
sil_sin_id    integer not null,
sil_box_code  varchar(64),
sil_supplier_sku   varchar(64),
sil_description   varchar(256),
sil_pr_id   integer not null default 0,
sil_qty     integer not null default 0,
sil_qty_received     integer not null default 0,
sil_qty_put_in_stock     integer not null default 0,
sil_raw_line_cost   decimal(10,2),
sil_computed_cost   decimal(10,2),
sil_shipped_count   integer not null default 0,

*/

	$q = "create temporary table customsreport as select orsi_sil_id, pr_id, pr0_883_f, pro_stock_code, pro_stock_available, pro_stock_unavailable, src.cn_name as SrcCnCountry, dst.cn_two_code as DstCnTwoCode, dst.cn_name as DstCnCountry, orsi_stock_code, orsi_pr_name, pr_location, pr_name, sp_name, sin_id, sin_customs_reference, sin_forwarder_name, sin_instock_date, sin_entered_currency, pt_name, orsi_date_shipped, 0 as QtyPutInStock, 0000000.00 as CostPrice, 0000000.00 as NetWeight, 0000000.00 as ShipWeight, count(orsi_box_number) as Boxes, sum(orsi_total/orsi_usd_rate) as USDSalesPrice, sum(orsi_cost_price/orsi_usd_rate) as USDCostPrice
		FROM shopsystem_order_sheets_items 
			join shopsystem_orders on orsi_or_id = or_id
			left join shopsystem_product_extended_options on pro_stock_code = orsi_stock_code
			left join shopsystem_products on pr_id = pro_pr_id
			left join product_type on pr_type = pt_id
			left join countries as dst on dst.cn_id = or_country
			left join users on or_us_id = us_id
			left join supplier_invoice_line on orsi_sil_id = sil_id
			left join supplier_invoice on sil_sin_id = sin_id
			left join countries as src on sin_from_cn_id = src.cn_id
			left join supplier on sin_sp_id = sp_id
		WHERE orsi_ors_id = ".safe($this->ATTRIBUTES['ors_id'])."
		  and or_cancelled IS NULL
		  and or_standby IS NULL
		  and orsi_date_shipped IS NOT NULL
		GROUP BY pr_id, src.cn_name, dst.cn_name, orsi_stock_code, orsi_pr_name, pr_location, pr_name, sp_name, sin_customs_reference, sin_forwarder_name, sin_instock_date, sin_entered_currency, pt_name
		ORDER BY pr_id, src.cn_name, dst.cn_name";

	ss_log_message( $q );
	query( $q );

	$q = 'update customsreport, supplier_invoice_line, shopsystem_product_extended_options, shopsystem_products
	set QtyPutInStock = sil_qty_put_in_stock,
		CostPrice = sil_computed_cost,
		NetWeight = pro_net_weight*Boxes,
		ShipWeight = pro_weight*Boxes
where customsreport.pro_stock_code = shopsystem_product_extended_options.pro_stock_code
  and pro_pr_id = customsreport.pr_id
  and orsi_sil_id = sil_id';

	ss_log_message( $q );
	query( $q );


	// loop through looking subs in the combos


	query( "create temporary table in_system as select oi_stock_code, count(*) as QtyPaidUnsent from shopsystem_order_items where oi_eos_id IS NULL group by oi_stock_code" );

	query( "create temporary table on_hold as select or_id from shopsystem_orders join transactions on tr_id = or_tr_id where or_archive_year IS NULL and or_standby IS NOT NULL and tr_completed >= 1" );
	// remove those that have been through the packing system
	query( "delete from on_hold where or_id in (select oi_or_id from shopsystem_order_items)" );

	query( "create temporary table on_standby as select op_stock_code as StockCode, sum(op_quantity) as QtyReservedOnStandby from ordered_products join on_hold on op_or_id = or_id group by op_stock_code" );

/*
	query( "create temporary table q_products as select * from shopsystem_products join shopsystem_product_extended_options on pr_id = pro_pr_id join vendor on pr_ve_id = ve_id left join in_system on oi_stock_code = pro_stock_code left join on_standby on pro_stock_code = StockCode where pr_ve_id = $vendor and pr_is_service = 'false' and pr_deleted IS NULL and pro_deleted is NULL and pr_combo IS NULL order by pr_ve_id, pro_stock_code" );
	$Q_products = query( "select *, pro_stock_available + pro_stock_unavailable + IF(QtyReservedOnStandby IS NOT NULL,QtyReservedOnStandby,0) + IF(QtyPaidUnsent IS NOT NULL,QtyPaidUnsent,0) as Shelf from q_products" );
*/

	$Q_OrderSheetItems = query("select 
			orsi_stock_code, SrcCnCountry, DstCnTwoCode, DstCnCountry, sp_name, pr_location, pr_name, pr0_883_f, orsi_pr_name, QtyPutInStock, 
			CostPrice, sin_id, sin_customs_reference, sin_forwarder_name, sin_instock_date, sin_entered_currency, pt_name, 
			orsi_date_shipped, 
			sum(NetWeight) as NetWeight, sum(ShipWeight) as ShipWeight, sum(Boxes) as Boxes, sum(USDSalesPrice) as USDSalesPrice, 
			sum(USDCostPrice) as USDCostPrice
		from customsreport
		group by orsi_stock_code, orsi_pr_name, pr_location, sin_customs_reference, sin_forwarder_name, sin_instock_date, sin_entered_currency, SrcCnCountry, DstCnTwoCode, DstCnCountry, sp_name
		order by orsi_pr_name, SrcCnCountry, DstCnCountry");

	$q = "create temporary table customsreport2 as select pr_id, pro_stock_code, pr_name, pro_source_currency, pro_supplier_price, cn_name as SrcCnCountry, pr_location, sp_name, sin_customs_reference, sin_forwarder_name, sin_instock_date, pt_name, pro_net_weight, pro_weight, sil_qty_put_in_stock - sil_shipped_count as StockAvailable, sil_qty_received - sil_qty_put_in_stock as StockUnavailable
		FROM supplier_invoice
			join supplier_invoice_line on sin_id = sil_sin_id
			join shopsystem_products on pr_id = sil_pr_id
			join shopsystem_product_extended_options on pro_pr_id = sil_pr_id
			left join product_type on pr_type = pt_id
			left join countries on sin_from_cn_id = cn_id
			left join supplier on sin_sp_id = sp_id
		WHERE pr_ve_id = $vendor";

	ss_log_message( $q );
	query( $q );

	query("create temporary table StockReport as select pr_id, pro_stock_code, pr_name, pr_location, pro_stock_available, pro_stock_unavailable, QtyPaidUnsent, QtyReservedOnStandby, 
							pro_stock_available + IF(QtyPaidUnsent IS NULL, 0, QtyPaidUnsent) + IF(QtyReservedOnStandby IS NULL, 0, QtyReservedOnStandby) as ShouldBeOnShelf,
							cu_stock_available as InvoiceUnshippedTotal,
							0 as ShippedHere
						from shopsystem_products join shopsystem_product_extended_options on pro_pr_id = pr_id
							left join unshipped_count on pr_id = cu_pr_id
							left join in_system on oi_stock_code = pro_stock_code
							left join on_standby on pro_stock_code = StockCode
						where pr_ve_id = $vendor and pr_combo IS NULL and pr_is_service = 'false'");

	query( "update StockReport set ShippedHere = (select count(*) from shopsystem_order_sheets_items where orsi_stock_code = pro_stock_code and orsi_ors_id = ".safe($this->ATTRIBUTES['ors_id'])." and orsi_date_shipped IS NOT NULL)" );

	query( "delete from StockReport where ShippedHere = 0");

	query( "delete from StockReport where 
			(pro_stock_unavailable = 0  OR pro_stock_unavailable IS NULL)
			and (pro_stock_available = 0 OR pro_stock_available IS NULL)
			and (QtyPaidUnsent = 0 OR QtyPaidUnsent IS NULL)
			and (QtyReservedOnStandby = 0 OR QtyReservedOnStandby IS NULL)
			and (InvoiceUnshippedTotal = 0 OR InvoiceUnshippedTotal IS NULL)" );

	$Q_Customs2 = query( "select * from StockReport" );
?>
