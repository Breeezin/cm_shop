<?php
	
	$Q_ChangesProducts = query("
		SELECT * FROM audit
		join users on au_userid = us_id
		left join shopsystem_products on au_key = pr_id
		left join shopsystem_product_extended_options on pro_pr_id = pr_id
		where au_table = 'Products'
			and au_timestamp > NOW() - INTERVAL 2 DAY
			and us_admin_level > 0
		order by au_id desc
	");	

?>
