<?php if ($data['JavaScriptOnly']) { ?>
<SCRIPT LANGUAGE="Javascript">

function UpdateTwoState(name,allowChange,allowChangeUse) {
	if (allowChange) {
		if (allowChangeUse) {
			var next = {'1' : '0', '0' : '1'};
			var currentValue = document.forms.<?php print(ss_HTMLEditFormat($data['FormName'])); ?>[name].value;
			var nextValue    = next[currentValue];
			
			document.forms.<?php print(ss_HTMLEditFormat($data['FormName'])); ?>[name].value = nextValue;
			document.images['IMG'+name].src = '<?php print(ss_HTMLEditFormat($data['RelativeHere'])); ?>Images/threeState_' + nextValue + '.gif';
		} else {
			alert("Feature not enabled : You are currently unable to restrict the viewing of assets to a particular user group (e.g. for creating 'Members Only' areas). If you would like to enable this feature, please contact your website developer.");
		}
	} else {
		alert("Permission Denied : To change the permissions for this asset you must take ownership.");
	}
}
</SCRIPT>
<?php } else { ?>
<INPUT TYPE="HIDDEN" NAME="<?php print(ss_HTMLEditFormat($data['FieldName'])); ?>" VALUE="<?php print(ss_HTMLEditFormat($data['Value'])); ?>"><A HREF="javascript:UpdateTwoState('<?php print(ss_HTMLEditFormat($data['FieldName'])); ?>',<?php print(ss_HTMLEditFormat($data['AllowChange'])); ?>,<?php print(ss_HTMLEditFormat($data['AllowChangeUse'])); ?>)"><IMG ID="IMG<?php print(ss_HTMLEditFormat($data['FieldName'])); ?>" src="Images/threeState_<?php print(ss_HTMLEditFormat($data['Value'])); ?>.gif" BORDER="0"></A>
<?php } ?>
