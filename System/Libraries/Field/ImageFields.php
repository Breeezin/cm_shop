<?php
	class ImageField extends TextField {
		
		function display($verify = FALSE, $formName = NULL, $multi = TRUE, $class = NULL) {
			/* Display a text type field with a button on the end of it to 
			 * open the image manager for selection of an image, also output
			 * a javascript function to write the receieved value into the field */
			
			$fieldName = $verify ? $this->name.'_V'   : $this->name;
			$script = "<SCRIPT LANGUAGE='Javascript'>function save_$fieldName (newImagePath) {document.forms.$formName.$fieldName.value = newImagePath;}</SCRIPT>";
			$button = "<INPUT TYPE=\"BUTTON\" OnClick=\"window.open('" . ss_JSStringFormat("{$_SERVER['PHP_SELF']}?act=ImageManager.Selector&Directory={$this->Directory}&Save=save_$fieldName") . "', 'FooWin', 'width=540,height=300');void(0)\" VALUE=\"Browse\">";

			$value = ss_HTMLEditFormat($verify ? $this->verifyValue : $this->value);
			return "<INPUT TYPE=\"TEXT\" SIZE=\"{$this->size}\" NAME=\"$fieldName\" VALUE=\"$value\" MAXLENGTH=\"$this->maxLength\"> $button $script";
						
		}		
	}
	
	
	class PopupUniqueImageField extends TextField {
	
	var $preview = '64x64';
	
	function display($verify = FALSE, $formName = NULL, $multi = TRUE, $class = NULL) {
		global $cfg;

		$value = ss_HTMLEditFormat($verify ? $this->verifyValue : $this->value);
		$name  = $verify ? $this->name.'_V'   : $this->name;

		$noImage = "System/Libraries/Field/Images/noimage.gif";
		
		$imageDirectory = ss_withTrailingSlash($this->directory);
		if ($this->value != NULL and strlen($this->value)) {
			if ($this->preview != false) {
				$previewImage = "index.php?act=ImageManager.get&Image=".ss_URLEncodedFormat($imageDirectory.$this->value)."&Size=".$this->preview;
			} else {
				$previewImage = $imageDirectory.$this->value;
			}
		} else {
			$previewImage = $noImage;
		}

		if ($this->preview != false) {
			$previewSize = $this->preview;
		} else {
			$previewSize = 'None';
		}


		$gotExisting = strlen($this->value)?'':'none';
		
		$result = <<< EOD
			<INPUT TYPE="HIDDEN" NAME="{$name}" VALUE="$this->value">
			<INPUT TYPE="HIDDEN" NAME="{$name}_Action" VALUE="NoChange">
			<INPUT TYPE="HIDDEN" NAME="{$name}_Original" VALUE="$this->value">		
			<TABLE STYLE="height:64px;">
				<TR>
					<TD VALIGN="TOP" WIDTH="64">
						<IMG ID="{$name}_Preview" SRC="" STYLE="display:none;border:0px">
						<IMG ID="{$name}_PreviewOriginal" SRC="$previewImage" STYLE="border:0px">
					</TD>
				</TR>
				<TR>
					<TD VALIGN="TOP">
						<SPAN><A HREF="index.php?act=ImageManager.SimpleUpload&ReturnForm={$formName}&ReturnField={$name}&Preview={$previewSize}" TARGET="SimpleUpload_{$name}" ONCLICK="winhandle=window.open('','SimpleUpload_{$name}','width=300,height=175')">Upload</A></SPAN>
						<SPAN STYLE="display:$gotExisting" ID="{$name}_Delete">
							<A HREF="Javascript:void(0);" ONCLICK="{$name}_OnDelete();">Delete</A>
						</SPAN>
						<SPAN STYLE="display:none;" ID="{$name}_Revert">
							<A HREF="Javascript:void(0);" ONCLICK="{$name}_OnRevert();">Revert</A>
						</SPAN>
					</TD>
				</TR>
			</TABLE>
			<SCRIPT LANGUAGE="Javascript">
				function {$name}_OnDelete() {
					document.forms.{$formName}.{$name}_Action.value='Delete';
					document.forms.{$formName}.{$name}.value='';
					document.getElementById('{$name}_Revert').style.display='';
					document.getElementById('{$name}_Delete').style.display='none';
					document.getElementById('{$name}_Preview').style.display='';
					document.getElementById('{$name}_Preview').src='$noImage';
					document.getElementById('{$name}_PreviewOriginal').style.display='none';
				}
				function {$name}_OnRevert() {
					document.forms.{$formName}.{$name}.value='$this->value';
					document.forms.{$formName}.{$name}_Action.value='NoChange'; 
					document.getElementById('{$name}_Preview').style.display='none';
					document.getElementById('{$name}_PreviewOriginal').style.display='';
					document.getElementById('{$name}_Revert').style.display='none';
					if (document.forms.{$formName}.{$name}_Original.value.length)
						document.getElementById('{$name}_Delete').style.display='';
				}
			</SCRIPT>
		
EOD;
		
		
		return $result;
	}
	
	function validate() {
		
		if( !array_key_exists($this->name."_Action", $_REQUEST) )
			die;
		if ($_REQUEST[$this->name."_Action"] == 'Upload') {
			// We have a new image
			
			$oldFileName = expandPath("Custom/Cache/Incoming/{$this->value}");
			$newFileName = ss_withTrailingSlash(expandpath($this->directory)).$this->value;
			
			//ss_DumpVarDie($_REQUEST, $oldFileName.' to '.$newFileName, true);
			//print("oldName: $oldFileName vs newName: $newFileName ");
			if (file_exists($oldFileName)){
				rename($oldFileName, $newFileName);
				//print("Renamed ".);
			}
			//ss_deletefile($this->directory,$this->value);
			
			//print("old old file: ".$this->directory.$_REQUEST[$this->name."_Original"]);
			
			if (strlen($_REQUEST[$this->name."_Original"])) {
				ss_deleteFile(expandPath($this->directory),$_REQUEST[$this->name."_Original"]);
			}
		} else if ($_REQUEST[$this->name."_Action"] == 'Delete') {
			// Delete existing image	
			if (strlen($_REQUEST[$this->name."_Original"])) {
				ss_deleteFile(expandPath($this->directory),$_REQUEST[$this->name."_Original"]);
			}
		}
		
		return NULL;
	}
}

