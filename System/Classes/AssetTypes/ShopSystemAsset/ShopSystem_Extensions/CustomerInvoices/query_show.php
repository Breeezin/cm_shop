<?php
	$this->param('cin_id');
	$data = getRow( "select * from customer_invoice join customer on cin_cp_id = cp_id join countries on cn_id = cin_to_cn_id where cin_id = ".safe($this->ATTRIBUTES['cin_id']) );
	$data['QCustomerInvoiceItems'] = query( "select * from customer_invoice_line join shopsystem_products on pr_id = cil_pr_id join shopsystem_product_extended_options on pro_pr_id = pr_id where cil_cin_id = ".safe($this->ATTRIBUTES['cin_id']) );
	$data['BackURL'] = $this->ATTRIBUTES['BackURL'];
?>
