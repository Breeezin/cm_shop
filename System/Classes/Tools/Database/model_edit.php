<?php 
	$this->param("CSSName",'sty_main');
	$this->param("Action",'');
	
	if(array_key_exists("DoAction", $this->ATTRIBUTES) AND $this->ATTRIBUTES['Action'] == "Save") {
		$this->param("CSSContent");
		$this->param("AppearsIn");
		
		$ListStylesheets = ListToArray(file_get_contents(expandPath('Custom/ContentStore/Layouts/Stylesheets.txt')),chr(10));				
		$name = ListLast($this->ATTRIBUTES['CSSName'], '_');
		$index = "";
		$counter = null;
		$i = 0;
		for($i=0; $i < count($ListStylesheets); $i++) {			
			if($name == ListFirst($ListStylesheets[$i],":")) {			
				$counter = $i;				
				break;
			}	
		}
		$newStylesheets = array();
		if($this->ATTRIBUTES['AppearsIn'] == "Yes") {
			if ($counter == null) {
				$char = strtoupper(substr($name,0,1));
				$tempName = substr_replace($name,$char,0,1);
				array_push($ListStylesheets, "$name:$tempName");
			}
			$newStylesheets = $ListStylesheets;
		} else {			
			foreach($ListStylesheets as $sheet) {	
				
				if($name != ListFirst($sheet,":")) {					
					array_push($newStylesheets, $sheet);
				}
			}
		}
		
		$dir = expandPath('Custom/ContentStore/Layouts');
		
		$newcontent = ArrayToList($newStylesheets, chr(10));
		$txtStylesheet = 'Stylesheets.txt';
		ss_deleteFile($dir,$txtStylesheet);
			
		if ($fh = fopen("$dir/$txtStylesheet", 'a+')){
			// write to file 
			fwrite($fh, $newcontent) or die("Could not write to file"); 
			// close file 
			fclose($fh); 
		} else {
			 die("Could not open file!"); 
		}		
	
		$content2 = $this->ATTRIBUTES['CSSContent'];
		$fileName = "{$this->ATTRIBUTES['CSSName']}.css";
		
		ss_deleteFile($dir, $fileName);
			
		if ($fh = fopen("$dir/$fileName", 'a+')){
			// write to file 
			fwrite($fh, $content2) or die("Could not write to file"); 
			// close file 
			fclose($fh); 
		} else {
			 die("Could not open file!"); 
		}		
	}
?>