<?php

requireOnceClass('Administration');
class ShopSystem_ProductsAdministration extends Administration {

    function exposeServices() {
        return array_merge(
            Administration::exposeServicesUsing('ShopSystem_Products'),
            array(
                'ShopSystem_ProductsAdministration.UpdateStockAvailability'    =>    array('method'=>'updateStockAvailability'),
                'ShopSystem_ProductsAdministration.setAddGift'    =>    array('method'=>'setAddGift'),
                'ShopSystem_ProductsAdministration.noDiscount'    =>    array('method'=>'noDiscount'),
                'ShopSystem_ProductsAdministration.setLP'    =>    array('method'=>'setLP'),
                'ShopSystem_ProductsAdministration.setSwiss'    =>    array('method'=>'setSwiss'),
                'ShopSystem_ProductsAdministration.Activate'    =>    array('method'=>'activate'),
                'ShopSystem_ProductsAdministration.Deactivate'    =>    array('method'=>'deactivate'),
                'ShopSystem_ProductsAdministration.EOQ'    =>    array('method'=>'EOQ'),
                'ShopSystem_ProductsAdministration.StockCheck'    =>    array('method'=>'StockCheck'),
                'ShopSystem_ProductsAdministration.ShowHistory'    =>    array('method'=>'showHistory'),
                'ShopSystem_ProductsAdministration.Duplicate'    =>    array('method'=>'duplicate'),
            )
        );
    }

	function check_language_descriptions( )
	{

		if( array_key_exists( 'pr_id', $this->ATTRIBUTES )
		  && array_key_exists( 'pr_name', $this->ATTRIBUTES )
		  && array_key_exists( 'pr_short', $this->ATTRIBUTES )
		  && array_key_exists( 'pr_long', $this->ATTRIBUTES )
		  && array_key_exists( 'pr_keywords', $this->ATTRIBUTES )
		  && array_key_exists( 'pr_window_title', $this->ATTRIBUTES ) )
		{
			$pr_id = $this->ATTRIBUTES['pr_id'];
			$name = escape($this->ATTRIBUTES['pr_name']);
			$shortDesc = escape($this->ATTRIBUTES['pr_short']);
			$longDesc = escape($this->ATTRIBUTES['pr_long']);
			$keywords = escape($this->ATTRIBUTES['pr_keywords']);
			$title = escape($this->ATTRIBUTES['pr_window_title']);

			$Q_lang = query( "select * from languages where lg_id > 0" );
			
			while( $lr = $Q_lang->fetchRow( ) )
			{
				$there = getRow( "select count(*) as count from shopsystem_product_descriptions where prd_pr_id = $pr_id and prd_language = {$lr['lg_id']}" );
				if( $there['count'] == 0 )
				{
					// insert this row
					$prd_id = newPrimaryKey( "shopsystem_product_descriptions", "prd_id" );
					query( "insert into shopsystem_product_descriptions (prd_id,prd_pr_id,prd_language,prd_pr_name,prd_short,prd_long,prd_keywords,prd_window_title) values ("
					   ." $prd_id, $pr_id, {$lr['lg_id']}, '$name', '$shortDesc', '$longDesc', '$keywords', '$title' )" );
				}
			}
		}
	}

	function duplicate()
	{
		$pr_id = $this->ATTRIBUTES['pr_id'];

		$tables = array( 'pr_id' => 'shopsystem_products', 'pro_pr_id' => 'shopsystem_product_extended_options', 'cpr_element_pr_id' => 'shopsystem_combo_products' );
		$ignore = array( 'shopsystem_product_extended_options' => 'pro_id', 'shopsystem_combo_products' => 'cpr_id' );

		$npr_id = getField( "select max(pr_id) from shopsystem_products" );
		$npr_id++;

		foreach ( $tables as $index=>$table )
		{
			$Q = Query( "select * from $table where $index = $pr_id" );
			while( $r = $Q->fetchRow() )
			{
				$r[$index] = $npr_id;

				if( $table == 'shopsystem_product_extended_options' )
					$r['pro_stock_code'] = 'Dup'.$r['pro_stock_code'];

				$q = "insert into $table set ";
				$first = true;
				foreach( $r as $i=>$v )
				{
					if( $ignore[$table] != $i )
					{
						if( !$first )
							$q .= ',';
						if( $v === NULL )
							$q .= "$i = NULL";
						else
							$q .= "$i = '".addslashes( $v )."'";
						$first = false;
					}
				}
				ss_log_message( $q );
				query( $q );
			}
		}
	}