class UniqueImageField extends Field {
	function display($verify = FALSE, $formName = NULL, $multi = TRUE, $class = NULL) {
		global $cfg;
		
		$value = ss_HTMLEditFormat($verify ? $this->verifyValue : $this->value);
		$name  = $verify ? $this->name.'_V'   : $this->name;
		$viewLink = '';
		$imageDirectory = $this->iconDir;
	
		if (strlen($this->value)) {
			// get the size of the image, if it is biger than 350*350 then make a link rather than displaying the image
			//$size = getimagesize($this->directory.$this->value);
			//if ($size[0] > 350 || $size[1] > 350) {
				//$link = "<A HREF=\"\" OnClick=\"javascript:window.open('".ss_absolutePathToURL($this->directory).$this->value."');\">View</A>";
				$viewLink = ss_absolutePathToURL($this->directory).$this->value;
			//} else {
			//	$link = "<IMG NAME=\"{$this->name}\" SRC=\"".$cfg['currentServer'].ss_absolutePathToURL($this->directory).$this->value."\" ALT=\"{$this->value}\" BORDER=\"0\">";
			//}
		}
		$result = '';
		if ($this->preview) {
			$result .= "<IMG NAME=\"{$this->name}\" SRC=\"".$cfg['currentServer'].ss_absolutePathToURL($this->directory).$this->value."\" ALT=\"{$this->value}\" BORDER=\"0\">";
		}
		
		$result .="<INPUT TYPE=\"HIDDEN\" NAME=\"$name\" VALUE=\"$this->value\"><INPUT TYPE=\"HIDDEN\" NAME=\"{$name}_Action\" VALUE=\"$this->value\">";
		$result .= "<INPUT NAME=\"{$name}_UPLOAD\" TYPE=\"File\"  OnClick=\"this.form.{$this->name}_Action.value ='Upload'\">&nbsp;";
		if (strlen($viewLink)) {
			$result .= "<A HREF=\"javascript:void(0);\" onClick=\"window.open('$viewLink', 'imageView', 'width=320,height=240,scrollbars=yes');void(0)\"><IMG SRC=\"{$imageDirectory}images/b-view.gif\" BORDER=\"0\" ALT=\"View Image\"></A>&nbsp;";
		} else {
			$result .= "<IMG SRC=\"{$imageDirectory}images/b-view.gif\" BORDER=\"0\" ALT=\"No Image To View\">&nbsp;";
		}
		
		$result .= "<A HREF=\"javascript:void(0);\" onClick=\"this.form.{$name}_Action.value = 'Delete';void(0)\"><IMG SRC=\"{$imageDirectory}images/b-clear.gif\" ALT=\"Clear Image\" BORDER=\"0\"></A>";
/*		
	old display
		$result="<INPUT TYPE=\"HIDDEN\" NAME=\"$name\" VALUE=\"$this->value\">";
		$result=$result."<TABLE WIDTH=\"100%\" BORDER=\"0\" CELLPADDING=\"2\"><TR><TD>";
		$result=$result."<INPUT TYPE=\"Radio\" VALUE=\"Upload\" NAME=\"{$name}_Action\" ><INPUT NAME=\"{$name}_UPLOAD\" TYPE=\"File\"  OnClick=\"this.form.{$this->name}_Action[0].checked=true;\"></TD></TR>";
		$result=$result."<TR><TD><INPUT TYPE=\"Radio\" VALUE=\"Delete\" Name=\"{$name}_Action\"> : Delete</TD></TR><TR><TD><INPUT TYPE=\"Radio\" CHECKED VALUE=\"No Change\" OnClick=\"this.select()\"";
		$result=$result."Name=\"{$name}_Action\"> : No Change</TD></TR><TR><TD ROWSPAN=\"2\" VALIGN=\"TOP\">{$link}</TD></TR></TABLE>";
		return $result;
		//return "<INPUT TYPE=\"HIDDEN\" NAME=\"$name\" VALUE=\"$this->value\"><INPUT CLASS=\"{$this->class}\" TYPE=\"File\" SIZE=\"{$this->size}\" NAME=\"{$name}_UPLOAD\" MAXLENGTH=\"$this->maxLength\">$link";
*/
		return $result;
	}
	
