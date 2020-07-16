<?php
//this was added for Kovacs/Postie plus
	if (array_key_exists('DoAction',$this->ATTRIBUTES)) {
		// add the product into the profile
		
        $usID = isset($this->ATTRIBUTES['us_id']) ? $this->ATTRIBUTES['us_id'] : $_SESSION['User']['us_id'];

		if (isset($this->ATTRIBUTES['Products']) and isset($usID)) {
            foreach ($this->ATTRIBUTES['Products'] as $product){
    			$optionID = ListLast($product,'_');

    			$option = getRow("
    				SELECT * FROM shopsystem_product_extended_options
    				WHERE pro_id = ".safe($optionID)."
    			");

    			$stockCode = $option['pro_stock_code'];

    			// Delete it just in case its already there
    			$Q_Clean = query("
    				DELETE FROM ShopSystem_UserProducts
    				WHERE UpStockCode LIKE '".escape($stockCode)."'
    					AND UpUserLink LIKE '{$usID}'
    			");

    			// Now insert a fresh record
    			$Q_Insert = query("
    				INSERT INTO ShopSystem_UserProducts
    					(UpStockCode, UpUserLink)
    				VALUES
    					('".escape($stockCode)."','{$usID}')
    			");
            }

		} else {
			$error = '<p style="color:red;">Please ensure you have selected atleast one product.</p>';
		}
    }

?>