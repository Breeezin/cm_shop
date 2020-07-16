<?php
	$this->param("ReturnField");	
	$this->param("ReturnJSFunction", "");	
	$this->param("Preview");	
	
	if (array_key_exists('DoAction',$this->ATTRIBUTES)) {
		if (is_uploaded_file($_FILES['NewFile']['tmp_name'])) {
/*			$extension = array_pop(explode('.', basename($_FILES['NewFile']['name'])));	*/
			$extension = "";
			if( $pos = strrpos( $_FILES['NewFile']['name'], "." ) )
				$extension = substr( $_FILES['NewFile']['name'], $pos+1 );
			$result = new Request("UID.Get");
			$this->ATTRIBUTES['NewFileName'] = md5($result->value).".".$extension;
			
			$oldLocation = $_FILES['NewFile']['tmp_name'];
			$newLocation = expandPath("Custom/Cache/Incoming/{$this->ATTRIBUTES['NewFileName']}");
			
			move_uploaded_file($oldLocation,$newLocation);
			//chmod($newLocation,0777);
		}
	}
?>