	function validate() {
		
    	// contains full path to uploaded file in temprary storage
	    $upload_temp = $_FILES[$this->name."_UPLOAD"]['tmp_name'];
		//ss_log_message_r("file --- ", $_FILES);   
	    // get file name portion of source file	
		$allowedCharacters = 'abcdefghijklmnopqrstuvwxyz 0123456789-,.)(';
		
    	$temp = $_FILES[$this->name."_UPLOAD"]['name'];
    	$upload_file = '';
	    for ($i=0; $i < strlen($temp); $i++){
	    	$chr = substr($temp, $i, 1);
	    	$tempChr = strtolower($chr);
	    	if (strstr($allowedCharacters, $tempChr) !== false) {
	    		$upload_file .= $chr;
	    	}
	    }

	    // destination directory fAdministration :or uploaded file
    	$target_dir = $this->directory;
		
	    $secureName = md5(rand());
		// build target filename
    	$target_file = $target_dir.$secureName."_".$upload_file;
		
//		ss_log_message_r("request", $_REQUEST);
		if (array_key_exists($this->name."_Action", $_REQUEST)) {
			if ($_REQUEST[$this->name."_Action"] == 'Delete') {
				if (strlen($this->value)) { 
					if (file_exists($target_dir)) {						
						ss_deleteFile($target_dir, $this->value);	
					}
					$this->value = '';
				}

			} else if ($_REQUEST[$this->name."_Action"] == 'Upload') {

				if (strlen($upload_file) == 0) {
					return "Failed to copy file. Please try again later.";
/*				if ($imageSize[1] > $this->imageHeight OR $imageSize[0] > this->imageWidth) {	
					return "Image size is too big. Please reduce its size and try agina.";*/
				}
				
				
				
				if (!copy($upload_temp, $target_file)) {
    		    	return "Failed to copy \"".$upload_file."\" file. Please try again later.";
	    		} else {
					if(strlen($this->value)) ss_deleteFile($target_dir, $this->imageName);
					$this->value = $secureName."_".$upload_file;
				}
			}
		}
		return NULL;
	}
	function valueSQL() { 
		return $this->valueSQLText();
	}
}


?>
