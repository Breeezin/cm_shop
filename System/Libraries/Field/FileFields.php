<?php

// ############# Filed Field ############# //
class FileField extends Field {
	
	var $secure = false;
	
	function display($verify = FALSE, $formName = NULL, $multi = TRUE, $class = NULL) {
		$value = ss_HTMLEditFormat($verify ? $this->verifyValue : $this->value);
		$name  = $verify ? $this->name.'_V'   : $this->name;
		$link = '';
		$isDelete = '';
		
		if (strlen($this->value)) {
			$filepath = ss_absolutePathToURL(ss_withTrailingSlash($this->directory)).$this->value;
			$size = filesize(expandPath($filepath));
			//$mb = (int) ($size/(1024*1024));
			$mb = $size/(1024*1024);			
			$bytes = ($size - ($mb * 1024 * 1024));
			
			$link = "<A HREF=\"javascript:window.open('".$filepath."');void(0);\">".ListLast($this->value, '/')." Download (".number_format(($bytes+$mb), 2, '.', '')." MB)</A>&nbsp;";
			$link .= "<INPUT TYPE=\"Checkbox\" VALUE=\"1\"  style='border:0' NAME=\"{$name}_DELETE\">Delete";
		}	
		return "<INPUT TYPE=\"HIDDEN\" NAME=\"$name\" VALUE=\"$this->value\"><INPUT CLASS=\"{$this->class}\" TYPE=\"File\" SIZE=\"{$this->size}\" NAME=\"{$name}_UPLOAD\" MAXLENGTH=\"$this->maxLength\"><br>$link";
	}
	
	function validate() {

		if (array_key_exists($this->name."_UPLOAD",$_FILES)) {
	    	// contains full path to uploaded file in temprary storage
			
		    $upload_temp = $_FILES[$this->name."_UPLOAD"]['tmp_name'];
			//ss_log_message_r("file --- ", $_FILES);   
		    // get file name portion of source file
	    	$upload_file = $_FILES[$this->name."_UPLOAD"]['name'];
			
		    // destination directory fAdministration :or uploaded file
	    	$target_dir = ss_withTrailingSlash($this->directory);
		    
	    	if ($this->secure) {
				$secureName = md5(rand())."/";
	    	} else {
				$secureName = '';
	    	}
			// build target filename
	    	$target_file = $target_dir.$secureName.$upload_file;
			
			if (strlen($upload_file) > 0) {
				
				if (strlen($this->value)) { 
					
					// Check if its in a sub folder (i.e. secure)
					// ass_u_me only one level deep
					if (ListLen($this->value,"/") > 1) {
						ss_deleteFile($target_dir.ListFirst($this->value,"/"), ListLast($this->value,"/"));	
						rmdir($target_dir.ListFirst($this->value,"/"));
					} else {
						ss_deleteFile($target_dir, $this->value);	
					}
					$this->value = '';
				}
	
				if ($this->secure) {
					mkdir($target_dir.$secureName,0775);
				}
				
				if (!copy($upload_temp, $target_file)) {
	    	    	return "Failed to upload file. Please try again later.";
		    	} else {
					$this->value = $secureName.$upload_file;
				}
			} else {
				
				ss_paramKey($this->fieldSet->ATTRIBUTES, $this->name.'_DELETE', '');
				
				if ($this->fieldSet->ATTRIBUTES[$this->name.'_DELETE'] == 1) {
					//ss_DumpVarDie($this->value, $target_dir.ListFirst($this->value,"/"), ListLast($this->value,"/"));				
					ss_deleteFile($target_dir.ListFirst($this->value,"/"), ListLast($this->value,"/"));				
					if ($this->secure) {
						rmdir($target_dir.ListFirst($this->value,"/"));
					}
					$this->value = null;
					
				}
				
			}
		}
	    // try to copy file to real upload directory
	   	//print("after coppy ".$this->directory);
		
		return NULL;
	}
	function valueSQL() { 
		return $this->valueSQLText();
	}
}


// ############# Filed Field ############# //
class FlashFileField extends Field {
	
	var $secure = false;
	
	function valueSQL() {			
		//ss_DumpVarDie($this->value);			
		return "'".serialize($this->value)."'";			
	}
	
