<?php
    require_once('session.php');
    require_once('func.php');
    echo "<style type=\"text/css\">@import url(\"/DashBoard/site.css\");</style>";

display_query( "select cast(e.pro_stock_available * 0.85 as unsigned) as Available, p.pr_name as Product, pr0_883_f as NumInBox, p.pr_id from shopsystem_products p join shopsystem_product_extended_options e on pro_pr_id = p.pr_id where e.pro_stock_available > 0 and p.pr_name not like '%shtray%' and p.pr_name not like '%Bespoke%' and p.pr_name not like '%Combo%' and p.pr_name not like '%petaca%' and p.pr_name not like '%Petaca%' and p.pr_name not like '%Behike%' and p.pr_combo IS NULL order by p.pr_name desc" );
#display_query( "select cast(e.pro_stock_available * 0.85 as unsigned) as Available, p.pr_name as Product, pr0_883_f as NumInBox, o.pr_id from onsell o join shopsystem_products p on p.pr_id = o.pr_id join shopsystem_product_extended_options e on pro_pr_id = o.pr_id where e.pro_stock_available > 0 and days_stock > 7 and ca_name not like '%shtray%' and o.pr_name not like '%shtray%' and o.pr_name not like '%Bespoke%' and o.pr_name not like '%Combo%' and o.pr_name not like '%petaca%' and o.pr_name not like '%Petaca%' and o.pr_name not like '%Behike%' and p.pr_combo IS NULL order by p.pr_name, days_stock desc;");

	exit;
?>
