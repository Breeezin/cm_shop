<div class="form-group">
	<p>Please enter your email address and password below and click the login button.</p>		
	<P><?php print($data['Errors']); ?></P>				
	<form NAME="LoginForm" METHOD="POST" ACTION="<?php print($data['FormAction']); ?>">
		<div class="form-group">
			<label class="control-label" for="input-email">E-Mail Address</label>
			<input type="text" name="Email" value="" placeholder="E-Mail Address" id="input-email" class="form-control">
		</div>
		<div class="form-group">
			<label class="control-label" for="input-password">Password</label>
			<input type="password" name="Password" value="" placeholder="Password" id="input-password" class="form-control">
		</div>
		<input TYPE="HIDDEN" NAME="DoAction" VALUE="Yes">
		<input TYPE="SUBMIT" VALUE="Login" NAME="SUBBY_THE_SUBMIT" class="btn btn-primary">
	</form>
	<a href="index.php?act=Security.ForgotPassword&BackURL=<?php print(ss_URLEncodedFormat($data['BackURL'])); ?>">Forgotten your password?</a>
</div>