	function display($verify = FALSE, $formName = NULL, $multi = TRUE, $class = NULL) {
		$value = $this->value;
		$name  = $verify ? $this->name.'_V'   : $this->name;
		$link = '';
		$isDelete = '';
		
		$serializedValue = $value;
		
		if (!is_array($value)) {		
			if (!strlen($value)) {
				$value = array();											
			} else {																		
				$value = unserialize(html_entity_decode($value)); 				
				if ($value == null) {
					$value = array();					
				}				
			}
		}
		ss_paramKey($value, 'file', '');
		ss_paramKey($value, 'width', '');
		ss_paramKey($value, 'height', '');
		
		$serializedValue = serialize($value);
		
		$serializedValue = ss_HTMLEditFormat($serializedValue);		
		
		
		
		if (strlen($value['file'])) {							
			$link = "<TD>File :</TD><TD colspan=\"3\"><A HREF=\"javascript:window.open('".ss_absolutePathToURL($this->directory).'/'.$value['file']."','','height={$value['height']},width={$value['width']}');void(0);\">{$value['file']}</A>&nbsp;";
			$link .= "<INPUT TYPE=\"Checkbox\" VALUE=\"1\" NAME=\"{$name}_DELETE\" style='border:0'>Delete</TD>";
		}	
		$displayHTML = <<< EOD
		<TABLE cellpadding="0" cellspacing="0">
		<TR>			
			{$link}
		</TR>
		<TR>			
			<TD>Upload :</TD>
			<TD colspan="3"><INPUT TYPE="HIDDEN" NAME="$name" VALUE="$serializedValue"><INPUT TYPE="File" SIZE="{$this->size}" NAME="{$name}_UPLOAD" ></TD>	
			
		</TR>
		<TR>
			<TD>Width :</TD>
			<TD><INPUT name="{$name}_Width" value="{$value['width']}" type="text" size='3'></TD>
			<TD>Height :</TD>
			<TD><INPUT name="{$name}_Height" value="{$value['height']}" type="text" size='3'></TD>	
		</TR>
		</TABLE>
EOD;
		
		return $displayHTML;
	}
	
	
	function displayValue($value) {
		
	}
	
	function validate() {
		
		if (array_key_exists($this->name."_UPLOAD",$_FILES)) {
			ss_paramKey($this->fieldSet->ATTRIBUTES, $this->name.'_DELETE', '');
			if (strlen($_FILES[$this->name."_UPLOAD"]['name']) or (strlen($this->value['file']) && $this->fieldSet->ATTRIBUTES[$this->name.'_DELETE'] != 1)) {
		    	// contains full path to uploaded file in temprary storage
		    	if (!strlen($this->value['width']) or !strlen($this->value['height'])) {
					return $this->displayName." requires the width and hieght.<BR>Please submit the file again.";			
				}
		    	if (!is_numeric($this->value['width']) or !is_numeric($this->value['height'])){
		    		return "The width and hieght of ".$this->displayName.' must be in numeric format.<BR>Please submit the file again.';			
		    	}
				
			}
			
		    $upload_temp = $_FILES[$this->name."_UPLOAD"]['tmp_name'];
			//ss_log_message_r("file --- ", $_FILES);   
		    // get file name portion of source file
	    	$upload_file = $_FILES[$this->name."_UPLOAD"]['name'];
			
		    // destination directory fAdministration :or uploaded file
	    	$target_dir = $this->directory.'/';
		    
	    	if ($this->secure) {
				$secureName = md5(rand())."/";
	    	} else {
				$secureName = '';
	    	}
			// build target filename
	    	$target_file = $target_dir.$secureName.$upload_file;
			
			if (strlen($upload_file) > 0) {			
				if (strlen($this->value['file'])) { 
					
					// Check if its in a sub folder (i.e. secure)
					// ass_u_me only one level deep
					if (ListLen($this->value['file'],"/") > 1) {
						ss_deleteFile($target_dir.ListFirst($this->value['file'],"/"), ListLast($this->value['file'],"/"));	
						rmdir($target_dir.ListFirst($this->value['file'],"/"));
					} else {
						ss_deleteFile($target_dir, $this->value['file']);	
					}
					$this->value['file'] = '';
				}
	
				if ($this->secure) {
					mkdir($target_dir.$secureName,0775);
				}
				
				if (!copy($upload_temp, $target_file)) {
	    	    	return "Failed to upload file. Please try again later.";
		    	} else {
					$this->value['file'] = $secureName.$upload_file;
					
				}
			} else {
					
				if ($this->fieldSet->ATTRIBUTES[$this->name.'_DELETE'] == 1) {
					//ss_DumpVarDie($this->value, $target_dir.ListFirst($this->value,"/"), ListLast($this->value,"/"));				
					ss_deleteFile($target_dir.ListFirst($this->value['file'],"/"), ListLast($this->value['file'],"/"));				
					if ($this->secure) {
						rmdir($target_dir.ListFirst($this->value['file'],"/"));
					}
					$this->value['file'] = '';				
				}
				
			}
		}
		
	    // try to copy file to real upload directory
	   	//print("after coppy ".$this->directory);
		
		return NULL;
	}
	
	function processFormInputValues() {

		$this->value = array();
		ss_paramKey($this->value, 'file','');
		ss_paramKey($this->value, 'width','');
		ss_paramKey($this->value, 'height','');
		
						
		ss_paramKey($this->fieldSet->ATTRIBUTES, $this->name.'_Width', '');
		ss_paramKey($this->fieldSet->ATTRIBUTES, $this->name.'_Height', '');
		ss_paramKey($this->fieldSet->ATTRIBUTES, $this->name);
		
		if (strlen($this->fieldSet->ATTRIBUTES[$this->name])) {
			$tempValue = unserialize($this->fieldSet->ATTRIBUTES[$this->name]);
		} else {
			$tempValue = array();
		}
		ss_paramKey($tempValue, 'file','');
		
		$this->value['width'] = $this->fieldSet->ATTRIBUTES[$this->name.'_Width'];
		$this->value['file'] = $tempValue['file'];
		$this->value['height'] = $this->fieldSet->ATTRIBUTES[$this->name.'_Height'];
		
	}
		
}

?>
