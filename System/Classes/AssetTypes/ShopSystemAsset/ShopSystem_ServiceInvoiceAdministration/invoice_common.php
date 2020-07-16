<?php

	$siv_id = $this->ATTRIBUTES['siv_id'];

	$details = getRow( "Select *, 
		scf.sic_name as SiCoNameFrom, scf.sic_template_suffix as SiCoTemplateSuffixFrom, scf.sic_email_address as SiCoEmailAddressFrom,
		sct.sic_name as SiCoNameTo, sct.sic_template_suffix as SiCoTemplateSuffixTo, sct.sic_email_address as SiCoEmailAddressTo,
		sct.sic_invline2 as Add2, sct.sic_invline3 as Add3, sct.sic_invline4 as Add4, 
		sct.sic_invline5 as Add5, sct.sic_invline6 as Add6, sct.sic_invline7 as Add7
		from shopsystem_service_invoice si, shopsystem_service_company scf, shopsystem_service_company sct where si.siv_to_sic_id = sct.sic_id and si.sic_id = scf.sic_id and si.siv_id = ".$siv_id );

	if( $details )
		{

/*
		ss_DumpVarDie( $details );
   [siv_id] => 1
    [sic_id] => 2
    [siv_to_sic_id] => 2
    [siv_created_date] => 2008-01-24
    [siv_paid_date] => 2008-10-16
    [siv_external_reference] => Number1
    [siv_notes] => Internet Consulting and Management
    [siv_1_created_date] => 2008-08-20
    [siv_1_text] => Content Management
    [siv_1_hours] => 1
    [siv_1_amount] => 4995
    [siv_1_tax] => 0
    [siv_2_created_date] => 2008-08-20
    [siv_2_text] => Adwords Campaign Management 
    [siv_2_hours] => 1
    [siv_2_amount] => 2800
    [siv_2_tax] => 0
    [siv_3_created_date] => 2008-01-01
    [siv_3_text] => Market Research
    [siv_3_hours] => 1
    [siv_3_amount] => 210
    [siv_3_tax] => 0
    [siv_4_created_date] => 1999-11-30
    [siv_4_text] => 
    [siv_4_hours] => 
    [siv_4_amount] => 
    [siv_4_tax] => 
    [siv_5_created_date] => 1999-11-30
    [siv_5_text] => 
    [siv_5_hours] => 
    [siv_5_amount] => 
    [siv_5_tax] => 
    [siv_6_created_date] => 1999-11-30
    [siv_6_text] => 
    [siv_6_hours] => 
    [siv_6_amount] => 
    [siv_6_tax] => 
    [sic_name] => Highco Technology Limited
    [sic_template_suffix] => _ht
    [sic_email_address] => biteme@admin.com
    [SiCoNameFrom] => Totara Corporation
    [SiCoTemplateSuffixFrom] => _tc
    [SiCoEmailAddressFrom] => gort@admin.com
    [SiCoNameTo] => Highco Technology Limited
    [SiCoTemplateSuffixTo] => _ht
    [SiCoEmailAddressTo] => biteme@admin.com
*/

		$data = array(
			'CreatedDate'	=>	$details['siv_created_date'],
			'PaidDate'	=>	$details['siv_paid_date'],
			'Notes'	=>	$details['siv_notes'],
			'Amount'	=>	'$'.number_format( $details['siv_1_amount']+$details['siv_2_amount']+$details['siv_3_amount']+$details['siv_4_amount']+$details['siv_5_amount']+$details['siv_6_amount']),
			'ID'	=>	$details['siv_id'],
			'ExternalReference'	=>	$details['siv_external_reference'],
			'rate1' => ($details['siv_1_hours']> 0 ? '$'.number_format($details['siv_1_amount']/$details['siv_1_hours']):''),
			'rate2' => ($details['siv_2_hours']> 0 ? '$'.number_format($details['siv_2_amount']/$details['siv_2_hours']):''),
			'rate3' => ($details['siv_3_hours']> 0 ? '$'.number_format($details['siv_3_amount']/$details['siv_3_hours']):''),
			'rate4' => ($details['siv_4_hours']> 0 ? '$'.number_format($details['siv_4_amount']/$details['siv_4_hours']):''),
			'rate5' => ($details['siv_5_hours']> 0 ? '$'.number_format($details['siv_5_amount']/$details['siv_5_hours']):''),
			'rate6' => ($details['siv_6_hours']> 0 ? '$'.number_format($details['siv_6_amount']/$details['siv_6_hours']):''),
		);

		foreach( $details as $index=>$val )
			{
			if( strstr( $index, "Date" ) )
				if( substr( $val, 0, 4 ) > '0000' )
					$data[$index] = $val;
				else
					$data[$index] = '';
			else
				if( strstr( $index, "Amount" ) )
					if( $val > 0 )
						$data[$index] = '$'.number_format( $val );
					else
						$data[$index] = '';
				else
					if( strstr( $index, "Tax" ) )
						if( $val > 0 )
							$data[$index] = '$'.number_format( $val );
						else
							$data[$index] = '';
					else
						$data[$index] = $val;
			}
		//ss_DumpVarDie( $data );
		}

