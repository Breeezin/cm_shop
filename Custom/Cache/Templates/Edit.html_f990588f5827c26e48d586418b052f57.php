<table width="95%" border="0" align="center" cellpadding="0" cellspacing="0" class="bodytext">
 	<tr>
 		<td align="left" valign="top"><img src="Images/holder.gif" width="25" height="15">
    			<table width="100%" border="0" cellspacing="0" cellpadding="0">
    				<tr>
                    	<td><img src="Images/h-users.gif" width="172" height="26"></td>
				 </tr>
    				<tr>
                    	<td align="left" valign="top">
							<?php print("<p><a href=\"javascript:void(0);\" onclick=\"parent.openNamedNonAssetPanel('index.php?act=UsersAdministration.List','Manage Users','UsersManager');return false;\" class=\"bodytextBlue\">Click here to manage individual users</a> <img src=\"Images/go-arrow.gif\" width=\"9\" height=\"9\"></p><p>&nbsp;</p>"); ?>                    	
               		 </td>
				 </tr>
                	<tr>
                		<td><img src="Images/h-usergroups.gif" width="172" height="26"></td>
           		 </tr>
                	<tr>
                		<td align="left" valign="top">
							<?php print("<p><a href=\"javascript:void(0);\" onclick=\"parent.openNamedNonAssetPanel('index.php?act=UserGroupsAdministration.List','Manage User Groups','UserGroupsManager');return false;\" class=\"bodytextBlue\">Click here to edit the groups that your users can be sorted into.</a> <img src=\"Images/go-arrow.gif\" width=\"9\" height=\"9\"></p><p>&nbsp;</p>"); ?>
           			 </td>
           		 </tr>
                	<tr>
                		<td><img src="Images/h-import.gif" ></td>
           		 </tr>
                	<tr>
                		<td align="left" valign="top">
        			         <?php print("<p><a href=\"javascript:void(0);\" onclick=\"parent.openNamedNonAssetPanel('index.php?act=Import.UsersPrompt','Import Users','ImportUsers');return false;\" class=\"bodytextBlue\">Import a list of Users</a> <img src=\"Images/go-arrow.gif\" width=\"9\" height=\"9\"></p><p>&nbsp;</p>"); ?></td>
           			 </td>
           		 </tr>
				<tr>			
                		<td><img src="Images/h-export.gif" ></td>
           		 </tr>           		 
                	<tr>
                		<td align="left" valign="top">
        			         <p><a href="index.php?act=Export.Users" class="bodytextBlue">Export a list of Users</a> <img src="System/Classes/AssetTypes/UsersAsset/Templates/Images/go-arrow.gif" width="9" height="9"></p><p>&nbsp;</p></td>
           			 </td>
           		 </tr>
                	<tr>
                		<td><img src="Images/h-config.gif" width="172" height="26"></td>
           		 </tr>
                	<tr>
                		<td align="left" valign="top">
        			         <?php print("<p><a class=\"bodytextBlue\" href=\"javascript:var el=document.getElementById('DefineUserFields');if (el.style.display == '') { el.style.display='none'; } else { el.style.display=''; }void(0);\">Define User Fields</a> <img src=\"Images/go-arrow.gif\" width=\"9\" height=\"9\"></p><p>&nbsp;</p>"); ?></td>
           			 </td>
           		 </tr>
                	</table></td>
	</tr>
</table>
<div id="DefineUserFields" style="display:none;">
	<?php $data['FieldSet']->displayField('AST_USER_FIELDS'); ?>
</div>