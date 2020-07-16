<table id="PanelBar<?php print(ss_HTMLEditFormat($data['Panel'])); ?>" width="100%" border="0" cellspacing="0" cellpadding="0" CLASS="panelBarClosed" onclick="toggleView(<?php print(ss_HTMLEditFormat($data['Panel'])); ?>,'sub');" style="cursor:hand;">
  <tr> 
    <td height="19" width="10">&nbsp;</td>
    <td height="19" align="left" valign="middle"><img id="PanelBarIcon<?php print(ss_HTMLEditFormat($data['Panel'])); ?>" src="Images/sub-icon.gif" width="9" height="11"><img src="Images/holder.gif" width="5" height="10"><span id="PanelSpan<?php print(ss_HTMLEditFormat($data['Panel'])); ?>" class="tabtext-off"><?php print(preg_replace("/\s/", "&nbsp;", $data['Name'])) ?></span>    </td>
    <td height="19" align="right" valign="middle" width="24"><img id="PanelBarArrow<?php print(ss_HTMLEditFormat($data['Panel'])); ?>" src="Images/arrow-dwn.gif" width="18" height="10" border="0"></td>
  </tr>
</table>
