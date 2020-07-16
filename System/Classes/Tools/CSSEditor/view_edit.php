<?php
		
		
	$ListStylesheets = ListToArray(file_get_contents(expandPath('Custom/ContentStore/Layouts/Stylesheets.txt')),chr(10));
	
	$data = array();
	$allCSSs = array();
	$dh=opendir(expandPath('Custom/ContentStore/Layouts'));
	while ($file=readdir($dh)){
		if($file!="." && $file!=".."){
			$fullpath=expandPath('Custom/ContentStore/Layouts'."/").$file;			
			if(!is_dir($fullpath) AND ListLast($file, '.') == "css"){										
				$inList = "0";
				$fileName = basename($fullpath, ".css");
				foreach($ListStylesheets as $css) {					
					if ("sty_".ListFirst($css,':') == $fileName) {
						$inList = "1";
						break;
					}					
				}
				
				$allCSSs["$fileName"] = $inList; 
				if ($fileName == $this->ATTRIBUTES['CSSName']) 
					$data['CSSContent'] = file_get_contents($fullpath);
			}
		}
	}
	closedir($dh);	
	$data['AllCSSs'] = $allCSSs;	
	//$data['CSSLink'] = expandPath("/ContentStore/Layouts/{$this->ATTRIBUTES['CSSName']}.css");
	$data['CSSLink'] = "Custom/ContentStore/Layouts/{$this->ATTRIBUTES['CSSName']}.css";
	
	$data['SelectedCSS'] = $this->ATTRIBUTES['CSSName'];
	
	print $this->processTemplate('Edit', $data);
	//ss_DumpVar($allCSSs);
	
?>			