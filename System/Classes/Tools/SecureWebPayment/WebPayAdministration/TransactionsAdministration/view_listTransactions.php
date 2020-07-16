<?php
	
	// set the title for the page
	$this->display->title = $this->ATTRIBUTES['BreadCrumbs'];
	
	// Get the URL to view this page again (useful for breadcrumbs and to return)
	$backURL = $_SESSION['BackStack']->getURL();
	
	// Set globals for the template
	$script_name = basename($_SERVER['SCRIPT_NAME']);
	$rfa = $backURL;
	$numRows = $result->numRows();
	$hasRows = $result->numRows() > 0;
	
	$breadCrumbs = $this->ATTRIBUTES['BreadCrumbs'];
	
	$displayFieldTitles = array("Reference","Total", "Transaction", "View", "Delete");
	
	$data = array();
	$data['Script_Name'] = $script_name;
	$data['act'] = $_REQUEST['act'];
	$data['BreadCrumbs'] = $breadCrumbs;
	$data['RFA'] = $rfa;
	$data['Singular'] = 'Transacton';
	$data['SearchKeyword'] = $this->ATTRIBUTES['SearchKeyword'];
	$data['RowsPerPage'] = $this->ATTRIBUTES['RowsPerPage'];
	
	$aConfiguration = $Q_Configuration->fetchRow();	
	$data['CurrencySymbol'] = $aConfiguration['cn_currency_symbol'];
	$data['CurrencyCode'] = $aConfiguration['cn_currency_code'];
	$data['Status'] = $Q_Status;
	$data['Methods'] = $Q_Methods;
	$data['CountMethod'] = $Q_Methods->numRows();
	
	$data['Select'] = 'Selected';
	$data['TrStatus'] = $this->ATTRIBUTES['Status'];
	$data['TrMethod'] = $this->ATTRIBUTES['Method'];
	//$data['Processors'] = $Q_Processors;
	/*
	$data['MethodOptions'] = "";
	
	while($aProcessor = $Q_Processors->fetchRow()) {
		$data['MethodOptions'] .= "<option value='".$aProcessor['wpp_name']."'>".$aProcessor['wpp_display_name']."</option>";
	}*/
	
	print $this->processTemplate("Head_List",$data);
	
	if ($hasRows) {
		
		print('<TABLE WIDTH="100%" ALIGN="CENTER" BORDER="0" CELLSPACING="0" CELLPADDING="2">');
		// Print each row		

		// Titles
		print("<TR>");
		print("<TD><STRONG>Reference</STRONG></TD>");
		print("<TD><STRONG>Total</STRONG></TD>");
		print("<TD><STRONG>Charged In</STRONG></TD>");
		print("<TD><STRONG>Method</STRONG></TD>");
		print("<TD ALIGN=\"RIGHT\" VALIGN=\"BOTTOM\"><STRONG>Transaction Status</STRONG></TD>");
		print("<TD ALIGN=\"RIGHT\" VALIGN=\"BOTTOM\"><STRONG>View</STRONG></TD>");
		print("<TD ALIGN=\"RIGHT\" VALIGN=\"BOTTOM\"><STRONG>Delete</STRONG></TD>");
		print("<TD>&nbsp;</TD></TR>");
	
		
		// Initialise the loops
		$evenRow = TRUE;
		$startRow = ($this->ATTRIBUTES['CurrentPage']-1)*$this->ATTRIBUTES['RowsPerPage'];
		$currentRow = $startRow;
	  $temp = 0;
		while(($row = $result->fetchRow()) && ($currentRow < $startRow+$this->ATTRIBUTES['RowsPerPage'])) { 
      if($temp == $currentRow) {
				// Start the row
				$rowClass = $evenRow ? 'AdminEvenRow' : 'AdminOddRow';
				$evenRow = !$evenRow;
				print("<TR CLASS=\"$rowClass\">");
				
				$breadCrumbs ='<A HREF="'.$backURL.'">'.$this->ATTRIBUTES['BreadCrumbs'].'</A> : ';
				//$breadCrumbs = $this->ATTRIBUTES['BreadCrumbs'].' : <A HREF="'.$backURL.'">';
				$comma = '';
				
				
				//$value = ss_HTMLEditFormat($row['tr_currency_symbol'].$row[$displayField].$row['tr_currency_code']);
				
				$value = ss_HTMLEditFormat($row['tr_reference']);
					
				// Add the field into the bread crumbs
				$breadCrumbs .= $comma;	$comma = ',';
				$breadCrumbs .= $value;
	
					// Print the display fields				
				print("<TD ALIGN=\"LEFT\">$value</TD>\n");				
				//$breadCrumbs .= '</A>';
				
	
				$value = ss_HTMLEditFormat($row['tr_currency_symbol'].$row['tr_total']." ".$row['tr_currency_code']);
					
				// Add the field into the bread crumbs
				$breadCrumbs .= $comma;	$comma = ',';
				$breadCrumbs .= $value;
	
					// Print the display fields				
				print("<TD ALIGN=\"LEFT\">$value</TD>\n");				
				//$breadCrumbs .= '</A>';
				$value = ss_HTMLEditFormat($row['TrChargedTotal']);
				
				// Print the display fields				
				print("<TD ALIGN=\"LEFT\">$value</TD>\n");	
				
				
				
				
				if ($row['tr_payment_method'] != "Cheque" or $row['tr_payment_method'] != "Direct") {
					$value = 'Credit';
				} else {
					$value = $row['tr_payment_method'];
				}
					
				// Add the field into the bread crumbs
				$breadCrumbs .= $comma;	$comma = ',';
				$breadCrumbs .= $value;
	
					// Print the display fields				
				print("<TD ALIGN=\"LEFT\">$value</TD>\n");				
				//$breadCrumbs .= '</A>';
				
				
				$processLink = "";
		
				if ($row['tr_status_link'] == 1) {								
					$fuseaction = $aProcessor['wpp_name'].".Process&tr_id=".$row['tr_id'];
					$processLink = "<a href=\"{$script_name}?act={$fuseaction}&BreadCrumbs=".ss_URLEncodedFormat($breadCrumbs)."&RFA=".ss_URLEncodedFormat($rfa)."\">{$row['trs_name']}</a>";				
				} else {
					$processLink = $row['trs_name'];
				}
				
				print("<TD ALIGN=\"RIGHT\" VALIGN=\"BOTTOM\">".$processLink."</TD>\n");
				
				$displayLink = 'Deleted';
				$deleteLink = '&nbsp;';
				if (strlen($row['tr_payment_details_szln']) > 0) {
					$fuseaction = $aProcessor['wpp_name'].".Display&tr_id=".$row['tr_id'];
					$displayLink = "<a href=\"{$script_name}?act={$fuseaction}&BreadCrumbs=".ss_URLEncodedFormat($breadCrumbs)."&RFA=".ss_URLEncodedFormat($rfa)."&Layout=Administration\">View</a>";
					$fuseaction = $aProcessor['wpp_name'].".Delete&tr_id=".$row['tr_id'];
					$deleteLink = "<a href=\"{$script_name}?act={$fuseaction}&BreadCrumbs=".ss_URLEncodedFormat($breadCrumbs)."&RFA=".ss_URLEncodedFormat($rfa)."\">Delete</a>";
					
				} 
				
				print("<TD ALIGN=\"RIGHT\" VALIGN=\"BOTTOM\">".$displayLink."</TD>\n");
				print("<TD ALIGN=\"RIGHT\" VALIGN=\"BOTTOM\">".$deleteLink."</TD>\n");
				/*
				if (strlen($deleteLink)) {
					print("<TD ALIGN=\"RIGHT\" VALIGN=\"BOTTOM\">".$deleteLink."<TD>\n");
				} else {
					print("<TD ALIGN=\"RIGHT\" VALIGN=\"BOTTOM\">&nsbp;<TD>\n");
				}*/
					// Print the manage cell
						
					/*
					print("<OPTION VALUE=\"{$script_name}?act=".$row['wpp_name'].".Display&tr_id=".$row['tr_id']."&BreadCrumbs=".ss_URLEncodedFormat($breadCrumbs)."&RFA=".ss_URLEncodedFormat($rfa)."\">View Detail</OPTION>");
	
					// Print the delete option
					print("<OPTION STYLE=\"background-color:red; color:white\" VALUE=\"javascript:confirmDelete('{$script_name}?act=WebPay.Delete&RFA=".ss_URLEncodedFormat($rfa)."')\">Delete</OPTION>");
				
				// End the manage cell
				print('</SELECT></TD>');	*/		
				
				// End the Row
				print("</TR>");
				$currentRow++;
			}
			$temp++;
			
		}
		print('</TABLE>');
	}
	
	print("<DIV ALIGN=\"CENTER\">$pageThru->display</DIV>");
	$data_tail = array();	
	print $this->processTemplate("Tail_List",$data_tail);

?>

