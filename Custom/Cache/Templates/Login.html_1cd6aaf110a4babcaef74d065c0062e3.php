
<p><?php print($data['Errors']); ?></p>
<div class="form-group">
	<p>Please enter your email address and password below and click the login button. </p>		
	<P><?php print($data['Errors']); ?></P>				
	<form NAME="LoginForm" METHOD="POST" ACTION="<?php print($data['FormAction']); ?>">
		<div class="form-group">
			<label class="control-label" for="input-email"><?php print(ss_HTMLEditFormat($data['UserNameDesc'])); ?></label>
			<input type="text" name="Email" value="" placeholder="E-Mail Address" id="input-email" class="form-control">
		</div>
		<div class="form-group">
			<label class="control-label" for="input-password">Password</label>
			<input type="password" name="Password" value="" placeholder="Password" id="input-password" class="form-control">
		</div>
		<div class="form-group">
          <?php if ($data['ShowKeepMeLoggedIn']) { ?> 
          	<input style="border:0px;" <?php if ($data['KeepMeLoggedIn']) print('checked'); ?> type="CHECKBOX" value="1" name="KeepMeLoggedIn" onClick="if (this.checked && !confirm('Ticking this box will mean you will remain logged\n in on this computer indefinately, instead of your\n login expiring after a period of inactivity.\n\nYou should click OK only if you are the only person\nwho uses this computer, otherwise click CANCEL.')) { this.checked = false;}"> Keep me logged in
          <?php } ?>
		</div>
		<div class="form-group">
		  <input TYPE="hidden" NAME="DoAction" VALUE="Yes">
		  <input type="hidden" name="BackURL" value="<?php print(ss_HTMLEditFormat($data['BackURL'])); ?>">
		  <input type="hidden" name="UserNameDesc" value="<?php print(ss_HTMLEditFormat($data['UserNameDesc'])); ?>">
		<input TYPE="SUBMIT" VALUE="Login" NAME="SUBBY_THE_SUBMIT" class="btn btn-primary">
		</div>
	</form>
	<P>
	<a href="index.php?act=Security.ForgotPassword&BackURL=<?php print(ss_URLEncodedFormat($data['BackURL'])); ?>">Forgotten your password?</a>
	</P>
</div>

<script language="Javascript">
	document.forms.LoginForm.Email.focus();
</script>

