<?php
	$this->param("as_id","");	
	$this->param("ImageWidth","");	
	$this->param("ImageHeight","");	
	if (array_key_exists('DoAction',$this->ATTRIBUTES)) {
		//<!--- Find the ID of the images folder off the root --->
		$assetName = "";
		if (array_key_exists('Ast_Image_FileField', $_FILES) and is_uploaded_file($_FILES['Ast_Image_FileField']['tmp_name'])) {
			
			$parentLink = ss_systemAsset('Images');
			$result = new Request("UID.Get");
			
			
			$allowedCharacters = 'abcdefghijklmnopqrstuvwxyz 0123456789-,.)(';
		
	    	$temp = $_FILES['Ast_Image_FileField']['name'];
	    	$upload_file = '';
		    for ($i=0; $i < strlen($temp); $i++){
		    	$chr = substr($temp, $i, 1);
		    	$tempChr = strtolower($chr);
		    	if (strstr($allowedCharacters, $tempChr) !== false) {
		    		$upload_file .= $chr;
		    	}
		    }
		    
		    $assetName = ss_newAssetName($upload_file,$parentLink);			
		    
			//!--- Add a new asset --->
			$result = new Request('Asset.Add',array(
				'as_name'	=>	$assetName,
				'as_type'	=>	'Image',
				'as_parent_as_id'	=>	$parentLink,
				'as_appear_in_menus'	=>	0,
				'DoAction'	=>	1,
				'AsService'	=>	true,
				'OnlineNow' =>	1,
			));
			//ss_DumpVarDie($result, $assetName.' vs '.$upload_file);
			$newID = $result->value;
			if ($newID !== null) {
				$extension = array_pop(explode('.', basename($upload_file)));				
				$oldLocation = $_FILES['Ast_Image_FileField']['tmp_name'];		
				$fileName = md5(rand()).".".$extension;	
				
				$assetCereal = array();
				$assetCereal['AST_IMAGE_STD'] = $fileName;
								
				$assetCereal = serialize($assetCereal);
				//ss_DumpVarDie($assetCereal);
				$Q_UpdateAsset = query("UPDATE assets SET as_serialized = '{$assetCereal}' WHERE as_id = {$newID};");
				
				$newLocation = expandPath(ss_storeForAsset($newID).$fileName);
				//ss_DumpVar($oldLocation);
				//ss_DumpVar($newLocation);
				//ss_DumpVarDie($assetCereal);
				$this->ATTRIBUTES['as_id'] = $newID;
				move_uploaded_file($oldLocation,$newLocation);
				
				//chmod($newLocation,0777);
			}	
		}
	}
?>