<FORM action="index.php?act=<?=$this->ATTRIBUTES['act']?>&DoAction=Yes" method="POST" >
<TABLE cellpadding="5" cellspacing="0">
<TR><TH align="right">Reference:<TH><TD><INPUT type="text" name="or_tracking_code" value="<?=$this->ATTRIBUTES['or_tracking_code']?>" size="50" maxlength="255"><TD></TR>

<TR><TD colspan="2"><INPUT type="submit" name="Submit" value="Submit"></TD></TR>
</TABLE>
<INPUT type="hidden" name="or_id" value="<?=$this->ATTRIBUTES['or_id']?>">
<INPUT type="hidden" name="BackURL" value="<?=$this->ATTRIBUTES['BackURL']?>">
<INPUT type="hidden" name="BreadCrumbs" value="<?=$this->ATTRIBUTES['BreadCrumbs']?>">
</FORM>