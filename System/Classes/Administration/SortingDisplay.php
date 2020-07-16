<?php
	
	// set the title for the page
	$this->display->title = $this->ATTRIBUTES['BreadCrumbs'] . ' : Ordering ' . $this->plural;
	
	// Get the URL to view this page again (useful for breadcrumbs and to return)
	$backURL = $_SESSION['BackStack']->getURL();
	
	// Set globals for the template
	$script_name = basename($_SERVER['SCRIPT_NAME']);
	$rfa = $backURL;
	$numRows = $result->numRows();
	$hasRows = $result->numRows() > 0;
	$breadCrumbs = $this->ATTRIBUTES['BreadCrumbs'].' : <A HREF="'.$backURL.'"> Ordering'.$this->plural.'</A>';
	
	$parentHiddenField = '';
	if (($this->parentTable != NULL) && array_key_exists($this->parentTable->linkField,$this->ATTRIBUTES)) {
		$parentHiddenField = "<INPUT TYPE=\"HIDDEN\" NAME=\"{$this->parentTable->linkField}\" VALUE=\"{$this->ATTRIBUTES[$this->parentTable->linkField]}\">";
	}

?>


<DIV ID="report"></DIV>
<FORM ACTION="<?php print $script_name?>" METHOD="POST" onsubmit="checkSort()" name='SortingForm'>
<TABLE WIDTH="100%" ALIGN="CENTER" BORDER="0" CELLSPACING="0" CELLPADDING="2">
	<TR>
		<td>
		<INPUT type="button" name="moveUp" onclick="moveRow(-1);" value="Move Up">&nbsp;&nbsp;&nbsp;
		<INPUT type="button" name="moveDown" onclick="moveRow(1);" value="Move Down">&nbsp;&nbsp;&nbsp;
		<INPUT type="button" name="moveTop" onclick="moveToTop(selectedRowIndex, true);" value="Move To Top">&nbsp;&nbsp;&nbsp;
		<INPUT type="button" name="moveBottom" onclick="moveToBottom(selectedRowIndex, true);" value="Move To Bottom">
		&nbsp;&nbsp;&nbsp;&nbsp;<input type='submit' name='submit' value='Submit'>
		</td>		
	</TR>
</TABLE>
	<INPUT TYPE="HIDDEN" NAME="act" VALUE="<?=$this->ATTRIBUTES['act']?>">
	<INPUT TYPE="HIDDEN" NAME="DoAction" VALUE="Yes">
	<INPUT NAME="BreadCrumbs" TYPE="HIDDEN" VALUE="<?php print ss_HTMLEditFormat($breadCrumbs) ?>">
	<INPUT TYPE="HIDDEN" NAME="BackURL" VALUE="<?=$this->ATTRIBUTES['BackURL']?>">
	<?php 
		print $parentHiddenField;
		if ($this->tableAssetLink !=null or $this->assetLink != null) {
			print('<INPUT TYPE="HIDDEN" NAME="as_id" VALUE="'.$this->assetLink.'">');					
		}
	?>
			
<!--- Display the fields from the table --->
<div style="overflow:auto;height:650;">
<?php

	print('<TABLE WIDTH="100%" ALIGN="CENTER" BORDER="0" CELLSPACING="0" CELLPADDING="2" ID="AdminListTable">');	
	// Print each row		

	// Titles
	if ($this->tableDisplayFieldTitles != NULL) {
		print("<TR>");
		
		foreach ($this->tableDisplayFieldTitles as $title) {
			print("<TD><STRONG>".ss_HTMLEditFormat($title)."</STRONG></TD>");
		}
		print("<TD>&nbsp;</TD></TR>"."\n");
	}
	
	// Initialise the loops
	$evenRow = TRUE;		
	$counter = 0;
	$index = 1;
	$i = 0;
	$jsIDs = '';
	while($row = $result->fetchRow()) { 

		// Start the row
		$rowClass = $evenRow ? 'AdminEvenRow' : 'AdminOddRow';
		$evenRow = !$evenRow;
		$jsIDs = ListAppend($jsIDs, $row[$this->tablePrimaryKey]);
		print("<TR onclick=\"selectRow(this,$index);\" ID=\"row{$row[$this->tablePrimaryKey]}\" >");
		// ID=\"row{$counter}\" ONMOUSEOVER=\"checkDragOver({$counter})\" ONMOUSEOUT=\"checkDragOut({$counter})\" 
		// <TD >&nbsp;<IMG ID=\"dragHandle{$row[$this->tablePrimaryKey]}\" SRC=\"System/Classes/Administration/move.gif\" ONMOUSEDOWN=\"return startDrag({$row[$this->tablePrimaryKey]})\"></TD>
		
		$breadCrumbs = $this->ATTRIBUTES['BreadCrumbs'].' : <A HREF="'.$backURL.'">';
		$comma = '';
		foreach ($this->tableDisplayFields as $displayField) {
			// Find the value for the field
						
			if ($this->tableTimeStamp == $displayField) {
				$value = formatDateTime($row[$displayField], "Y-m-d");
			} else {
				if (array_key_exists($displayField, $this->fields) AND is_object($this->fields[$displayField])) {
					
					$value = $this->fields[$displayField]->displayValue($row[$displayField]);
				} else {
					$value = $row[$displayField];
				}
			}

			// Add the field into the bread crumbs
			$breadCrumbs .= $comma;	$comma = ',';
			$breadCrumbs .= $value;

			// Print the display fields
			print("<TD ALIGN=\"LEFT\">$value</TD>");
		}
		$breadCrumbs .= '</A>';
		
		// End the Row
		print("<td>");
		print("<input type='Hidden' name='TableSort[$i]' value='{$row[$this->tablePrimaryKey]}'></td></TR>"."\n");
		$index++;
		$i++;
	}
	print('</TABLE>'."\n");

