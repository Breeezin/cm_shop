<?php
	$this->param('sin_id');
	$data = getRow( "select * from supplier_invoice join supplier on sin_sp_id = sp_id join countries on cn_id = sin_from_cn_id where sin_id = ".safe($this->ATTRIBUTES['sin_id']) );
	$data['QSupplierInvoiceItems'] = query( "select * from supplier_invoice_line join shopsystem_products on pr_id = sil_pr_id join shopsystem_product_extended_options on pro_pr_id = pr_id where sil_sin_id = ".safe($this->ATTRIBUTES['sin_id']) );
	$data['BackURL'] = $this->ATTRIBUTES['BackURL'];
?>
