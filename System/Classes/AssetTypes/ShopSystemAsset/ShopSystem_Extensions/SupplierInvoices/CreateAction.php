<?php
	$errors = array();
	if (array_key_exists('DoAction',$this->ATTRIBUTES)) {		
		$this->loadFieldValuesFromForm($this->ATTRIBUTES);

		// default invoice to values from supplier record
		$sp_id = (int)$this->ATTRIBUTES['sin_sp_id'];
		if( $sp_id > 0 )
		{
			if( $sp = getRow( "select * from supplier where sp_id = $sp_id" ) )
			{
//				if( !strlen( $this->ATTRIBUTES['sin_currency'] ) )
//					$this->ATTRIBUTES['sin_currency'] = $sp['sp_default_currency'];

				if( !strlen( $this->ATTRIBUTES['sin_discount'] ) )
					$this->ATTRIBUTES['sin_discount'] = $sp['sp_default_discount'];

			$this->loadFieldValuesFromForm($this->ATTRIBUTES);
			}

		}

		$this->ATTRIBUTES['sin_instock_date'] = '';

		// Validate and then write to the database
		$errors = $this->insert();
		//ss_DumpVarDie($errors);
		// Return if no error messages were returned
		if (count($errors) == 0) {
			// Return (to the list of records hopefully)
			//rfaReturn();
			////////location($this->ATTRIBUTES['RFA']);
			location($this->ATTRIBUTES['BackURL']);
		}
	}
?>
