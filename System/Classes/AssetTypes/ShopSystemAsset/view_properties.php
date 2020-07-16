<?php
	$assetID = $asset->getID();
	$prodCount = getRow("
		SELECT COUNT(*) AS theCount FROM shopsystem_products
		WHERE pr_deleted IS NULL 
			AND pr_as_id = $assetID
	");
	$catCount = getRow("
		SELECT COUNT(*) AS theCount FROM shopsystem_categories
		WHERE ca_as_id = $assetID
	");
	$orderCount = getRow("
		SELECT COUNT(*) AS theCount FROM shopsystem_orders, transactions
		WHERE or_tr_id = tr_id
			AND tr_completed = 1
			AND or_archive_year IS NULL
			AND or_as_id = $assetID
	");
	$accessCode = "";
	if (array_key_exists('AccessCode', $_SESSION)) {
		$accessCode = "&AccessCode=".$_SESSION['AccessCode'];
	}
?>
<table cellpadding="0" cellspacing="4" width="100%">
<? if (ss_optionExists('Sell Products')) { ?>
<tr><td><li><a href="javascript:void(0);" onclick="res=window.open('<?=ss_withTrailingSlash($GLOBALS['cfg']['secure_server']);?>index.php?act=WebPayAdministration.List&as_id=&as_id=<?=$assetID?><?=$accessCode?>','OrderManager','width=760,height=480,scrollbars=yes,menubar=yes,resizable=yes');res.focus();return false;">Orders</a> (<?=$orderCount['theCount']?>)</li></td></tr>
<? } ?>
<tr><td><li><a href="javascript:openNamedNonAssetPanel('index.php?act=ShopSystem_ServiceInvoiceAdministration.List&as_id=<?=$assetID?>','Manage Invoices','InvoiceManager');void(0);">Invoices</a></li></td></tr>
<tr><td><li><a href="javascript:openNamedNonAssetPanel('index.php?act=ShopSystem_ProductsAdministration.List&as_id=<?=$assetID?>','Manage Products','ShopSystemProductsManager');void(0);">Products</a> (<?=$prodCount['theCount']?>)</li></td></tr>
<tr><td><li><a href="javascript:openNamedNonAssetPanel('index.php?act=ShopSystem_CategoriesAdministration.List&as_id=<?=$assetID?>','Manage Categories','ShopSystemCategoriesManager');void(0);">Categories</a> (<?=$catCount['theCount']?>)</li></td></tr>
</table>