	function StockCheck() {
		if( $this->ATTRIBUTES['Fix'] )
			$fix = true;
		else
			$fix = false;

		query( "create temporary table in_system as select oi_stock_code, count(*) as QtyPaidUnsent from shopsystem_order_items where oi_eos_id IS NULL group by oi_stock_code" );
		query( "create temporary table on_hold as select or_id from shopsystem_orders where or_archive_year IS NULL and or_standby IS NOT NULL and or_paid_not_shipped IS NOT NULL" );
		query( "delete from on_hold where or_id in (select oi_or_id from shopsystem_order_items)" );
		query( "create temporary table on_standby as select op_stock_code as StockCode, sum(op_quantity) as QtyReservedOnStandby from ordered_products join on_hold on op_or_id = or_id group by op_stock_code" );

		if( $fix )
		{
			query( "update shopsystem_product_extended_options, shopsystem_products set pro_stock_available = 0
						where pro_pr_id = pr_id AND pr_ve_id = 2" );
			query( "update shopsystem_product_extended_options, unshipped_count set pro_stock_available = cu_stock_available
						where pro_pr_id = cu_pr_id" );
			query( "update shopsystem_product_extended_options, in_system set pro_stock_available = pro_stock_available - IF(QtyPaidUnsent IS NULL, 0, QtyPaidUnsent)
						where oi_stock_code = pro_stock_code" );
			query( "update shopsystem_product_extended_options, on_standby set pro_stock_available = pro_stock_available - IF(QtyReservedOnStandby IS NULL, 0, QtyReservedOnStandby)
						where pro_stock_code = StockCode" );
		}
		else
		{
			echo "<html><h4>Stock Level Errors Check List</h4><br />";
			echo "<br />";
			echo "<br />";

			echo "<table border=1>";
			echo "<tr><th>Product ID</th><th>Stock Code</th><th>Name</th><th>Stock Unavailable</th><th>Stock Available</th><th>Paid For And Unsent</th><th>On Standby</th><th>On Shelf</th><th>Unshipped on Supplier Invoices</th></tr>";
			$Q = query( "select pr_id, pro_stock_code, pr_name, pro_stock_available, pro_stock_unavailable, QtyPaidUnsent, QtyReservedOnStandby, pro_stock_available + IF(QtyPaidUnsent IS NULL, 0, QtyPaidUnsent) + IF(QtyReservedOnStandby IS NULL, 0, QtyReservedOnStandby) as ShouldBeOnShelf, cu_stock_available as InvoiceUnshippedTotal
							from shopsystem_products join shopsystem_product_extended_options on pro_pr_id = pr_id
								left join unshipped_count on pr_id = cu_pr_id
								left join in_system on oi_stock_code = pro_stock_code
								left join on_standby on pro_stock_code = StockCode
							where pr_ve_id = 2 and (cu_stock_available IS NOT NULL OR pro_stock_available > 0 ) and pr_combo IS NULL
							order by pr_name
							" );
							//where pr_ve_id = 2 and pro_stock_available + IF(QtyPaidUnsent IS NULL, 0, QtyPaidUnsent) + IF(QtyReservedOnStandby IS NULL, 0, QtyReservedOnStandby) != cu_stock_available" );
			while ($row = $Q->fetchRow()) {
				echo "<tr>";
				echo "<td>".$row['pr_id']."</td>";
				echo "<td>".$row['pro_stock_code']."</td>";
				echo "<td>".$row['pr_name']."</td>";
				echo "<td>".$row['pro_stock_unavailable']."</td>";
				echo "<td>".$row['pro_stock_available']."</td>";
				echo "<td>".$row['QtyPaidUnsent']."</td>";
				echo "<td>".$row['QtyReservedOnStandby']."</td>";
				echo "<td>".$row['ShouldBeOnShelf']."</td>";
				echo "<td>".$row['InvoiceUnshippedTotal']."</td>";
				echo "</tr>";
			}
			echo "</table></html>";
		}

	}

	function showHistory() {
		$pr_id = $this->ATTRIBUTES['pr_id'];
		echo "<br />";
		echo "<br />";

		echo "<h4>Price History</h4><br />";
		echo "<A href='".$this->ATTRIBUTES['BackURL']."'> BACK </A>";
		echo "<br />";
		echo "<br />";

		echo "<table border=1>";
		echo "<tr><th>When</th><th>Stock Available</th><th>Stock Unavailable</th><th>Normal</th><th>Special</th><th>Supplier</th></tr>";
		$Q = query( "select * from prexop_history where pro_pr_id = $pr_id order by pro_recorded" );
		while ($row = $Q->fetchRow()) {
			echo "<tr>";
			echo "<td>".substr( $row['pro_recorded'], 0, 10 )."</td>";
			echo "<td>".$row['pro_stock_available']."</td>";
			echo "<td>".$row['pro_stock_unavailable']."</td>";
			echo "<td>".$row['pro_price']."</td>";
			echo "<td>".$row['pro_special_price']."</td>";
			echo "<td>".$row['pro_supplier_price']."</td>";
			echo "</tr>";
		}
		echo "</table><br />";

		echo "<br />";
		echo "<br />";
		echo "<br />";
		echo "<br />";
		echo "<br />";
		echo "<br />";

		echo "<table border=1>";
		echo "<tr><th>When</th><th>Email</th><th>Name</th><th>Price Field</th><th>Price</th><th>Reason</th></tr>";
		$Q = query( "select * from price_changes join users on pc_us_id = us_id where pc_pr_id = $pr_id order by pc_id" );
		while ($row = $Q->fetchRow()) {
			echo "<tr>";
			echo "<td>".$row['pc_timestamp']."</td>";
			echo "<td>".$row['us_email']."</td>";
			echo "<td>".$row['us_first_name']."</td>";
			echo "<td>".$row['pc_field_name']."</td>";
			echo "<td>".$row['pc_amount']."</td>";
			echo "<td>".$row['pc_notes']."</td>";
			echo "</tr>";
		}
		echo "</table><br />";

		echo "<br />";
		echo "<br />";
		echo "<br />";
		echo "<br />";
		echo "<br />";
		echo "<br />";

		echo "<h4>Stock History</h4><br />";
		echo "<table border=1>";
		echo "<tr><th>When</th><th>Email</th><th>User</th><th>Operation</th><th>Reason</th></tr>";
		$Q = query( "select * from audit left join users on au_userid = us_id where au_key = $pr_id and au_table = 'Products' order by au_id" );
		while ($row = $Q->fetchRow()) {
			echo "<tr>";
			echo "<td>".$row['au_timestamp']."</td>";
			echo "<td>".$row['us_email']."</td>";
			echo "<td>".$row['us_first_name'].' '.$row['us_last_name']."</td>";
			echo "<td>".$row['au_operation']."</td>";
			echo "<td>".$row['au_notes']."</td>";
			echo "</tr>";
		}
		echo "</table><br />";

		echo "<A href='".$this->ATTRIBUTES['BackURL']."'> BACK </A>";
	}

    function updateStockAvailability() {
        require('model_updateStockAvailability.php');    
    }

    function eoq() {
        require('model_eoq.php');    
    }

    function update() {

	    if( ((int) $this->primaryKey) > 0 )
	    {
		    // TODO - DONE, this is causing issues in reshipments with the service ID changing
		    // query( "delete from product_service_options where sv_pr_id = ".((int)$this->primaryKey) );
		    $toAdd = array();
		    $toDelete = array();
		    $Q = query( "select * from product_service_options where sv_pr_id = ".((int)$this->primaryKey) );
		    while ($row = $Q->fetchRow())
			    $toDelete[] = $row['sv_pr_id_service'];

//			ss_log_message("retrieved service IDs for ".(int)$this->primaryKey);
//			ss_log_message_r(  'Log:'.__FILE__.':'.__LINE__, $toDelete );

		    foreach( $_POST as $k=>$v )
			    if( ( substr( $k, 0, 7) == 'service' ) && $v )
			    {
				    $sp = escape(substr( $k, 7 ));
//					ss_log_message( "want service ID ".$sp );
				    if( ( $key = array_search( $sp, $toDelete ) ) !== FALSE )		// still there?
					    unset( $toDelete[$key] );		// don't delete it.
				    else
					    $toAdd[] = $sp;				// not there yet
			    }

//			ss_log_message("deleting service IDs for ".(int)$this->primaryKey);
//			ss_log_message_r(  'Log:'.__FILE__.':'.__LINE__, $toDelete );
//			ss_log_message("adding service IDs for ".(int)$this->primaryKey);
//			ss_log_message_r(  'Log:'.__FILE__.':'.__LINE__, $toAdd );

		    foreach( $toAdd as $addme )
				if( strlen( $addme ) )
					query( "insert into product_service_options (sv_pr_id, sv_pr_id_service ) values (".((int)$this->primaryKey).", ".$addme.")" );
		    foreach( $toDelete as $delme )
				if( strlen( $delme ) )
					query( "delete from product_service_options where sv_pr_id = ".((int)$this->primaryKey)." and sv_pr_id_service = ".$delme );

	    }

	    $this->check_language_descriptions( );
	    parent::update();
    }

    function query($params = array()) {        
//        $params['FilterSQL'] = ' AND pro_is_main = 1 AND pro_pr_id = pr_id';

        ss_paramKey($params,'FilterSQL','');
		$params['FilterSQL'] .= ' AND pro_pr_id = pr_id';
		$params['FilterTablesSQL'] = 'shopsystem_product_extended_options';

		ss_log_message_r(  'Log:'.__FILE__.':'.__LINE__, $this );
        $query = parent::query($params);
        
        if (ss_optionExists('Shop Acme Rockets'))
		{
            if (is_object($query)) 
			{
                $query->addColumn('BackStampCode');
                $counter = 0;
                while ($row = $query->fetchRow()) {
                    
					$backStampCode = getRow("
						SELECT orsi_date_changed, orsi_bs_code
						FROM shopsystem_order_sheets_items, shopsystem_products, 
							shopsystem_product_extended_options
						WHERE pr_id = pro_pr_id
							AND pr_id = {$row['pr_id']}
							AND pro_stock_code LIKE orsi_stock_code
							AND orsi_bs_code IS NOT NULL 
							AND orsi_date_changed IS NOT NULL
						ORDER BY orsi_date_changed DESC
						LIMIT 0,1                    
					");
					if ($backStampCode !== null) {
						$query->setCell('BackStampCode',$backStampCode['orsi_bs_code'].' - '.date('j M y',ss_SQLtoTimeStamp($backStampCode['orsi_date_changed'])),$counter);
					}
					$counter++;
                }
            }
        }
        
        return $query;
    }    
    
    function getCurrencyFromCereal($cereal,$type) {
        return include('System/Classes/AssetTypes/ShopSystemAsset/inc_getCurrencyFromCereal.php');
    }

	function activate()
	{
		$alterations = "Activating product IDs {$this->ATTRIBUTES['pr_id']}";
		$result = new Request('Email.Send',array(
							'to'	=>	'acme@admin.com', 
							'from'	=>	'webserver@acmerockets.com',
							'subject'	=>	"Product Activations",
							'html'	=>	$alterations,
						));

		$result = query( "update shopsystem_products set pr_offline = null where pr_id in ({$this->ATTRIBUTES['pr_id']})" );
		location($this->ATTRIBUTES['BackURL']);
	}

	function deactivate()
	{
		$alterations = "Deactivating product IDs {$this->ATTRIBUTES['pr_id']}";
		$result = new Request('Email.Send',array(
							'to'	=>	'acme@admin.com', 
							'from'	=>	'webserver@acmerockets.com',
							'subject'	=>	"Product Deactivations",
							'html'	=>	$alterations,
						));

		$result = query( "update shopsystem_products set pr_offline = 1 where pr_id in ({$this->ATTRIBUTES['pr_id']})" );
		location($this->ATTRIBUTES['BackURL']);
	}


    function delete() {
        // Delete the row
        
//        $result = query("
 //           DELETE FROM shopsystem_product_extended_options 
  //          WHERE pro_pr_id IN ({$this->ATTRIBUTES['pr_id']})
   //     ");
		$alterations = "Deleting product IDs {$this->ATTRIBUTES['pr_id']}";
		$result = new Request('Email.Send',array(
							'to'	=>	'acme@admin.com', 
							'from'	=>	'webserver@acmerockets.com',
							'subject'	=>	"Product Deletions",
							'html'	=>	$alterations,
						));

        $result = query("
			Update shopsystem_product_extended_options 
				set pro_deleted = 1
            WHERE pro_pr_id IN ({$this->ATTRIBUTES['pr_id']})
        ");
        parent::delete();
    }

    function setAddGift( ) {

        $result = query("
            UPDATE shopsystem_products set pr_add_gift = ".$this->ATTRIBUTES['pr_add_gift']
        );
    }

    function noDiscount( ) {

		$alterations = "removing all llamas from discount group";
		$result = new Request('Email.Send',array(
							'to'	=>	'acme@admin.com', 
							'from'	=>	'webserver@acmerockets.com',
							'subject'	=>	"Product Activations",
							'html'	=>	$alterations,
						));

        $result = query("UPDATE shopsystem_products set pr_dig_id = NULL");
    }

    function setLP( ) {

		$alterations = "Activating All Las Palmas product IDs";
		$result = new Request('Email.Send',array(
							'to'	=>	'acme@admin.com', 
							'from'	=>	'webserver@acmerockets.com',
							'subject'	=>	"Product Activations",
							'html'	=>	$alterations,
						));

		// update swiss products to be offline
        $result = query("UPDATE shopsystem_products set pr_offline = 1 where pr_ve_id = 2");

		// update LP products to be not offline
        $result = query("UPDATE shopsystem_products set pr_offline = null where pr_ve_id is null or pr_ve_id = 0");
    }

    function setSwiss( ) {

		$alterations = "Activating All Swiss product IDs";
		$result = new Request('Email.Send',array(
							'to'	=>	'acme@admin.com', 
							'from'	=>	'webserver@acmerockets.com',
							'subject'	=>	"Product Activations",
							'html'	=>	$alterations,
						));
       
		// update swiss products to be offline
        $result = query("UPDATE shopsystem_products set pr_offline = null where pr_ve_id = 2");

		// update LP products to be not offline
        $result = query("UPDATE shopsystem_products set pr_offline = 1 where pr_ve_id is null or pr_ve_id = 0");
     
    }

    function entries() {    
        
        if (array_key_exists($this->tablePrimaryKey,$this->ATTRIBUTES))
			$this->primaryKey = $this->ATTRIBUTES[$this->tablePrimaryKey];

        require('EntriesQuery.php');        
        require('EntriesDisplay.php');    
    }    
    
	function __construct($assetID = null,$pr_id = null) {
        if ($assetID === null || is_array($assetID)) {
            if (!strlen($this->assetLink)) {
                if (array_key_exists("as_id", $_REQUEST)) {
                    $assetID = $_REQUEST['as_id'];            
                }            
            }
        }        

        if ($assetID === null || is_array($assetID))
		{
			ss_log_message( "Error: ProductAdmin assetID is null" );
			ss_log_message_r(  'Log:'.__FILE__.':'.__LINE__, $_REQUEST );
            return;
		}

		$tableDisplayFields = array('pr_id','pr_name','pro_stock_code','pro_source_currency', 'pro_price','pro_special_price');
        $tableDisplayFieldTitles = array('Product ID', 'Name','Stock Code', 'Currency', 'Price','Special Price');
		/*
        if (ss_optionExists('Shop Members')) {
            $tableDisplayFieldTitles = array('Product ID','Name','Stock Code','Price','Special Price','Member Price','Wholesale Price');
        }
		*/
        
        if (ss_optionExists('Shop Acme Rockets')) {
            array_push($tableDisplayFields,'pro_supplier_price');    
            array_push($tableDisplayFieldTitles,'Supplier Price (Margin %)');    
            //array_push($tableDisplayFields,'Margin');    
            //array_push($tableDisplayFieldTitles,'Margin %');    
        }

        if (ss_optionExists('Restricted Shop Products')) {
            array_push($tableDisplayFields,'PrRestricted');
            array_push($tableDisplayFieldTitles,'');
        }

        if (ss_optionExists('Shop Gallery')) {
            array_push($tableDisplayFields,'PrGallery');
            array_push($tableDisplayFieldTitles,'');
        }

        if (ss_optionExists('Shop Featured Products')) {
            array_push($tableDisplayFields,'pr_featured');
            array_push($tableDisplayFieldTitles,'');
        }


		array_push($tableDisplayFields,'pr_is_service');    
		array_push($tableDisplayFieldTitles,'');    

        if (ss_optionExists('Shop Acme Rockets') or ss_optionExists('Shop Products Offline')) {
            array_push($tableDisplayFields,'pr_offline');    
            array_push($tableDisplayFieldTitles,'');    
        }
        
        if (ss_optionExists('Shop Acme Rockets')) {
            array_push($tableDisplayFields,'pr_points');    
            array_push($tableDisplayFieldTitles,'');    
        }
        
        

        $allCategoriesResult = new Request("shopsystem_categories.QueryAll",array('as_id'    =>    $assetID, 'ForAdmin' => true));
        $Q_Categories = $allCategoriesResult->value;
        $categoriesOptionsHTML = '<option value="">Please select</option>';
        while ($row = $Q_Categories->fetchRow()) {
            $categoriesOptionsHTML .= '<option value="'.$row['ca_id'].'">'.ss_HTMLEditFormat($row['ca_name']).'</option>';
        }
        
        
        $foo = array(
            'prefix'                    =>    'ShopSystem_Products',
            'singular'                    =>    'Product',
            'plural'                    =>    'Products',
            'tableName'                    =>    'shopsystem_products',
            'tablePrimaryKey'            =>    'pr_id',
            'tableDisplayFields'        =>    $tableDisplayFields,
            'tableDisplayFieldTitles'    =>    $tableDisplayFieldTitles,
            'tableOrderBy'                =>    array('pr_sort_order, pr_id' => 'Default','pr_name' => 'Name','pro_stock_code' => 'Stock Code'),
            'tableAssetLink'            =>    'pr_as_id',
            'assetLink'                    =>    $assetID,
            'tableDeleteFlag'            =>    'pr_deleted',
            'tableSortOrderField'        =>    'pr_sort_order',
        );
		if( ss_adminCapability( ADMIN_PRODUCT_ENTRY ) )
			$foo[ 'hideNewButton' ] = 'Create new product in: <input type="hidden" name="act" value="ShopSystem_CategoryProductsAdministration.New"><select onchange="if (this.selectedIndex != 0) this.form.submit();" name="pr_ca_id">'.$categoriesOptionsHTML.'</select>';

        $this->tableSearchFields = $tableDisplayFields;
		array_push($this->tableSearchFields, 'pro_supplier_sku');

		parent::__construct( $foo );

		array_push($this->tableDisplayFields,'pr_combo');
		array_push($this->tableDisplayFieldTitles,'');
		$this->tableOrderBy['(IF(pro_special_price IS NULL, pro_price, pro_special_price) - pro_supplier_price )/IF(pro_special_price IS NULL, pro_price, pro_special_price)'] = 'Product Margin';

        if (ss_optionExists('Shop Product Out Of Stock') or ss_optionExists('Shop Product Stock Levels')) {
            $this->tableOrderBy['pro_stock_available'] = 'Stock Available';
            array_push($this->tableDisplayFields,'pro_stock_available');
            array_push($this->tableDisplayFieldTitles,'Stock Available');
            array_push($this->tableDisplayFields,'pr_location');
            array_push($this->tableDisplayFieldTitles,'Location');
        }
        
		$this->addChild(new ChildTable (array(
                'prefix'                    =>    'ShopSystem_ComboProducts',
                'plural'                    =>    'Combo Products',
                'singular'                    =>    'Combo Product',
                'tableName'                    =>    'shopsystem_combo_products',
                'tablePrimaryKey'            =>    'cpr_id',
                'linkField'                    =>    'cpr_element_pr_id',
                'tableAssetLink'            =>    'cpr_as_id',
            )));
        
/*        
        $this->setParent(new ParentTable(array(
            'tableName'                    =>    'shopsystem_categories',
            'tablePrimaryKey'            =>    'ca_id',
            'linkField'                    =>    'pr_ca_id',
        )));
*/

        $this->addField(new SelectField (array(
            'name'            =>    'pr_ca_id',
            'displayName'    =>    'Primary Category',
            'note'            =>    null,
            'required'        =>    true,
            'verify'        =>    false,
            'unique'        =>    false,
            'size'    =>    '30',    'maxLength'    =>    '255',
            'rows'    =>    '6',    'cols'        =>    '40',
            'linkQueryAction'    =>    'shopsystem_categories.QueryAll',
            'linkQueryValueField'    =>    'ca_id',
            'linkQueryDisplayField'    =>    'ca_name',
            'linkQueryParameters'    =>    array('as_id'=>$assetID, 'ForAdmin'=>true),
        )));

        $this->addField(new SelectField (array(
            'name'            =>    'pr_sub_ca_id',
            'displayName'    =>    'Secondary Category',
            'note'            =>    null,
            'required'        =>    false,
            'verify'        =>    false,
            'unique'        =>    false,
            'size'    =>    '30',    'maxLength'    =>    '255',
            'rows'    =>    '6',    'cols'        =>    '40',
            'linkQueryAction'    =>    'shopsystem_categories.QueryAll',
            'linkQueryValueField'    =>    'ca_id',
            'linkQueryDisplayField'    =>    'ca_name',
            'linkQueryParameters'    =>    array('as_id'=>$assetID, 'ForAdmin'=>true),
        )));

        $this->addField(new SelectField (array(
            'name'            =>    'pr_restrict_special_to_gateway',
            'displayName'    =>    'Special will apply only to this gateway',
            'note'            =>    null,
            'required'        =>    false,
            'verify'        =>    false,
            'unique'        =>    false,
            'size'    =>    '30',    'maxLength'    =>    '255',
            'rows'    =>    '6',    'cols'        =>    '40',
			'linkQuery'		=>	'select * from payment_gateways where pg_id in (select po_pg_id from payment_gateway_options where po_active = true)',
			'linkQueryValueField'	=>	'pg_id',
			'linkQueryDisplayField'	=>	array( 'pg_id', 'pg_name', 'pg_description' ),
        )));

        $this->addField(new SelectField (array(
            'name'            =>    'pr_restrict_product_to_gateway',
            'displayName'    =>    'Product only available using this gateway',
            'note'            =>    null,
            'required'        =>    false,
            'verify'        =>    false,
            'unique'        =>    false,
            'size'    =>    '30',    'maxLength'    =>    '255',
            'rows'    =>    '6',    'cols'        =>    '40',
			'linkQuery'		=>	'select * from payment_gateways where pg_id in (select po_pg_id from payment_gateway_options where po_active = true)',
			'linkQueryValueField'	=>	'pg_id',
			'linkQueryDisplayField'	=>	array( 'pg_id', 'pg_name', 'pg_description' ),
        )));

        require('System/Classes/AssetTypes/ShopSystemAsset/inc_productFields.php');
    }
    
     function edit() {

        if (array_key_exists('prd_id',$this->ATTRIBUTES))
			{
			// hijack this, use it for another table

			$this->tablePrimaryKey = 'prd_id';
			$this->primaryKey = $this->ATTRIBUTES['prd_id'];
            $tableDisplayFields = array('prd_pr_id', 'prd_pr_name','prd_short','prd_long');
			$tableDisplayFieldTitles = array('Product ID', 'Name','Short','Long');
			parent::__construct( array(
					'prefix'                    =>    'ShopSystem_ProductDescriptions',
					'singular'                    =>    'Product',
					'plural'                    =>    'Products',
					'tableName'                    =>    'shopsystem_product_descriptions',
					'tablePrimaryKey'            =>    'prd_id',
					'tableDisplayFields'        =>    $tableDisplayFields,
					'tableDisplayFieldTitles'    =>    $tableDisplayFieldTitles,
					));

			$prd_id = $this->ATTRIBUTES['prd_id'];
			$this->fields = array();
			if( array_key_exists( 'Name', $this->ATTRIBUTES ) )
				$this->singular = $this->singular." in ".$this->ATTRIBUTES['Name'];
			require('System/Classes/AssetTypes/ShopSystemAsset/inc_productLanguageFields.php');
			}
		else
			{
			if (array_key_exists($this->tablePrimaryKey,$this->ATTRIBUTES))
				$this->primaryKey = $this->ATTRIBUTES[$this->tablePrimaryKey];
			}

		if (array_key_exists('as_id',$this->ATTRIBUTES))
			$this->assetLink = $this->ATTRIBUTES['as_id'];

        require('EditAction.php');
        require('EditDisplay.php');
    }



}
    
?>
