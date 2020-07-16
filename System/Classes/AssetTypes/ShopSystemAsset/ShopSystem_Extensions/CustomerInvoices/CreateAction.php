<?php
	$errors = array();
	if (array_key_exists('DoAction',$this->ATTRIBUTES)) {		
		$this->loadFieldValuesFromForm($this->ATTRIBUTES);

		// default invoice to values from supplier record
		$cp_id = (int)$this->ATTRIBUTES['cin_cp_id'];
		if( $cp_id > 0 )
		{
			if( $cp = getRow( "select * from customer where cp_id = $cp_id" ) )
			{
//				if( !strlen( $this->ATTRIBUTES['sin_currency'] ) )
//					$this->ATTRIBUTES['sin_currency'] = $sp['sp_default_currency'];

				if( !strlen( $this->ATTRIBUTES['cin_discount'] ) )
					$this->ATTRIBUTES['cin_discount'] = $cp['cp_default_discount'];

			$this->loadFieldValuesFromForm($this->ATTRIBUTES);
			}

		}

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
