<?php 
	$result = query("
		SELECT * FROM credit_card_types, web_pay_configuration_credit_card_types  
		WHERE 		
			cct_id = wpcf_cct_id			
		ORDER BY cct_name desc
	");
			
	return $result;
?>
