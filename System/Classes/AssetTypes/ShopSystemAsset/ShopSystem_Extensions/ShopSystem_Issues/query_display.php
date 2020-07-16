<?php

	$issues_filters = array();
	if( array_key_exists( 'Filters', $_SESSION ) )
		if( is_array( $_SESSION['Filters'] ) )
			if( array_key_exists( 'Issues', $_SESSION['Filters'] ) )
				if( is_array(  $_SESSION['Filters']['Issues'] ) )
					$issues_filters =  $_SESSION['Filters']['Issues'];
					/*
				else
					$_SESSION['Filters']['Issues'] = array();
			else
				$_SESSION['Filters']['Issues'] = array();
		else
		{
			$_SESSION['Filters'] = array();
			$_SESSION['Filters']['Issues'] = array();
		}
	else
	{
		$_SESSION['Filters'] = array();
		$_SESSION['Filters']['Issues'] = array();
	}
*/

	foreach( $this->ATTRIBUTES as $name => $val )
		if( strpos( $name, '_filter' ) )
			$issues_filters[$name] = $val;

	$_SESSION['Filters']['Issues'] = $issues_filters;

	$fclauses = array();

	if( array_key_exists( 'admin_filter', $issues_filters ) && strlen( $issues_filters['admin_filter'] ) )
		$fclauses[] = 'ci_assigned_to '.$issues_filters['admin_filter'];

	if( array_key_exists( 'closed_filter', $issues_filters ) && strlen( $issues_filters['closed_filter'] )  )
		$fclauses[] = 'ci_closed '.$issues_filters['closed_filter'];
	else
	{
		$issues_filters['closed_filter'] = 'IS NULL';
		$fclauses[] = 'ci_closed IS NULL';
		$_SESSION['Filters']['Issues'] = $issues_filters;
	}

	if( array_key_exists( 'user_filter', $issues_filters ) )
	{
		if( strlen( $issues_filters['user_filter'] ) &&  array_key_exists( 'user_filter_type', $issues_filters ) )
		{
			$search = ltrim(rtrim($issues_filters['user_filter']) );

			if( $issues_filters['user_filter_type'] == 'Name' )
				$fclauses[] = "(ci_us_id in (select us_id from users where us_first_name like '%$search%' or us_last_name like '%$search%'))";

			if( $issues_filters['user_filter_type'] == 'Email' )
				$fclauses[] = "(ci_us_id in (select us_id from users where us_email like '%$search%') or (ci_verified_email like  '%$search%'))";

			if( $issues_filters['user_filter_type'] == 'OrderNumber' )
				$fclauses[] = "(ci_transaction_number = $search)";
		}
	}

	if( count($fclauses) )
		$fclause = 'where '.implode( ' and ', $fclauses );
	else
		$fclause = '';

//	print( "select ci_id as pci_id, max( cir_created) as pcir_created from client_issue left join client_issue_response on cir_ci_id = ci_id $fclause group by pci_id" );
	// what is the last admin response?
	query( "create temporary table last_admin_response as select ci_id as pci_id, max( cir_created) as pcir_created from client_issue left join client_issue_response on cir_ci_id = ci_id $fclause group by pci_id" );

	// easier this way...
	query( "update last_admin_response set pcir_created = '1990-01-01 00:01' where pcir_created IS NULL" );

	// what is the earliest client entry AFTER this response 
	query( "create temporary table earliest_entry_after_response as select pci_id, pcir_created, min( ce_created ) as pce_created from last_admin_response left join client_issue_entry on ce_ci_id = pci_id and ce_created > pcir_created group by pci_id" );

	// if there is a entry after the response
	$Q_New = query( "select cl.*, client_issue.*, client_issue_entry.*, ad.us_first_name as admin, earliest_entry_after_response.*
		from earliest_entry_after_response
			join client_issue on pci_id = ci_id
			join client_issue_entry on ci_id = ce_ci_id and ce_created = pce_created
			left join users cl on ci_us_id = cl.us_id
			left join users ad on ad.us_id = ci_assigned_to
		where pcir_created IS NOT NULL and pce_created IS NOT NULL order by UNIX_TIMESTAMP(pce_created)" );

	// if there isn't
	$Q_Awaiting = query( "select cl.*, client_issue.*, ad.us_first_name as admin, earliest_entry_after_response.*, client_issue_response.*
		from earliest_entry_after_response
			join client_issue_response on cir_ci_id = pci_id and pcir_created = cir_created
			join client_issue on pci_id = ci_id
			left join users cl on ci_us_id = cl.us_id
			left join users ad on ad.us_id = ci_assigned_to
			where pcir_created IS NOT NULL and pce_created IS NULL order by UNIX_TIMESTAMP(pcir_created)" );

	$Q_Administrators = query( "select * from user_user_groups join users on us_id = uug_us_id where uug_ug_id = 1 and us_admin_level & ".ADMIN_CUSTOMER_ISSUE );


?>
