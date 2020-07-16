<FORM name="adminForm" action="index.php?act=<?=$this->ATTRIBUTES['act']?>&DoAction=Yes" method="POST">
</p>
<TABLE>
<TR>
	<TH>Categories</TH>
	<?php 
		foreach ($attOptions as $desc => $uuid) {
			print "<th>$desc</th>";
		}
	?>
</TR>
<?php 
	foreach ($allCategoriesResult as $name => $aCat) {
		$tds = '';
		foreach ($attOptions as $desc => $uuid) {
			$checked = '';
			if (strstr($aCat['Ca'.$this->ATTRIBUTES['Type'].'Setting'], $uuid)) {
				$checked = 'checked';
			}
			$tds .= "<td align='center'><input style=\"border:0\" type=\"checkbox\" value=\"$uuid\" name=\"Att_{$aCat['ca_id']}[]\" $checked></td>";
		}
		print "<TR><TD>$name</TD>$tds<TR>";
	}
?>
</TABLE>
</p>
<INPUT TYPE="Submit" NAME="Submit" VALUE="Save">&nbsp;&nbsp;&nbsp;<INPUT TYPE="RESET" NAME="RESET" VALUE="Reset">&nbsp;

<INPUT TYPE="HIDDEN" NAME="as_id" VALUE="<?php print $this->assetLink ?>">
<INPUT TYPE="HIDDEN" NAME="act" VALUE="<?=$this->ATTRIBUTES['act']?>">
<INPUT TYPE="HIDDEN" NAME="Type" VALUE="<?=$this->ATTRIBUTES['Type']?>">
</FORM>