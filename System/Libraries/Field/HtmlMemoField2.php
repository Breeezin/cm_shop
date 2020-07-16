<?php
		
	class HtmlMemoField2 extends TextField {
		var $tableWidth = '100%';
		var $disabledTools = array();
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
		var $height = 300;
		
		//function HtmlMemoField2($settings) {
		function __construct($settings) {
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
			parent::__construct($settings);						
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
		function display($verify = FALSE, $formName = NULL, $multi = TRUE, $class = NULL) {
			
			global $cfg;
			
			/* Display a text type field with a button on the end of it to 
			 * open the image manager for selection of an image, also output
			 * a javascript function to write the receieved value into the field */
			
//			ss_log_message( "HTMLMemoField2" );
//			ss_log_message_r(  'Log:'.__FILE__.':'.__LINE__, $this->value );
			$encHTML = ss_HTMLEditFormat($this->value);
//			ss_log_message( $encHTML );
						
//			ss_DumpVarDie('Custom/ContentStore/Layouts/'.$GLOBALS['cfg']['currentSiteFolder'].'sty_main.css');

			if( file_exists( 'Custom/ContentStore/Layouts/'.$GLOBALS['cfg']['currentSiteFolder'].'sty_htmlarea.css' ) )
				$encCSS = ss_JSStringFormat(file_get_contents('Custom/ContentStore/Layouts/'.$GLOBALS['cfg']['currentSiteFolder'].'sty_htmlarea.css'));
			else
				$encCSS = '';

			
			// Load up the classes definitions
			$classes = '';
			if (file_exists('Custom/ContentStore/Layouts/Classes.txt')) {
			$classesFile = file_get_contents('Custom/ContentStore/Layouts/Classes.txt');
				foreach(ListToArray($classesFile,chr(10)) as $def) {
					$class = ListFirst($def,":");	
					$classDescription = ListLast($def,":");	
					ss_comma($classes);
					$classes .= "'$class' : '$classDescription'";
				}
				if (strlen($classes)) {
					$classes = "HTMLAreaConfig_{$this->name}.css_style = { $classes };";
				}
			}
			
			$currentDir = dirname($_SERVER['SCRIPT_NAME']);
			if (substr($currentDir,-1) != '/') $currentDir .= "/";	
			
			$htmlAreaDir = "System/Libraries/Field/htmlarea";

			$baseHref = ss_JSStringFormat($GLOBALS['cfg']['currentServer']);
		
			if ($this->width === null) $this->width = 'document.body.clientWidth-77';
			
			$defaultToolBars = '["formatblock","space","bold","italic","underline","strikethrough"],["textindicator","separator"],["subscript","superscript","killword"],["linebreak","justifyleft","justifycenter","justifyright","justifyfull","separator"],["insertorderedlist","insertunorderedlist","outdent","indent","separator"],["inserthorizontalrule","createlink","createanchor","removelink","insertimage","inserttable","separator"],["htmlmode","about"]';
			if (count($this->disabledTools)) {				
				$defaultTools = ListToArray("[,formatblock,space,bold,italic,underline,strikethrough,],[,textindicator,separator,],[,subscript,superscript,killword,],[,linebreak,justifyleft,justifycenter,justifyright,justifyfull,separator,],[,insertorderedlist,insertunorderedlist,outdent,indent,separator,],[,inserthorizontalrule,createlink,createanchor,removelink,insertimage,inserttable,separator,],[,htmlmode,about,]");
				$temp = array();
				foreach ($defaultTools as $item) {
					if (array_search($item,$this->disabledTools) === false) {
						array_push($temp, $item);
					}					
				}
				$previous = '';				
				$defaultToolBars = '';
				foreach ($temp as $iteml) {
					$item = $iteml;
					if ($previous != '' and $previous != '[') {												
						$defaultToolBars .= ',';
						
					} 					
					if ($item == ']') {
						$defaultToolBars = substr($defaultToolBars,0,-1);
					}
						
					if ($item != '[' and $item != ']') {
						$item = '"'.$item.'"';
					}					
				
					$defaultToolBars .= $item;
					$previous = $iteml;
				}
				$defaultToolBars = substr($defaultToolBars,0,-1).']';
			}				
			$retVal = '';

			if (!array_key_exists('HTMLAreaJavascriptAlreadyIncluded',$GLOBALS)) {
				$GLOBALS['HTMLAreaJavascriptAlreadyIncluded'] = TRUE;
				$retVal .= <<<END
				<script type="text/javascript">_editor_url = '$htmlAreaDir';</script>
<script type="text/javascript" src="$htmlAreaDir/htmlarea.js"></script>
<script type="text/javascript" src="$htmlAreaDir/htmlarea_css.js"></script>
<script type="text/javascript" src="$htmlAreaDir/lang/en.js"></script>
<script type="text/javascript" src="$htmlAreaDir/dialog.js"></script>
<script type="text/javascript" src="$htmlAreaDir/popupwin.js"></script>
<!-- load the TableOperations plugin files -->
<script type="text/javascript" src="$htmlAreaDir/plugins/TableOperations/table-operations.js"></script>
<script type="text/javascript" src="$htmlAreaDir/plugins/TableOperations/lang/en.js"></script>
<!-- load the SpellChecker plugin files -->
<script type="text/javascript" src="$htmlAreaDir/plugins/SpellChecker/spell-checker.js"></script>
<script type="text/javascript" src="$htmlAreaDir/plugins/SpellChecker/lang/en.js"></script>
<style type="text/css">
@import url($htmlAreaDir/htmlarea.css);
</style>				
END;

			}
			
			$retVal .= <<<END
<div style="border:1px solid black;width:{$this->tableWidth}"><textarea style="width:100%" rows="20" cols="40" name="{$this->name}" id="{$this->name}">$encHTML</textarea>
<script language="Javascript">
var HTMLArea_{$this->name} = new HTMLArea("{$this->name}");
var HTMLAreaConfig_{$this->name} = HTMLArea_{$this->name}.config; 
	HTMLAreaConfig_{$this->name}.editorURL = '$htmlAreaDir/';
	HTMLAreaConfig_{$this->name}.width = {$this->width};
	HTMLAreaConfig_{$this->name}.height = '{$this->height}px';
	$classes
	HTMLAreaConfig_{$this->name}.pageStyle = '$encCSS';
	HTMLAreaConfig_{$this->name}.baseHref = '$baseHref';
	HTMLAreaConfig_{$this->name}.fontstyles = [{
  name: "headline",
  className: "headline",
  classStyle: "font-family: arial; font-size: 28px;"
},{
  name: "red text",
  className: "saletext2",
  classStyle: ""
}];
	HTMLAreaConfig_{$this->name}.registerButton(
	{id:"removelink", tooltip:"Remove Link", image:"System/Libraries/Field/htmlarea/images/ed_removelink.gif", textMode:false, action:function(e) { e._removeLink() }});
 
	HTMLAreaConfig_{$this->name}.toolbar = [$defaultToolBars]; // denied - "fontname","space","fontsize","space",
	
  	HTMLArea_{$this->name}.registerPlugin("TableOperations");
   HTMLArea_{$this->name}.registerPlugin("SpellChecker");

  	HTMLArea_{$this->name}.generate();
 	
</script></div>
END;

return $retVal;					
		}		
}
?>
