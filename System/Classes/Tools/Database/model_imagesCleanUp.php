<?php 

	$this->param("Fields");
	$this->param("as_id");
	
	$folders = ListToArray($this->ATTRIBUTES['Fields']);
	
	$dirPath = expandPath('');
	
	$Q_AllRecords = query("SELECT * FROM DataCollection_{$this->ATTRIBUTES['as_id']}");			
	
	foreach ($folders as $aFolder) {
		$assetFolder = ss_secretStoreForAsset($this->ATTRIBUTES['as_id'], $aFolder);
				
		$allImages = array();
		
		$dh=opendir($dirPath.$assetFolder);
		while ($file=readdir($dh)){
			if($file!="." && $file!=".."){
				$fullpath=$dirPath.$assetFolder.'/'.$file;
				if(!is_dir($fullpath)){
					$allImages[$file] = array('path'=>$fullpath, 'identified'=>0);
					//print($file);
				}
			}
		}
		closedir($dh);
		
		
		while($aRecord = $Q_AllRecords->fetchRow()) {											
			if (strlen($aRecord['DaCo'.$aFolder]) and array_key_exists($aRecord['DaCo'.$aFolder], $allImages)) {
				$allImages[$aRecord['DaCo'.$aFolder]]['identified'] = 1;
			}			
		}
		ss_DumpVar($allImages);
		foreach ($allImages as $fileInfo) {
			if ($fileInfo['identified'] == 0) {
				ss_DumpVar($fileInfo['path']);	
				unlink($fileInfo['path']);		
				
			}
		}
	}

?>