?>
</DIV>
<?php 
	if ($result->numRows() > 15) { 
?>
<TABLE WIDTH="80%" ALIGN="CENTER" BORDER="0" CELLSPACING="0" CELLPADDING="2">
	<TR>
		<td align="right">
		<INPUT type="button" name="moveUp" onclick="moveRow(-1);" value="Move Up">&nbsp;&nbsp;&nbsp;
		<INPUT type="button" name="moveDown" onclick="moveRow(1);" value="Move Down">&nbsp;&nbsp;&nbsp;
		<INPUT type="button" name="moveTop" onclick="moveToTop(selectedRowIndex, true);" value="Move To Top">&nbsp;&nbsp;&nbsp;
		<INPUT type="button" name="moveBottom" onclick="moveToBottom(selectedRowIndex, true);" value="Move To Bottom">
		&nbsp;&nbsp;&nbsp;&nbsp;<input type='submit' name='submit' value='Submit'>
		</td>		
	</TR>
</TABLE>
<?php 
	}
?>
</FORM>
<STYLE>
   .rowSelected {  background-color: yellow;}   
</STYLE>
<SCRIPT language="javascript">
 	var ids = new Array(<?=$jsIDs?>);
 	
 	var selectedRowIndex  = null;
 	 	
 	function dump(o) {
		var s = '';
		for (var prop in o) {
			s += prop + ' = ' + o[prop] + '\\n';
		}		
		alert(s);
	}
	function checkSort() {
		//document.forms.adminForm['{$name}[]'].item(i)
	/*	
		var theSort = document.SortingForm['TableSort[]'];
		if (!theSort.value){
			for(var i=0; i < theSort.length; i++) {
				alert(i + ' is '+theSort.item(i).value);
			}
			
		}
	*/	
	}
	function moveRow(direction) {
	  
      rowIndex = selectedRowIndex;
      var id_listTable = document.getElementById('AdminListTable');
                  
       var newIndex = rowIndex + direction; 
       var maxIndex = id_listTable.rows.length - 1;
       
       var moveOk = false;
       if ((newIndex > 0) && (newIndex <= maxIndex)) {
			moveOk = true;
			resetTable();
			swapRows(rowIndex, newIndex);
       } else if (newIndex >= maxIndex) {      	  	
       		resetTable();
       		newIndex = 1;
      	  	moveToTop(rowIndex, false);    
      	  	moveOk = true;
       } else if (newIndex == 0){
       		resetTable();
       		newIndex = maxIndex;
      	  	moveToBottom(rowIndex, false);
      	 	moveOk = true;
       }     	  

      if (moveOk) {     	  	 	      
	      id_listTable.rows[newIndex].bgColor = 'yellow';	      
	      selectedRowIndex = newIndex;	      
      } 
    }
    function moveToTop(rowIndex, reset) {
    	
    	var table = document.getElementById('AdminListTable');
    	var maxIndex = table.rows.length - 1;
	    if (rowIndex != 1) {	
	    	for(var startIndex = rowIndex; startIndex > 1; startIndex--) {
	    		swapRows(startIndex, startIndex-1);
	    	}
	    	if (reset) {
	    	  resetTable();	  
	    	  table.rows[1].bgColor = 'yellow';      		      	      
		      selectedRowIndex = 1;		      
	    	}
    	}
    }
    
    function moveToBottom(rowIndex, reset) {
    	
    	var table = document.getElementById('AdminListTable');
    	var maxIndex = table.rows.length - 1;
    	if (rowIndex != maxIndex) {
	    	for(var startIndex=rowIndex; startIndex < maxIndex; startIndex++) {
	    		swapRows(startIndex, startIndex+1);
	    	}
	    	if (reset) {
	    	  resetTable();	    	  
	    	  table.rows[maxIndex].bgColor = 'yellow';    	  	      
		      selectedRowIndex = maxIndex;		      
	    	}
    	}
    }
    function swapRows(from, to){
    	var listTable = document.getElementById('AdminListTable');
    	var temphtml = to + '';    	
		for(var i=0;  i < listTable.rows[from].cells.length-1; i++) {				
	    	var swapHTML = listTable.rows[to].cells[i].innerHTML;
	    	temphtml += ' :: '+swapHTML;
	        listTable.rows[to].cells[i].innerHTML = listTable.rows[from].cells[i].innerHTML;
	      	listTable.rows[from].cells[i].innerHTML = swapHTML;	      		      		     	      	
   		}    	 	
   		
		
		swapValue = document.SortingForm['TableSort['+ (to-1) +']'].value;
		document.SortingForm['TableSort['+ (to-1) +']'].value = document.SortingForm['TableSort['+ (from-1) +']'].value;
		document.SortingForm['TableSort['+ (from-1) +']'].value = swapValue;					
		
    }
    
	function resetTable () {
				
 		selectedRowIndex = null;
 		var theTable = document.getElementById('AdminListTable');
 		for(var i = 0; i < theTable.rows.length; i++) { 			
 			theTable.rows[i].bgColor = ''; 			
 		}
	}
 	function selectRow(rowObj,rowIndex) { 	 		
 		resetTable(); 		
 		rowObj.bgColor = 'yellow'; 		 		
 		selectedRowIndex = rowIndex;
 		
 		
 		//document.getElementById('row'+id).class = 
	}
</SCRIPT>