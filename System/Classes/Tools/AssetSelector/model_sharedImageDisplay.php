<?php
	$this->param("as_id");
	$this->param("OnClick");
	$this->param("SelectedIamge", "");
	$this->param("Folder", "");
	$error = '';
	
	if (strlen($this->ATTRIBUTES['Folder'])) {
		$directory = ss_secretStoreForAsset($this->ATTRIBUTES['as_id'], $this->ATTRIBUTES['Folder'])."/";
	} else {
		$directory = ss_storeForAsset($this->ATTRIBUTES['as_id']);
	}
	
	if (array_key_exists('DoAction',$this->ATTRIBUTES)) {	
		$this->param("Action");
		if ($this->ATTRIBUTES['Action'] == "Upload") {	
			if (is_uploaded_file($_FILES['SharedImage_FileField']['tmp_name'])) {
											
				$imageName = $_FILES['SharedImage_FileField']['name'];			
			
				//!--- Add a new image --->
				
				if (strlen($imageName)) {
					$extension = array_pop(explode('.', basename($_FILES['SharedImage_FileField']['name'])));
					
					$oldLocation = $_FILES['SharedImage_FileField']['tmp_name'];														
					$newLocation = expandPath($directory.$imageName);											
					if (file_exists($newLocation)) {
						$error = "$imageName is already existing in the folder.";
					} else {
						move_uploaded_file($oldLocation,$newLocation);
					}
					
				}	
			}
		} else {		
			$dir = expandPath($directory);
			$test = ss_deleteFile($dir, $this->ATTRIBUTES['SelectedImage']);			
		}
	}
?>