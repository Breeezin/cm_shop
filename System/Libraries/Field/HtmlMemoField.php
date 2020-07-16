<?php
		
	class HtmlMemoField extends TextField {
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
		
		function __construct($settings) {
			global $cfg;
			$defaults = array(
				"width" => "100%", 
				"height" => "256px", 
				"cols" => 60, 
				"rows" => 10, 
				"pageEdit" => false, 
				"singleSpaced" => false, 
				"wordCount" => false, 
				"baseURL" => $cfg['currentServer'],
				"scriptPath" => "System/Libraries/Field/siteobjects/soeditor/lite/"
			); // There are lots of other soEditor options but I am too lazy to use them
//			var_dump($defaults);
			$settings = $this->checkSettings($settings, $defaults);
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
		
		
		function display($verify=FALSE, $formName=FALSE, $multi = TRUE, $class = NULL) {
			/* Display a text type field with a button on the end of it to 
			 * open the image manager for selection of an image, also output
			 * a javascript function to write the receieved value into the field */
			
			if ($verify) {
				die("Just how in the hell do you expect a user to verify an HTML field !");
			};
			 
			$encHTML = ss_HTMLEditFormat($this->value);
			
			$formatOptions = "";
			foreach ($this->paraFormats as $fmtCode => $fmtName) {
				$formatOptions = $formatOptions . "<OPTION VALUE=\"$fmtCode\">$fmtName</OPTION>";
			};
					
			$fontOptions = "";
			foreach ($this->fontFaces as $fntName) {
				$fontOptions = $fontOptions . "<OPTION VALUE=\"$fntName\">$fntName</OPTION>";
			};
			
			$sizeOptions = "";
			foreach ($this->fontSizes as $sizeName) {
				$sizeOptions = $sizeOptions . "<OPTION VALUE=\"$sizeName\">$sizeName</OPTION>";
			};
			
			$imageDirectory = ss_JSStringFormat($this->Directory);
			
			$retVal =  <<<END
				<SCRIPT LANGUAGE="Javascript">
					htmlMemoField_imageDirectory = '{$imageDirectory}';
				</SCRIPT>	
				<?xml:namespace prefix="so" />
				<script language="javascript" src="{$this->scriptPath}spch.js" defer="true"></script>
				<link rel="stylesheet" type="text/css" href="{$this->scriptPath}sotoolbar.css"></link>
				<style>
				so\:editor {behavior: url({$this->scriptPath}soeditor.htc);}
				so\:toolbar {behavior: url({$this->scriptPath}sotoolbar.htc);}    
				so\:toolbar .button {behavior: url({$this->scriptPath}sobutton.htc);}
				so\:toolbar .switch {behavior: url({$this->scriptPath}soswitch.htc);}
				</style>
				<input type="hidden" name="{$this->name}" value="$encHTML">
				<table 	id="soEditorTable" 
						cellspacing="0" 
						style="width:{$this->width};background:#d4d0c8;border:1 outset #d4d0c8;">
				<tr>
  					<td colspan="2">
					<so:toolbar id="sotb_standard" style="width:1px;">
  					<span id="soToolBar">
					<table cellspacing="0"><tr>
						<td><img align="absmiddle" src="{$this->scriptPath}icons/toolbar.gif" width="5" height="20" border="0" alt=""/></td>
						<td nowrap="true" class="button" title="new" id="btnNew" action="soEditor.newDocument();"><img align="absmiddle" src="{$this->scriptPath}icons/newdoc.gif" width="16" height="15" alt="Clear Document"></td>
						<td nowrap="true" class="button" title="save" id="btnSave" action="soEditor.saveDoc();"><img align="absmiddle" src="{$this->scriptPath}icons/save.gif" width="16" height="15" alt="Save"/></td>
						<td><img align="absmiddle" src="{$this->scriptPath}icons/separator.gif" width="3" height="20" border="0" alt=""/></td>
						<td nowrap="true" class="button" title="cut" id="btnCut" action="soEditor.execCmd(5003);"><img align="absmiddle" src="{$this->scriptPath}icons/cut.gif" width="16" height="15" ALT="Cut"/></td>
						<td nowrap="true" class="button" title="copy" id="btnCopy" action="soEditor.execCmd(5002);"><img align="absmiddle" src="{$this->scriptPath}icons/copy.gif" width="16" height="15" ALT="Copy"/></td>    
						<td nowrap="true" class="button" title="paste" id="btnPaste" action="soEditor.execCmd(5032);"><img align="absmiddle" src="{$this->scriptPath}icons/paste.gif" width="16" height="15" ALT="Paste"/></td>
						<td nowrap="true" class="button" title="delete" id="btnDelete" action="soEditor.execCmd(5004);"><img align="absmiddle" src="{$this->scriptPath}icons/delete.gif" width="16" height="15" ALT="Delete"/></td>
						<td nowrap="true" class="button" title="find" id="btnFind" action="soEditor.execCmd(5008,1);"><img align="absmiddle" src="{$this->scriptPath}icons/find.gif" width="16" height="15" ALT="Find"/></td>
						<td><img align="absmiddle" src="{$this->scriptPath}icons/separator.gif" width="3" height="20" border="0" alt=""/></td>
						<td nowrap="true" class="button" title="undo" id="btnUndo" action="soEditor.execCmd(5049);"><img align="absmiddle" src="{$this->scriptPath}icons/undo.gif" width="16" height="15" ALT="Undo"/></td>
						<td nowrap="true" class="button" title="redo" id="btnRedo" action="soEditor.execCmd(5033);"><img align="absmiddle" src="{$this->scriptPath}icons/redo.gif" width="16" height="15" ALT="Redo"/></td>
						<td><img align="absmiddle" src="{$this->scriptPath}icons/separator.gif" width="3" height="20" border="0" alt=""/></td>
						<td nowrap="true" class="button" title="horizontal rule" id="btnHR" action="soEditor.insertText('<hr/>','',true,true);"><img align="absmiddle" src="{$this->scriptPath}icons/hr.gif" width="16" height="15" ALT="Rule"/></td>
						
					   <td nowrap="true" class="button" title="image" id="btnImage" action="soEditor.image();"><img align="absmiddle" src="{$this->scriptPath}icons/image.gif" width="16" height="15" ALT="Image/Asset"/></td>
					   
					   <td nowrap="true" class="button" title="hyperlink" id="btnLink" action="soEditor.insertLink();"><img align="absmiddle" src="{$this->scriptPath}icons/link.gif" width="16" height="15" ALT="Link"/></td>
					   
					   <td nowrap="true" class="button" title="remove hyperlink" id="btnUnLink" action="soEditor.execCmd(5050,0,null);"><img align="absmiddle" src="{$this->scriptPath}icons/unlink.gif" width="16" height="15" ALT="Unlink"/></td>
					   
					   <td><img align="absmiddle" src="{$this->scriptPath}icons/separator.gif" width="3" height="20" border="0" alt=""/></td>
						<td width="2"></td>
					  </tr></table>
					  </span>
					  </so:toolbar>
					  
					  <so:toolbar id="sotb_paragraph" style="width:1px;">
  <span id="soToolBar">
  <table cellspacing="0"><tr>
    <td><img align="absmiddle" src="{$this->scriptPath}icons/toolbar.gif" width="5" height="20" border="0" alt=""/></td>
      <td nowrap="true" class="switch" title="align left" id="btnAlign" action="soEditor.execCmd(5025);"><img align="absmiddle" src="{$this->scriptPath}icons/left.gif" width="16" height="15"/></td>
      <td nowrap="true" class="switch" title="align center" id="btnAlign" action="soEditor.execCmd(5024);"><img align="absmiddle" src="{$this->scriptPath}icons/center.gif" width="16" height="15"/></td>
      <td nowrap="true" class="switch" title="align right" id="btnAlign" action="soEditor.execCmd(5026);"><img align="absmiddle" src="{$this->scriptPath}icons/right.gif" width="16" height="15"/></td>	  
   	  <td><img align="absmiddle" src="{$this->scriptPath}icons/separator.gif" width="3" height="20" border="0" alt=""/></td>
      <td nowrap="true" class="switch" title="numbered list" id="btnList" action="soEditor.execCmd(5030);"><img align="absmiddle" src="{$this->scriptPath}icons/numlist.gif" width="16" height="15"/></td>    
      <td nowrap="true" class="switch" title="bulleted list" id="btnList" action="soEditor.execCmd(5051);"><img align="absmiddle" src="{$this->scriptPath}icons/bullist.gif" width="16" height="15"/></td>    
<td nowrap="true" class="button" title="undent" id="btnUnIndent" action="soEditor.execCmd(5031);"><img align="absmiddle" src="{$this->scriptPath}icons/deindent.gif" width="16" height="15"/></td>
    <td nowrap="true" class="button" title="indent" id="btnIndent" action="soEditor.execCmd(5018);"><img align="absmiddle" src="{$this->scriptPath}icons/indent.gif" width="16" height="15"/></td>
    <td width="2"></td>
  </tr></table>
  </span>
  </so:toolbar>
  
  <so:toolbar id="sotb_format" style="width:1px;">
  <span id="soToolBar">
  <table cellspacing="0"><tr>
    <td><img align="absmiddle" src="{$this->scriptPath}icons/toolbar.gif" width="5" height="20" border="0" alt=""/></td>
    <td nowrap="true" class="button" title="font properties" id="btnFontCtrl" action="soEditor.execCmd(5009);"><img align="absmiddle" src="{$this->scriptPath}icons/font.gif" width="16" height="15"/></td>
    <td title="format"><select id="btnFormat" onchange="soEditor.blockFormat(this.options[this.selectedIndex].value);">{$formatOptions}</select>
    <td title="format"><select id="btnFont" onchange="soEditor.execCmd(5044,0,this.options[this.selectedIndex].value);">{$fontOptions}</select>
    <td title="format"><select id="btnSize" onchange="soEditor.execCmd(5045,0,this.options[this.selectedIndex].value);">{$sizeOptions}</select>
    <td><img align="absmiddle" src="{$this->scriptPath}icons/separator.gif" width="3" height="20" border="0" alt=""/></td>
    <td nowrap="true" class="switch" title="bold" id="btnBold" action="soEditor.execCmd(5000);"><img align="absmiddle" src="{$this->scriptPath}icons/bold.gif" width="16" height="15"/></td>      
    <td nowrap="true" class="switch" title="italic" id="btnItalic" action="soEditor.execCmd(5023);"><img align="absmiddle" src="{$this->scriptPath}icons/italic.gif" width="16" height="15"/></td>      
    <td nowrap="true" class="switch" title="underline" id="btnUnder" action="soEditor.execCmd(5048);"><img align="absmiddle" src="{$this->scriptPath}icons/under.gif" width="16" height="15"/></td>          
    <td><img align="absmiddle" src="{$this->scriptPath}icons/separator.gif" width="3" height="20" border="0" alt=""/></td>
    <td nowrap="true" class="button" title="superscript" id="btnSuper" action="soEditor.insertText('<sup>','</sup>',true,false);"><img align="absmiddle" src="{$this->scriptPath}icons/superscript.gif" width="16" height="15" unselectable="on"/></td>      
    <td nowrap="true" class="button" title="subscript" id="btnSub" action="soEditor.insertText('<sub>','</sub>',true,false);"><img align="absmiddle" src="{$this->scriptPath}icons/subscript.gif" width="16" height="15"/></td>
    <td><img align="absmiddle" src="{$this->scriptPath}icons/separator.gif" width="3" height="20" border="0" alt=""/></td>
    <td nowrap="true" class="button" title="foreground color" id="btnFgColor" action="soEditor.colorPicker(5046);"><img align="absmiddle" src="{$this->scriptPath}icons/fgcolor.gif" width="16" height="15"/></td>    
    <td nowrap="true" class="button" title="background color" id="btnBgColor" action="soEditor.colorPicker(5042);"><img align="absmiddle" src="{$this->scriptPath}icons/bgcolor.gif" width="16" height="15"/></td>
    <td width="2"></td>
  </tr></table>
  </span>
  </so:toolbar>
  
  <so:toolbar id="sotb_table" style="width:1px;">
  <span id="soToolBar">
  <table cellspacing="0"><tr>
    <td><img align="absmiddle" src="{$this->scriptPath}icons/toolbar.gif" width="5" height="20" border="0" alt=""/></td>  
    <td nowrap="true" class="button" title="insert table" id="btnTable" action="soEditor.insertTable();"><img align="absmiddle" src="{$this->scriptPath}icons/instable.gif" width="16" height="15"/></td>    
    <td nowrap="true" class="button" title="insert cell" id="btnInsCell" action="soEditor.execCmd(5019);"><img align="absmiddle" src="{$this->scriptPath}icons/inscell.gif" width="16" height="15"/></td>
    <td nowrap="true" class="button" title="delete cell" id="btnDelCell" action="soEditor.execCmd(5005);"><img align="absmiddle" src="{$this->scriptPath}icons/delcell.gif" width="16" height="15"/></td>
    <td nowrap="true" class="button" title="insert row" id="btnInsRow" action="soEditor.execCmd(5021);"><img align="absmiddle" src="{$this->scriptPath}icons/insrow.gif" width="16" height="15"/></td>      
    <td nowrap="true" class="button" title="delete row" id="btnDelRow" action="soEditor.execCmd(5007);"><img align="absmiddle" src="{$this->scriptPath}icons/delrow.gif" width="16" height="15"/></td>      
    <td nowrap="true" class="button" title="insert column" id="btnInsColumn" action="soEditor.execCmd(5020);"><img align="absmiddle" src="{$this->scriptPath}icons/inscol.gif" width="16" height="15"/></td>      
    <td nowrap="true" class="button" title="delete column" id="btnDelColumn" action="soEditor.execCmd(5006);"><img align="absmiddle" src="{$this->scriptPath}icons/delcol.gif" width="16" height="15"/></td>      
    <td nowrap="true" class="button" title="split cell" id="btnSpltCell" action="soEditor.execCmd(5047);"><img align="absmiddle" src="{$this->scriptPath}icons/spltcell.gif" width="16" height="15"/></td>      
    <td nowrap="true" class="button" title="merge cells" id="btnMrgCell" action="soEditor.execCmd(5029);"><img align="absmiddle" src="{$this->scriptPath}icons/mrgcell.gif" width="16" height="15"/></td>      
    <td nowrap="true" class="button" title="cell properties" id="btnTDProp" action="soEditor.tdProperties();"><img align="absmiddle" src="{$this->scriptPath}icons/tdprop.gif" width="16" height="15"/></td>
  </tr></table>
  </span>
  </so:toolbar>
  
  </td>
</tr>
<tr>
  <td colspan="2">
    <so:editor scriptpath="{$this->scriptPath}" pageedit="{$this->pageEdit}" field="{$this->name}" form="$formName" id="soEditor"
			   baseURL="{$this->baseURL}" height="{$this->height}" width="100%" singlespaced="{$this->singleSpaced}" 
			   wordcount="{$this->wordCount}" validateonsave="false" validationmessage="Zig a Zig Ahhhhhh"/>
  </td>
</tr>
<tr>
  <td colspan="2">
  
    <table cellspacing="0" width="100%">
    <tr><td width="50">
	 <so:toolbar id="sotb_view" style="width:1px;">
    <span id="soToolBar">
    <table cellspacing="0"><tr>    
      <td><img align="absmiddle" src="{$this->scriptPath}icons/toolbar.gif" width="5" height="20" border="0" alt=""/></td>  
      <td nowrap="true" class="switch" title="toggle edit mode" id="btnMode" action="soEditor.SourceEdit = !soEditor.SourceEdit;"><img align="absmiddle" src="{$this->scriptPath}icons/viewsource.gif" width="16" height="15"/></td>
      <td nowrap="true" class="switch" title="toggle borders" id="btnBorders" action="soEditor.toggleBorders();"><img align="absmiddle" src="{$this->scriptPath}icons/borders.gif" width="15" height="15"/></td>
      <td nowrap="true" class="switch" title="toggle details" id="btnDetails" action="soEditor.toggleDetails();"><img align="absmiddle" src="{$this->scriptPath}icons/details.gif" width="16" height="15"/></td>
    </tr></table>
    </span>
    </so:toolbar>
	
	</td>
    <td onclick="soEditor.about();" style="text-align:center;font:8pt Tahoma;cursor:hand;"><img src="{$this->scriptPath}icons/soeditor_icon.gif" height="20" width="17" title="about" align="absmiddle">&nbsp; soEditor Lite 2.1</td>    
    <td style="text-align:right;width:100px;border-width: 1px;border-style: solid;border-color: threeddarkshadow white white threeddarkshadow;font:8pt Tahoma;">
      <nobr><span id="totalwords" style="width:95px;text-align:right;">^_^"<!--Add by James. 17/1/03 ---></span></nobr>
    </td>
    </tr>
    </table>
  </td>
</tr>
</table>
END;
return $retVal;					
		}		
}
?>
