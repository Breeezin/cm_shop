<FORM NAME="adminForm" METHOD="POST" ACTION="<?=$assetPath?>/Service/EmailInChinese/Do_Service/Yes">
	<P>
	<img src='images/EmailInChinese.png' /><br />
	<INPUT type='text' size='40' name='email' /><br />
<?php
	if( isset( $error ) && strlen( $error ) )
		echo '<font color=red>'.$error.'</font>';
?>
	<BR />
	<img src='images/QueryInChinese.png' /><BR />
	<TEXTAREA name='issue' cols=100 rows=24></TEXTAREA>
	</P>	
	<INPUT TYPE="hidden" name="DoAction" value="yes">
	<input type="image" src="/images/SubmitInChinese.png" alt="Submit Form" />
</FORM>	
