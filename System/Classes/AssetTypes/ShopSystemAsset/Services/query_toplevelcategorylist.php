<?php
//	$this->param('Template','TopLevelCategoryList');

//	$asset->display->layout = 'None';

//	$this->useTemplate($this->ATTRIBUTES['Template'],$data);	

	$this->param('OrderBy','SortOrder');
	$this->param('Template','TopLevelCategoryList');

	switch (strtolower($this->ATTRIBUTES['OrderBy'])) {
		case 'alphabetical':	
			$orderBy = 'ca_name'; 
			break;
		default:
			$orderBy = 'ca_sort_order,ca_name'; 
	}
	
	$data = array(
		'AssetPath'	=>	ss_EscapeAssetPath($asset->getPath()),
	);

	global $cfg;

	$externalSQL = '1 = 1';

	if( $_SESSION['ForceCountry']['cn_two_code'] != 'US' )
		$externalSQL = 'PrUsOnly = "false"';
	else
		if( ss_countryISEU( $_SESSION['ForceCountry']['cn_two_code'] ) )
			$externalSQL = 'pr_ship_to_eu = 1';
		else
			$externalSQL = 'pr_ship_to_non_eu = 1';

	if( $_SESSION['ForceCountry']['cn_generic_limit'] > 0 )
		$externalSQL .= " and pr_id in (select pro_pr_id from shopsystem_product_extended_options where pro_weight > 0 and pro_weight <= ".(int)$_SESSION['ForceCountry']['cn_generic_limit'].")";

	$data['countQuery'] = "select count(distinct pr_id ) as available FROM shopsystem_products join shopsystem_product_extended_options on pr_id = pro_pr_id
						where pr_deleted IS NULL and pro_stock_available > 1 and pr_offline is NULL and pr_is_service = 'false' and $externalSQL and pr_ca_id ";

	$data['Q_Categories'] = query("
		SELECT * FROM shopsystem_categories
		  join category_navigation on ca_nav_id = cnv_id
		  join site_category_mask on scm_ca_id = ca_id and scm_lg_id = {$cfg['currentLanguage']} and scm_ca_active = 1
		WHERE ca_as_id = ".$asset->getID()."
			".ss_shopRestrictedCategoriesSQL()."
		group by ca_id
		ORDER BY cnv_sort, ca_sort_order, ca_name
	");	

	$data['categories'] = array();

	while( $row = $data['Q_Categories']->fetchRow() )
	{
		$data['categories'][$row['ca_id']] = $row;
		$data['categories'][$row['ca_id']]['Children'] = array();
	}

	foreach( $data['categories'] as $ind => $val )
		if( $data['categories'][$ind]['ca_parent_ca_id'] > 0 )
			$data['categories'][$data['categories'][$ind]['ca_parent_ca_id']]['Children'][] = $ind;

/*
	foreach( $data['categories'] as $ind => $val )
	{
		$cats = array( $val['ca_id'] );
		add_children( $data, $ind, &$cats );
		$foo = getRow( $data['countQuery']."in (".implode( ',', $cats ).")" );
		if( $foo['available'] > 0 )
			$data['categories'][$ind]['Available'] = $foo['available'];
		else
			$data['categories'][$ind]['Available'] = 0;
	}
*/

	$asset->display->layout = 'None';

	$this->useTemplate($this->ATTRIBUTES['Template'],$data);	
	
	$data['Q_Categories']->free();


?>
