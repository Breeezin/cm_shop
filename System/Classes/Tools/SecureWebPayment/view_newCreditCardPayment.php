<?php 
	if ($display) {
		//{tmpl_eval $data['AssetTypeObject']->edit($data['this']);}
		$data = array();
		$this->display->title = "Payment";
		
		$data['ProcessorForm'] = $processorType->newPayment($this);
				
		$data['errors'] = $errors;
		
		$data['Fields'] = $this->payment;
		$data['FormAction'] = $_SERVER['SCRIPT_NAME'].'?act='.$this->ATTRIBUTES['act'].'&DoAction=1';
		$data['tr_id'] = $this->ATTRIBUTES['tr_id'];
		$data['tr_token'] = $this->ATTRIBUTES['tr_token'];
		$data['BackURL'] = $this->ATTRIBUTES['BackURL'];
		$data['Type'] = $this->ATTRIBUTES['Type'];
	
		$data['ChargedIn'] = $chargedIn;
		ss_customStyleSheet('shop');
		print $this->processTemplate("PaymentEdit",$data);	
	} else {		
		$this->display->title = 'Processing...';
?>
	
		<p align="center" style="font-family : Verdana, Geneva, Arial, Helvetica, sans-serif;font-size:10pt;">
		Processing, please wait...
		</p>
		<script language="Javascript">
			function doRedirect() {
				document.location = '<?=ss_JSStringFormat($this->ATTRIBUTES['BackURL'])?>';
			}
			setTimeout('doRedirect()',2000);
		</script>
	
<?php		
	}	
?>
