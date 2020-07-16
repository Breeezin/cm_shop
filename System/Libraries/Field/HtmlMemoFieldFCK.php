<?php

	include('System/Libraries/Field/FCKeditor/fckeditor.php');
		
	class HtmlMemoField2 extends TextField {
		var $paraFormats = array(
			"none" 	=> "None",
			"h1"	=> "Header 1",
			"h2"	=> "Header 2",
			"h3"	=> "Header 3",
			"pre"	=> "Preformatted"
		);
		var $fontFaces 	= array(
			"Arial", "Verdana", "Geneva", "Courier New" , "Times New Roman" , "Wingdings"
		);		
		var $fontSizes = array(1, 2, 3, 4, 5, 6, 7);
		
		var $width = null;
		var $height = 400;
		var $fckWidth = null;
		var $fckHeight = null;
		
		function HtmlMemoField2($settings) {
			/*global $cfg;
			$defaults = array(
				"width" => "100%", 
				"height" => "256px", 
				"cols" => 60, 
				"rows" => 10, 
				"pageEdit" => false, 
				"singleSpaced" => false, 
				"wordCount" => false, 
				"baseURL" => $cfg['currentServer'],
				"scriptPath" => "-Libraries/Field/siteobjects/soeditor/lite/"
			); // There are lots of other soEditor options but I am too lazy to use them
//			var_dump($defaults);*/
			//$settings = $this->checkSettings($settings, $defaults);
			$this->Field($settings);						
		}
		
		function parSet($key, $current, $default = NULL) {
			if ($current === NULL) {
				if ($default === NULL) {
					die("Value not supplied for $key and no default given. " . __FILE__ . " Line " . __LINE__);
				};
				return $default;
			}
		}
		
		function validate() {
			global $cfg;
			// This sucks, but the htmlarea always returns absolute paths.
			$this->value = stri_replace($cfg['currentServer'],'',$this->value);
			return NULL;
		}		
		
		function checkSettings($settings, $defaults) {
			foreach ($defaults as $key => $value) {
				if (array_key_exists($key,$settings)) {
					$settings[$key] = $this->parSet($key, $settings[$key], $value);
				} else {
					$settings[$key] = $this->parSet($key, NULL, $value);
				}
			};
			return $settings;
		}
		
		function displayValue($value) {
			return ss_parseText($value);
		}
		
		function display($verify=FALSE, $formName=FALSE) {
			
			global $cfg;

			$xmlStyles = 'Custom/ContentStore/Layouts/'.$GLOBALS['cfg']['currentSiteFolder'].'Classes.xml';
			$xmlStylesDef = 'Custom/ContentStore/Layouts/'.$GLOBALS['cfg']['currentSiteFolder'].'Classes.txt';
			$updateCache = false;
			if (file_exists($xmlStyles)) {
				clearstatcache();
				$cacheTime = filemtime($xmlStyles);
				$defTime = filemtime($xmlStylesDef);
				if ($cacheTime < $defTime) {
					$updateCache = true;
				}
			} else {
				$updateCache = true;
			}			
			if ($updateCache) {
				$def = file_get_contents($xmlStylesDef);
				$output = '';
				foreach (ListToArray($def,chr(10)) as $theDef) {
					$className = ListFirst(trim($theDef),":");
					$className = ListFirst($className,".");
					$classDesc = ListLast(trim($theDef),":");
					$output .= '<Style name="'.ss_HTMLEditFormat($classDesc).'" element="span"><Attribute name="class" value="'.ss_HTMLEditFormat($className).'" /></Style>';
				}
				$output = '<?xml version="1.0" encoding="utf-8" ?><Styles>'.$output.'</Styles>';
				$newHandle = fopen($xmlStyles,'w');
				flock($newHandle,LOCK_EX);
				fwrite($newHandle,$output);
				flock($newHandle,LOCK_UN);
				fclose($newHandle);
				
			}
			
			$sBasePath = 'System/Libraries/Field/FCKeditor/';
			$oFCKeditor = new FCKeditor($this->name) ;
			$oFCKeditor->BasePath	= $sBasePath ;
			$oFCKeditor->Value		= $this->value;
			$oFCKeditor->Height = $this->height;	
			$oFCKeditor->Config = array(
				'EditorAreaCSS' => $GLOBALS['cfg']['currentServer'].'Custom/ContentStore/Layouts/'.$GLOBALS['cfg']['currentSiteFolder'].'sty_main.css',
				'SkinPath'		=>	$GLOBALS['cfg']['currentServer'].$sBasePath.'editor/skins/silver/',
				'StylesXmlPath'	=>	$GLOBALS['cfg']['currentServer'].$xmlStyles,
			);
			if ($this->fckWidth !== null) $tihs->width = $this->fckWidth;
			if ($this->fckHeight !== null) $tihs->height = $this->fckHeight;
			
			// render the editor and grab the output
			ob_start();
			$oFCKeditor->Create();		
			$retVal = ob_get_contents();
			ob_end_clean();

			return $retVal;					
		}		
}
?>