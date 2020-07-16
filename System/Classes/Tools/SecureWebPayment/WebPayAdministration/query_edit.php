<?php 
	$this->param("wpc_id", 1);
	$this->param("BackURL", '');
	$this->param("BreadCrumbs", '');
	$errors = array();
	$this->configuration = getRow("SELECT * FROM web_pay_configuration");
		
	$this->display->title = $this->ATTRIBUTES['BreadCrumbs']." : Web Pay configuration";
	$this->primaryKey = $this->ATTRIBUTES['wpc_id'];
		
	$this->loadFieldValues($this->ATTRIBUTES,$this->configuration);
	
	//ss_DumpVarDie($this->configuration);
	if (strlen($this->configuration['wpc_default_currency_details'])) {
		//$this->cereal = deserialize($row['as_serialized']);
		$this->currencySettings = unserialize($this->configuration['wpc_default_currency_details']);
		if ($this->currencySettings == NULL) $this->currencySettings = array();	
	} else {
		$this->currencySettings = array();	
	}
	
		
	requireOnceClass('CurrencySettings');
	$currencySettings = new CurrencySettings;
	$currencySettings->defineFields(&$this);	
	$currencySettings->fieldSet->loadFieldValues($this->ATTRIBUTES,$this->currencySettings,$this->isEdit($this->ATTRIBUTES));
?>