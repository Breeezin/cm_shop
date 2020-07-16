<script language="JavaScript">

window.focus();
function MM_callJS(jsStr) { //v2.0
	window.close();
}

</script>
<?php 
	
	$printForm = false;
	if (array_key_exists("DoAction", $this->ATTRIBUTES)) {
		if (!count($errors)) {
			print("<p>The information has been sent to your friends.<BR>Thank you for telling your friends.<br><p>");
			print("<input name=\"Button\" type=\"button\" onClick=\"MM_callJS('window.close();')\" value=\"Close Window\">");
			$printForm = true;
		} else {		
			if (count($errors)) {			
				$errorMessages = ''; 
				foreach ($errors as $message) {
					$errorMessages .= "<LI>$message</LI>";
				} 
				print('<P><TABLE WIDTH="95%" BORDER="0" ALIGN="CENTER"><TR><TD CLASS="entryErrors">Errors were detected in the data you entered, please correct the	following issues and re-submit. <UL>'.$errorMessages.'</UL></TD></TR></TABLE></P>');
			}
		}
	}

	if (!$printForm) {
	
		$message = ss_HTMLEditFormat($this->ATTRIBUTES['Message']);
		$toEmails = "";
		
		for($i=1; $i <= $asset->cereal['AST_TELLAFRIEND_LIMIT']; $i++) {
			ss_paramKey($this->ATTRIBUTES, "ToEmail$i", "");
			$value = $this->ATTRIBUTES["ToEmail$i"];
			$toEmails .= "<TR><TD WIDTH=\"15\">$i :</TD><TD><INPUT TYPE=\"TEXT\" NAME=\"ToEmail$i\" VALUE=\"$value\" SIZE=\"40\" STYLE=\"width:100%\"></TD></TR>";
		}	
		$assetPath = ss_withoutPreceedingSlash($asset->getPath());	
		print <<< EOD
		Please fill in the form below<br>
  <br>
  <SPAN><SPAN CLASS="requiredFlag">*</SPAN> denotes compulsory fields</SPAN><br>
  <br>
	<!--- output an enquiry form --->
		<FORM ACTION="{$cfg['currentServer']}$assetPath" method="POST">
		<TABLE CLASS="TellAFriend">			
			<TR>
				<TD CLASS="requiredFlag" VALIGN="TOP" WIDTH="5%">*</TD>
				<TH ALIGN="LEFT" VALIGN="TOP" WiDTH="35%">Your Name : </TH>
				<TD WIDTH="30%"><INPUT TYPE="TEXT" NAME="us_first_name" VALUE="{$this->ATTRIBUTES['us_first_name']}" STYLE="width:100%"><BR><SMALL>First</SMALL></TD>
				<TD WIDTH="30%"><INPUT TYPE="TEXT" NAME="us_last_name"  VALUE="{$this->ATTRIBUTES['us_last_name']}" STYLE="width:100%"><BR><SMALL>Last</SMALL></TD>
			</TR>
			<TR><TD CLASS="requiredFlag" VALIGN="TOP">*</TD><TH ALIGN="LEFT" VALIGN="TOP">Your Email Address : </TH>
				<TD COLSPAN="2"><INPUT TYPE="TEXT" NAME="us_email" VALUE="{$this->ATTRIBUTES['us_email']}" SIZE="40" STYLE="width:100%"></TD>
			</TR>
			<TR><TD CLASS="requiredFlag" VALIGN="TOP">*</TD><TH ALIGN="LEFT" VALIGN="TOP">Your Friends Email Addresses : </TH>
				<TD COLSPAN="2">
				<TABLE BORDER="0" WIDTH="100%">		
					$toEmails
				</TABLE>	
				</TD>
			</TR>
			<TR><TD CLASS="requiredFlag" VALIGN="TOP">*</TD><TH ALIGN="LEFT" VALIGN="TOP">Subject : </TH>
				<TD COLSPAN="2"><INPUT TYPE="TEXT" NAME="ToSubject" VALUE="{$this->ATTRIBUTES['ToSubject']}" SIZE="40" STYLE="width:100%"></TD>
			</TR>
			<TR><TD CLASS="requiredFlag" VALIGN="TOP">*</TD>
				<TH ALIGN="LEFT" VALIGN="TOP">Message :</TH>
				<TD COLSPAN="2">
				</TD>
			</TR>
			<TR><TD CLASS="requiredFlag" VALIGN="TOP"></TD><TD COLSPAN="3">
			I just thought I'd let you know about {$this->ATTRIBUTES['BeforeText']} <A HREF="{$this->ATTRIBUTES['TellingAbout']}" TARGET="_blank">{$this->ATTRIBUTES['LinkText']}</A> {$this->ATTRIBUTES['AfterText']} at <a href="{$cfg['currentSite']}" TARGET="_blank">{$cfg['website_name']}</a>.<BR>
			<TEXTAREA NAME="Message" ROWS="5" STYLE="width:100%">$message</TEXTAREA>
			</TD></TR>
			<TR>
				<TD COLSPAN="4" ALIGN="CENTER">
					<INPUT TYPE="SUBMIT" VALUE="Send" CLASS="buttons">
				</TD>
			</TR>
			<INPUT TYPE="HIDDEN" NAME="DoAction" VALUE="Yes">	
			<INPUT TYPE="HIDDEN" NAME="TellingAbout" VALUE="{$this->ATTRIBUTES['TellingAbout']}">
			
		</TABLE>
		</FORM>
EOD;
		
		
	}
	
?>
