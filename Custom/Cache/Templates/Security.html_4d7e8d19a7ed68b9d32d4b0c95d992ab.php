	<?php $result = new Request("Asset.TwoState", array('JavaScriptOnly' => true, 'FormName' =>	'AssetForm'));	print($result->display); ?>
	<P>
  <TABLE CELLPADDING="5">
    <TR> 
      <TD colspan="2"> 
	<P>
	This asset is owned by <?php print(ss_HTMLEditFormat($data['us_first_name'])); ?> <?php print(ss_HTMLEditFormat($data['us_last_name'])); ?>.
	<!--- Allow admin@innovativemedia.co.nz to give the asset to - super user account --->
	<?php if ($data['as_owner_au_id'] != 0 && $data['IsDeployer']) { ?>
		<P>
			You may lock this asset so it may not be moved, renamed or deleted by 
			anyone
			<INPUT TYPE="SUBMIT" NAME="Save" VALUE="Lock This Asset" CLASS="adminButtons" OnClick="this.form.as_owner_au_id.value = 0;document.getElementById('SaveCompleted').innerHTML = 'Saving... please wait';">
		</P>
	<?php } elseif ($data['as_owner_au_id'] == 0 && $data['IsDeployer']) { ?>
		It has been locked.
		<P>
			You may unlock this asset, but moving, renaming or deleting it may break the 
			website. If in doubt, please check with a programmer first.
			<INPUT TYPE="SUBMIT" NAME="Save" VALUE="Unlock This Asset" CLASS="adminButtons" OnClick="this.form.as_owner_au_id.value = 1;document.getElementById('SaveCompleted').innerHTML = 'Saving... please wait';">
		</P>
	<?php } elseif ($data['as_owner_au_id'] != 0 && $data['as_owner_au_id'] != $data['User']) { ?>
		<INPUT NAME="Save" TYPE="SUBMIT" VALUE="Take Ownership" CLASS="adminButtons" OnClick="this.form.as_owner_au_id.value = <?php print(ss_HTMLEditFormat($data['User'])); ?>;document.getElementById('SaveCompleted').innerHTML = 'Saving... please wait';">
	<?php } ?>
	 
	</P>
	<P>
		The following permissions are assigned to users who are 
        members of these security groups and mailing lists (click a checkbox to 
        change it's state).
		</P>
		<P>
		<strong>Note: </strong>"Allow" type permissions have a higher priority than
		"Disallow" permissions when a user is a member of more than one applicable
		group/mailing list.
		</P>
		</TD>
    </TR>
    <TR> 
      <TD>
	  <TABLE BORDER="0" WIDTH="400">
          <TR> 
            <TD>Group/Mailing List</TD>
            <TD ALIGN="CENTER">Access</TD>
            <TD ALIGN="CENTER">Admin</TD>
            <TD ALIGN="CENTER">Child<br />Access</TD>
            <TD ALIGN="CENTER">Child<br />Admin</TD>
          </TR>
          
   	        <?php $id = 'Default';  ?>
	        <?php $Permissions['aug_can_use'] = $data['Asset']['as_can_use_default']; ?>
	        <?php $Permissions['aug_can_administer'] = $data['Asset']['as_can_admin_default']; ?>
	        <?php $Permissions['aug_child_can_use'] = $data['Asset']['as_child_can_use_default']; ?>
	        <?php $Permissions['aug_child_can_administer'] = $data['Asset']['as_child_can_admin_default']; ?>

            <TR> 
              <TD><strong>Default</strong><br />(new user groups will have these permissions assigned by default)</TD>
                <TD ALIGN="CENTER"><?php $result = new Request("Asset.TwoState", array('FieldName' =>	'Perm_CanUse_'.$id, 'FormName' =>	'AssetForm', 'Value' => $Permissions['aug_can_use'], 'AllowChange' => 'Yes', 'AllowChangeUse' => 'Yes', ));	print($result->display); ?></TD>
		     	<TD ALIGN="CENTER"><?php $result = new Request("Asset.TwoState", array('FieldName' =>	'Perm_CanAdminister_'.$id, 'FormName' =>	'AssetForm', 'Value' => $Permissions['aug_can_administer'], 'AllowChange' => 'Yes', 'AllowChangeUse' => 'Yes', ));	print($result->display); ?></TD>
                <TD ALIGN="CENTER"><?php $result = new Request("Asset.TwoState", array('FieldName' =>	'Perm_ChildCanUse_'.$id, 'FormName' =>	'AssetForm', 'Value' => $Permissions['aug_child_can_use'], 'AllowChange' => 'Yes', 'AllowChangeUse' => 'Yes', ));	print($result->display); ?></TD>
		     	<TD ALIGN="CENTER"><?php $result = new Request("Asset.TwoState", array('FieldName' =>	'Perm_ChildCanAdminister_'.$id, 'FormName' =>	'AssetForm', 'Value' => $Permissions['aug_child_can_administer'], 'AllowChange' => 'Yes', 'AllowChangeUse' => 'Yes', ));	print($result->display); ?></TD>
            </TR>
                      
          <?php $tmpl_loop_rows = $data['Q_UserGroups']->numRows(); $tmpl_loop_counter = 0; while ($row = $data['Q_UserGroups']->fetchRow()) { $tmpl_loop_counter++; ?>    
          	<?php $Permissions = array(); ?>
          	<?php $Q_PermSet = query("SELECT aug_can_use, aug_can_administer, aug_child_can_use, aug_child_can_administer FROM asset_user_groups WHERE aug_as_id =".$data['as_id']." AND aug_ug_id = ".$row['ug_id']); ?>

			<?php if ($Q_PermSet->numRows()) { ?>
    	    	<?php $aPermset = $Q_PermSet->fetchRow(); ?>
	        	<?php $Permissions['aug_can_use'] = $aPermset['aug_can_use']; ?>
	        	<?php $Permissions['aug_can_administer'] = $aPermset['aug_can_administer']; ?>
	        	<?php $Permissions['aug_child_can_use'] = $aPermset['aug_child_can_use']; ?>
	        	<?php $Permissions['aug_child_can_administer'] = $aPermset['aug_child_can_administer']; ?>
           	<?php } else { ?>             
                <?php $Permissions['aug_can_use'] = ''; ?>
	            <?php $Permissions['aug_can_administer'] = ''; ?>
                <?php $Permissions['aug_child_can_use'] = ''; ?>
	            <?php $Permissions['aug_child_can_administer'] = ''; ?>
	        <?php } ?>
	        <?php $id =$row['ug_id'];  ?>
          
            <TR> 
              <TH ALIGN="LEFT"><?php print(ss_HTMLEditFormat($row['ug_name'])); ?></TH>
                <TD ALIGN="CENTER"><?php $result = new Request("Asset.TwoState", array('FieldName' =>	'Perm_CanUse_'.$id, 'FormName' =>	'AssetForm', 'Value' => $Permissions['aug_can_use'], 'AllowChange' => 'Yes', 'AllowChangeUse' => 'Yes', ));	print($result->display); ?></TD>
		     	<TD ALIGN="CENTER"><?php $result = new Request("Asset.TwoState", array('FieldName' =>	'Perm_CanAdminister_'.$id, 'FormName' =>	'AssetForm', 'Value' => $Permissions['aug_can_administer'], 'AllowChange' => 'Yes', 'AllowChangeUse' => 'Yes', ));	print($result->display); ?></TD>
                <TD ALIGN="CENTER"><?php $result = new Request("Asset.TwoState", array('FieldName' =>	'Perm_ChildCanUse_'.$id, 'FormName' =>	'AssetForm', 'Value' => $Permissions['aug_child_can_use'], 'AllowChange' => 'Yes', 'AllowChangeUse' => 'Yes', ));	print($result->display); ?></TD>
		     	<TD ALIGN="CENTER"><?php $result = new Request("Asset.TwoState", array('FieldName' =>	'Perm_ChildCanAdminister_'.$id, 'FormName' =>	'AssetForm', 'Value' => $Permissions['aug_child_can_administer'], 'AllowChange' => 'Yes', 'AllowChangeUse' => 'Yes', ));	print($result->display); ?></TD>
            </TR>
         <?php } ?>
         
         <?php $tmpl_loop_rows = $data['Q_MailGroups']->numRows(); $tmpl_loop_counter = 0; while ($row = $data['Q_MailGroups']->fetchRow()) { $tmpl_loop_counter++; ?>    
          	<?php $Permissions = array(); ?>
          	<?php $Q_PermSet = query("SELECT aug_can_use, aug_can_administer, aug_child_can_use, aug_child_can_administer FROM asset_user_groups WHERE aug_as_id = ".$data['as_id']." AND aug_ug_id = ".$row['ug_id']); ?>

          	<?php if ($Q_PermSet->numRows()) { ?>
    	    	<?php $aPermset = $Q_PermSet->fetchRow(); ?>
	        	<?php $Permissions['aug_can_use'] = $aPermset['aug_can_use']; ?>
	        	<?php $Permissions['aug_can_administer'] = $aPermset['aug_can_administer']; ?>
	        	<?php $Permissions['aug_child_can_use'] = $aPermset['aug_child_can_use']; ?>
	        	<?php $Permissions['aug_child_can_administer'] = $aPermset['aug_child_can_administer']; ?>
           	<?php } else { ?>             
                <?php $Permissions['aug_can_use'] = ''; ?>
	            <?php $Permissions['aug_can_administer'] = ''; ?>
                <?php $Permissions['aug_child_can_use'] = ''; ?>
	            <?php $Permissions['aug_child_can_administer'] = ''; ?>
	        <?php } ?>
	        <?php $id =$row['ug_id'];  ?>
            <TR> 
              <TH ALIGN="LEFT"><?php print(ss_HTMLEditFormat($row['ug_name'])); ?> Subscribers</TH>
                <TD ALIGN="CENTER"><?php $result = new Request("Asset.TwoState", array('FieldName' =>	'Perm_CanUse_'.$id, 'FormName' =>	'AssetForm', 'Value' => $Permissions['aug_can_use'], 'AllowChange' => 'Yes', 'AllowChangeUse' => 'Yes', ));	print($result->display); ?></TD>
		     	<TD ALIGN="CENTER"><?php $result = new Request("Asset.TwoState", array('FieldName' =>	'Perm_CanAdminister_'.$id, 'FormName' =>	'AssetForm', 'Value' => $Permissions['aug_can_administer'], 'AllowChange' => 'Yes', 'AllowChangeUse' => 'Yes', ));	print($result->display); ?></TD>
                <TD ALIGN="CENTER"><?php $result = new Request("Asset.TwoState", array('FieldName' =>	'Perm_ChildCanUse_'.$id, 'FormName' =>	'AssetForm', 'Value' => $Permissions['aug_child_can_use'], 'AllowChange' => 'Yes', 'AllowChangeUse' => 'Yes', ));	print($result->display); ?></TD>
		     	<TD ALIGN="CENTER"><?php $result = new Request("Asset.TwoState", array('FieldName' =>	'Perm_ChildCanAdminister_'.$id, 'FormName' =>	'AssetForm', 'Value' => $Permissions['aug_child_can_administer'], 'AllowChange' => 'Yes', 'AllowChangeUse' => 'Yes', ));	print($result->display); ?></TD>
            </TR>
         <?php } ?>
        </TABLE></TD>
      <INPUT TYPE="HIDDEN" NAME="as_owner_au_id" VALUE="<?php print(ss_HTMLEditFormat($data['as_owner_au_id'])); ?>">  
      <TD VALIGN="TOP" NOWRAP STYLE="border:1px black solid"><strong>Key :</strong><br>
        <table border="0">
          <!--<tr> 
            <td><img src="Images/threeState_.gif" width="24" height="24"></td>
            <td valign="middle">Inherit</td>
          </tr>-->
          <tr> 
            <td><img src="Images/threeState_0.gif" width="24" height="24"></td>
            <td valign="middle"> Disallow</td>
          </tr>
          <tr> 
            <td><img src="Images/threeState_1.gif" width="24" height="24"></td>
            <td valign="middle">Allow</td>
          </tr>
        </table>
        <BR>
        <BR>
      </TD>
    </TR>
    <tr>
    	<td>
    		<p>If you would like to propagate all the child permissions to sub assets, click the "propagate permissions" button:</p>
			<INPUT TYPE="SUBMIT" NAME="Save" VALUE="Propagate" CLASS="adminButtons" OnClick="if (parent.openWindowCount() > 2) { alert('Please close all other open assets before propagating.'); return false;}  if (!confirm('Are you sure you wish to propagate permissions to all sub assets? If you wish to proceed, click OK.')) return false;">
    	</td>
    
    </tr>
  </TABLE>

	</P>
  </TD> </TR> </TABLE>
