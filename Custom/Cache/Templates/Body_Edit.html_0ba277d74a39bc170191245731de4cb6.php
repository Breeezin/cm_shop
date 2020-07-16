<?
	$showSave = true;
	if ($data['this']->underReview) {
		$showSave = false;
	}
?>
<iframe id="PipeFrame" name="PipeFrame" src="about:blank" style="width:100%;height:100;display:none;"></iframe>
<!--- Display a form to edit the details of an asst --->
<form style="padding:0px; margin:0px;" method="POST" action="<?php print(ss_HTMLEditFormat($data['Script_Name'])); ?>?act=<?php print(ss_HTMLEditFormat($data['act'])); ?>&DoAction=Yes" name="AssetForm" id="AssetForm" enctype="MULTIPART/FORM-DATA" onSubmit='<?php if ($data['this']->underReview) { ?>return false;<?php } else { ?>processForm()<?php } ?>'>

    <?php if (count($data['errors']) != 0) {	$errorMessages = ''; foreach ($data['errors'] as $messages) foreach ($messages as $message) $errorMessages .= "<LI>$message</LI>"; print('<P><TABLE WIDTH="95%" BORDER="0" ALIGN="CENTER"><TR><TD CLASS="entryErrors">Errors were detected in the data you entered, please correct the	following issues and re-submit. <UL>'.$errorMessages.'</UL></TD></TR></TABLE></P>'); } ?>
    <table id="AssetTypeEdit" border="0" width="100%" cellpadding="0" cellspacing="0">
		<tr>
			<td> 				
				<?php if ($data['as_type'] == "Users") { ?>
					<table cellpadding="5" cellspacing="0" width="100"><tr><td valign="TOP" class="bodytext">
					Users
					</td></tr></table>
				<?php } elseif (($data['as_system'] == 1) || ($data['as_owner_au_id'] == 0 and !ss_HasPermission('IsSuperUser'))) { ?>
					<table cellpadding="5" cellspacing="0" width="100"><tr><td valign="TOP" class="bodytext">
		          	 <?=str_replace(" ","&nbsp;",ss_HTMLEditFormat($data['at_display']))?>&nbsp;Name&nbsp;:&nbsp;<?=str_replace(" ","&nbsp;",ss_HTMLEditFormat($data['as_name']))?> <?php print(ss_HTMLEditFormat($data['as_deleted'])); ?><input type="hidden" name="as_name" value="<?php print(ss_HTMLEditFormat($data['as_name'])); ?>">
					</td></tr>
					</table>
		        <?php } else { ?> 
		        <table cellpadding="5" cellspacing="0" width="100"><tr>
		        <td valign="TOP" class="bodytext">
					<script type="text/javascript">
					<!--
						// copyright 1999 Idocs, Inc. http://www.idocs.com
						// Distribute this script freely but keep this notice in place
						var allowedCharacters = 'abcdefghijklmnopqrstuvwxyz 0123456789-,.)(';
						
						function letternumber(e) {
							var key;
							var keychar;
							
							if (window.event)
							   key = window.event.keyCode;
							else if (e)
							   key = e.which;
							else
							   return true;
							keychar = String.fromCharCode(key);
							keychar = keychar.toLowerCase();
							
							// control keys
							if ((key==null) || (key==0) || (key==8) || 
							    (key==9) || (key==13) || (key==27) )
							   return true;
							
							// alphas and numbers
							else if ((allowedCharacters.indexOf(keychar) > -1))
							   return true;
							else {
							   alert('Sorry, that character is not allowed in the item name.\n\nIf you would like to use special characters, open the\n\'layout\' bar below and enter values in the \'titles\' section.');
							   return false;
							}
						}
						
						
					  	function checkAssetName() {
							// This is a safety incase they try to do a cut and paste
							var checkStr = document.forms.AssetForm.as_name.value;
							var allValid = true;
							var newVersion = '';
							for (var i = 0;  i < checkStr.length;  i++) {
								ch = checkStr.charAt(i);
								if (allowedCharacters.indexOf(ch.toLowerCase()) == -1) {
									allValid = false;
								} else {
									newVersion += ch;
								}
							}
							if (!allValid) {
								document.forms.AssetForm.as_name.value = newVersion;
								alert('Sorry, one or more characters from your item name are not allowed.\n\nIf you would like to use special characters, open the\n\'layout\' bar below and enter values in the \'titles\' section.');
							}
							return true;
						}
						
					//-->
					</script>				  
				Name: </td><td><input type="TEXT" name="as_name" value="<?php print(ss_HTMLEditFormat($data['as_name'])); ?>" size="30" maxlength="255" onKeyPress="return letternumber(event);" onChange="checkAssetName();"></td>
				<td>
		          <select name="as_appear_in_menus">
		          <?php if (strlen($data['as_appear_in_menus']) && $data['as_appear_in_menus']) { ?>
		          	<?php $CBYes = "SELECTED"; ?>
		          	<?php $CBNo  = ""; ?>
		          <?php } else { ?>
		          	<?php $CBYes = ""; ?>
		          	<?php $CBNo  = "SELECTED"; ?>
		          <?php } ?>
		            <option value="1" <?php print($CBYes) ?>>Appears In Menus</option>
		            <option value="0" <?php print($CBNo) ?>>Does Not Appear In Menus</option>		    		
		    	</select> <?php print(ss_HTMLEditFormat($data['as_deleted'])); ?>
				</td></tr>
				<?php if (ss_optionExists('Asset Sub Title')) {?>
				<tr><td valign="TOP" class="bodytext" nowrap>Sub Title: </td><td><input type="TEXT" name="as_subtitle" value="<?php print(ss_HTMLEditFormat($data['as_subtitle'])); ?>" size="60" maxlength="255"></td></tr>
				<?php }?>
                <?php if (ss_HasPermission('IsDeployer')) { ?>
                <tr><td colspan="2">Asset ID: <?php print($data['as_id']); ?></td></tr>
                <?php } ?>
                <?php if (ss_OptionExists('Newsletter Signatures') and $data['as_type'] == "Page") { ?>
       			<tr>
                    <td colspan="4">
                        <table width="100%">
                        <tr>
                            <td width="50%"><strong>This is the signature of:</strong></td>
               				<td>
               					<?
									$data['Q_Users'] = query("
										SELECT * FROM Users, UserUserGroups, UserGroups
                                        WHERE us_id > 2
                                        AND ug_id = aug_ug_id
										AND UserLink = us_id
                                        AND aug_ug_id = 1
                                        ORDER BY us_first_name, us_last_name
									");
                                ?>
               					<select name="AssetAuthor">
               						<option value="-1">Please select</option>
               						<?php $tmpl_loop_rows = $data['Q_Users']->numRows(); $tmpl_loop_counter = 0; while ($row = $data['Q_Users']->fetchRow()) { $tmpl_loop_counter++; ?>
                   						<option <?if ($data['this']->fields['AssetAuthor'] === $row['us_id']) print('SELECTED'); ?> value="<?php print(ss_HTMLEditFormat($row['us_id'])); ?>"><?php print(ss_HTMLEditFormat($row['us_first_name'])); ?> <?php print(ss_HTMLEditFormat($row['us_last_name'])); ?> - <?php print(ss_HTMLEditFormat($row['us_email'])); ?></option>
                   					<?php } ?>
               					</select>
              				</td>
                        </tr>
                        </table>
                </td>
                </tr>
                <?php } ?>
                </table>
				 <?php } ?>
				
			</td>
			<td align="RIGHT">
				<table cellpadding="5" cellspacing="0" height="40"><tr><td>
				  	<!--- Save, Move, Copy, Delete --->
				        <table id="enabledButtons" width="100" border="0" cellpadding="0" cellspacing="0">
					          <script language="Javascript">
					          	 function disableButtons() {
					          	 	document.getElementById('enabledButtons').style.display = 'none';
					          	 	document.getElementById('disabledButtons').style.display = '';
					          	 }	
					          </script>
						<?php if ($showSave) { ?>
					          <tr>
					            <td width="70%">&nbsp;</td>
					             
									<?php if ($data['as_type'] != "Users") { ?>
										<td><input type="IMAGE" src="Images/but-save-close.gif" alt="Save Asset" title="Save and Close" name="SaveCloseButton" style="border:none 1px;" id="SaveCloseButton" onMouseOver="MM_swapImage('label','','Images/but-save-close-lb.gif','SaveCloseButton','','Images/but-save-close-on.gif',1)" onMouseOut="MM_swapImgRestore()" onClick="document.getElementById('SaveStarting').innerHTML = 'Saving&nbsp;and&nbsp;Closing...&nbsp;please&nbsp;wait';disableButtons();"></td>
									<?php } ?>
									<td><input type="IMAGE" src="Images/but-save.gif" alt="Save Asset" title="Save" style="border:none 1px;" id="SaveButton" onMouseOver="MM_swapImage('label','','Images/but-save-lb.gif','SaveButton','','Images/but-save-on.gif',1)" onMouseOut="MM_swapImgRestore()" onClick="document.getElementById('SaveStarting').innerHTML = 'Saving...&nbsp;please&nbsp;wait';disableButtons();"></td>
								
								<?php if (ss_HasPermission('IsDeployer')) { ?>
								<?php if ($data['as_type'] == "Page") { ?>
									<td><input name="import" type="IMAGE" src="Images/but-import.gif" alt="Import Asset" title="Import" style="border:none 1px;" id="Image6" onMouseOver="MM_swapImage('label','','Images/but-import-lb.gif','Image6','','Images/but-import-on.gif',1)" onMouseOut="MM_swapImgRestore()" onClick="document.getElementById('SaveStarting').innerHTML = 'Importing... please wait';disableButtons();"></td>
									<td><input name="export" type="IMAGE" src="Images/but-export.gif" alt="Export Asset" title="Export" style="border:none 1px;" id="Image5" onMouseOver="MM_swapImage('label','','Images/but-export-lb.gif','Image5','','Images/but-export-on.gif',1)" onMouseOut="MM_swapImgRestore()" onClick="document.getElementById('SaveStarting').innerHTML = 'Exporting... please wait';disableButtons();"></td>
								<?php } ?>
								<?php } ?>
								<?php if (!(($data['as_system'] == 1) || ($data['as_owner_au_id'] == 0 and !ss_HasPermission('IsSuperUser')))) { ?>
						            <td><a href="javascript:moveAsset()" ><img border="0" src="Images/but-move.gif" name="Image2" id="Image2" onMouseOver="MM_swapImage('label','','Images/but-move-lb.gif','Image2','','Images/but-move-on.gif',1)" onMouseOut="MM_swapImgRestore()"></a></td>
						            
						            <td><a href="javascript:copyAsset()"><img border="0" src="Images/but-copy.gif" name="Image3" id="Image3" onMouseOver="MM_swapImage('label','','Images/but-copy-lb.gif','Image3','','Images/but-copy-on.gif',1)" onMouseOut="MM_swapImgRestore()"></a></td>
						            
						            <?php if (!$data['this']->supportsReview) { ?>
						            	<td><a href="javascript:deleteAsset()"><img border="0" src="Images/but-delete.gif" name="Image4" id="Image4" onMouseOver="MM_swapImage('label','','Images/but-delete-lb.gif','Image4','','Images/but-delete-on.gif',1)" onMouseOut="MM_swapImgRestore()"></a></td>
						            <?php } ?>
					           	<?php } ?>
					          </tr>
					          <?php } else { ?>
					          <tr>
					          	<td colspan="8">
									<img src="Images/holder.gif" width="1" height="27">
					          	</td>
					          </tr>
					          <?php } ?>
							  <tr><td align="right" colspan="8"><img src="Images/but-holder-lb.gif" name="label" width="89" height="12" id="label"></td>
							  </tr>
							  <tr>
							  	<td align="right" colspan="8" id="SaveCompleted" style="color: Red">&nbsp;</td>
						  	</tr>
			         </table>
			        <table id="disabledButtons" style="display:none;" width="100" border="0" cellpadding="0" cellspacing="0">
				          <tr>
				            <td width="70%">&nbsp;</td>
							<?php if ($data['as_type'] != "Users") { ?>
							<td><img src="Images/but-save-close.gif" style="border:none 1px;" id="SaveCloseButton"></td>
							<?php } ?>
							<td><img src="Images/but-save.gif" style="border:none 1px;" id="SaveButton"></td>
							<?php if (ss_HasPermission('IsDeployer')) { ?>
							<?php if ($data['as_type'] == "Page") { ?>
								<td><img src="Images/but-import.gif" style="border:none 1px;"></td>
								<td><img src="Images/but-export.gif" style="border:none 1px;"></td>
							<?php } ?>
							<?php } ?>
							<?php if (!(($data['as_system'] == 1) || ($data['as_owner_au_id'] == 0 and !ss_HasPermission('IsSuperUser')))) { ?>
					            <td><img border="0" src="Images/but-move.gif"></td>
					            <td><img border="0" src="Images/but-copy.gif"></td>
					            <?php if (!$data['this']->supportsReview) { ?>
						            <td><img border="0" src="Images/but-delete.gif"></td>
						        <?php } ?>
				           	<?php } ?>
				          </tr>
						  <tr><td align="right" colspan="8"><img src="Images/but-holder-lb.gif" name="label2" width="89" height="12" id="label2"></td>
						  </tr>
						  <tr>
						  	<td align="right" colspan="8" id="SaveStarting" style="color: Red">&nbsp;</td>
					  	  </tr>
				    </table>		

				</td></tr></table>
			</td>
		</tr>		
		
		<tr> 
	        <td colspan="2">
	        
					 <script language="javascript">
						function toggleView(choice,icon) {
							
							var panel = document.getElementById('Panel' + choice);
							var panelBar = document.getElementById('PanelBar' + choice);
							var panelSpan = document.getElementById('PanelSpan' + choice);
							var panelBarIcon = document.getElementById('PanelBarIcon' + choice);
							var panelBarArrow = document.getElementById('PanelBarArrow' + choice);
				
							if (panel.style.display == 'none') {

								// Make visible
								panel.style.display = '';
							
								
								//if (choice != 0) {
							 
								//panel.style.position = 'relative';
								
								//alert("togglveiew: 2-2:pos "+panel.style.position);
								panelBar.className = 'panelBarOpen';
								//alert("togglveiew: 2-3");
								panelSpan.className = 'tabtext-onCopy';
								//alert("togglveiew: 2-4");
								panelBarArrow.src = 'Images/arrow-up.gif';
								//alert("togglveiew: 2-5");
								panelBarIcon.src = 'Images/'+icon+'-icon-on.gif';
								//alert("togglveiew: 2-6");
								//}
							} else {
								// Make hidden
								//alert("togglveiew: 3");
								//panel.style.visibility = 'hidden';
								//panel.style.position = 'absolute';
								panel.style.display = 'none';
								panelBar.className = 'panelBarClosed';
								panelSpan.className = 'tabtext-off';
								panelBarArrow.src = 'Images/arrow-dwn.gif';
								panelBarIcon.src = 'Images/'+icon+'-icon.gif';
							}
//							alert(panelBarIcon.src);
						}
						//alert(document.body.clientWidth+' by '+document.body.clientHeight);
						//document.write('<div style="width:100%;height:100%;overflow:auto;">');
					</script> 

					<?php if (ss_optionExists('Asset Edit Fixed Buttons')) { ?>
					<script>
						document.write('<div id="assetScroller" style="width:'+(document.body.clientWidth)+'px;height:'+(document.body.clientHeight-66)+';overflow:auto;">'); // 66 for IE
					</script>
					<?php } ?>
							
                              <table width="100%" border="0" cellspacing="7" cellpadding="0">
                                <tr> 
                                  <td>
           
                                  <?php $result = new Request("Asset.OpenerCloser",array('Open'	=>  1,'Icon' =>	'page', 'Panel'	=>	3, 'Name' => $data['at_display'],));	print($result->display); ?>

				<table id="Panel3" width="100%" style="display:none;" class="panelBackground" cellpadding="10">
					<tr>
						<td> 
                           <table bgcolor="white" width="100%" border="0" cellspacing="0" cellpadding="8" style="border: 1px solid black;"><tr><td>
                           <?php if ($data['this']->underReview) { ?>
                           		<?
                           			$data['Reviewer'] = 'Unknown';
                           			if ($data['this']->fields['AssetReviewer'] !== null) {
                           				$reviewer = getRow("
	                           				SELECT * FROM Users
                           					WHERE us_id = ".$data['this']->fields['AssetReviewer']."
                           				");
                           				if ($reviewer !== null) {
	                           				$data['Reviewer'] = $reviewer['us_first_name'].' '.$reviewer['us_last_name'];
                           				}
                           			}
                           		?>
								This item is under review.  You cannot modify it until it has been reviewed by <?php print(ss_HTMLEditFormat($data['Reviewer'])); ?>.
                           	<?php } else { ?>
                           		<?php $data['AssetTypeObject']->edit($data['this']); ?>
                           <?php } ?>
							</td></tr></table>
						</td>
					</tr>
				</table>
                        </td>
                                </tr>

                               <?php if (ss_optionExists('Schedule Assets')) { ?>
                                <tr> 
                                  <td> 
								
				
				<?php $result = new Request("Asset.OpenerCloser",array('Icon' =>	'schedule', 'Panel'	=>	6, 'Name' => 'Scheduling',));	print($result->display); ?>

				<table id="Panel6" width="100%" style="display:none;" class="panelBackground" cellpadding="10">
					<tr>
						<td>
                           <table bgcolor="white" width="100%" border="0" cellspacing="0" cellpadding="3" style="border: 1px solid black;"><tr><td>
                           		<table width="100%">
                           			<tr>
										<td width="200" valign="top"><strong>Online</strong></td>
										<td>
											<table width="500">
												<tr>
													<td width="1%"><input <? if ($data['AssetOnline'] == 'Never') echo "checked"; ?> type="radio" name="AssetOnline" value="Never"></td>
													<td width="32%">Never</td>
													<td width="1%"><input <? if ($data['AssetOnline'] == null) echo "checked"; ?> type="radio" name="AssetOnline" value=""></td>
													<td width="32%">Now</td>
													<td width="1%"><input <? if ($data['AssetOnline'] == 'Date') echo "checked"; ?> type="radio" name="AssetOnline" value="Date"></td>
													<td width="33%" nowrap>Starting From: <?php $data['fieldSet']->displayField('AssetOnlineDate'); ?></td>
												</tr>
											</table>
										</td>                           			
                           			</tr>
                           			<tr>
										<td width="200" valign="top"><strong>Offline</strong></td>
										<td>
											<table width="500">
												<tr>
													<td width="1%"><input <? if ($data['AssetOffline'] == null) echo "checked"; ?> type="radio" name="AssetOffline" value=""></td>
													<td width="32%">Never</td>
													<td width="1%"><input <? if ($data['AssetOffline'] == 'Now') echo "checked"; ?> type="radio" name="AssetOffline" value="Now"></td>
													<td width="32%">Now</td>
													<td width="1%"><input <? if ($data['AssetOffline'] == 'Date') echo "checked"; ?> type="radio" name="AssetOffline" value="Date"></td>
													<td width="33%" nowrap>Starting From: <?php $data['fieldSet']->displayField('AssetOfflineDate'); ?></td>
												</tr>
											</table>
										</td>                           			
                           			</tr>
                           		</table>
							</td></tr></table>
						
						</td>
					</tr>
				</table>			
                                  </td>
                                </tr>
                                <?php } ?>

                                <?php if ($data['this']->supportsReview and !$data['this']->underReview) { ?>
                                <tr> 
                                  <td> 
								
				
				<?php $result = new Request("Asset.OpenerCloser",array('Icon' =>	'review', 'Panel'	=>	5, 'Name' => 'Review',));	print($result->display); ?>

				<table id="Panel5" width="100%" style="display:none;" class="panelBackground" cellpadding="10">
					<tr>
						<td>
                           <table bgcolor="white" width="100%" border="0" cellspacing="0" cellpadding="3" style="border: 1px solid black;"><tr><td>
                           		<table width="100%">
                           			<tr>
										<td width="200" valign="top"><strong>Status</strong></td>
										<td>
											<?php if ($data['this']->liveContent) { ?>
                           						Live
                           					<?php } else { ?>
                           						This content has been edited and is not currently live on the website.  Click the revert button to erase any changes and revert back to the live content <input type="submit" name="Revert" value="Revert" onClick="if (confirm('Are you sure you wish to revert to the current live version of this item?')) { document.getElementById('SaveStarting').innerHTML = 'Reverting...&nbsp;please&nbsp;wait';disableButtons(); return true;} else { return false; }">
                           					<?php } ?>
										</td>                           			
                           			</tr>
                           			<tr>
                           				<td><strong>Author</strong></td>
                           				<?
                           					$data['Author'] = 'Unknown';
                           					if ($data['this']->fields['AssetAuthor'] !== null) {
                           						$author = getRow("
	                           						SELECT * FROM Users
                           							WHERE us_id = {$data['this']->fields['AssetAuthor']}
                           						");
                           						if ($author !== null) {
                           							$data['Author'] = $author['us_first_name'].' '.$author['us_last_name'];
                           						}
                           					}
                           				?>
                           				<td><?php print(ss_HTMLEditFormat($data['Author'])); ?></td>
                           			</tr>
                           			<tr>
                           				<td valign="top"><strong>Author Comments</strong><br />This will be sent to the reviewer.</td>
                           				<td><TEXTAREA NAME="AssetAuthorComments" ROWS="6"  STYLE="width:100%" wrap="soft"><?=ss_HTMLEditFormat($data['this']->fields['AssetAuthorComments'])?></TEXTAREA></td>
                           			</tr>
                           			<tr>
                           				<td><strong>Reviewer</strong></td>
                           				<td>
                           					<?
												$data['Q_Reviewers'] = query("
													SELECT us_id, us_first_name, us_last_name FROM Users, UserUserGroups, UserGroups
													WHERE ug_reviewer = 1
														AND ug_id = aug_ug_id
														AND UserLink = us_id
													ORDER BY us_first_name, us_last_name
												");					
                           					
                           					?>
                           					<select name="AssetReviewer">
                           						<option value="">Please select</option>
                           						<?php $tmpl_loop_rows = $data['Q_Reviewers']->numRows(); $tmpl_loop_counter = 0; while ($row = $data['Q_Reviewers']->fetchRow()) { $tmpl_loop_counter++; ?>
	                           						<option <?if ($data['this']->fields['AssetReviewer'] === $row['us_id']) print('SELECTED'); ?> value="<?php print(ss_HTMLEditFormat($row['us_id'])); ?>"><?php print(ss_HTMLEditFormat($row['us_first_name'])); ?> <?php print(ss_HTMLEditFormat($row['us_last_name'])); ?></option>
    	                       					<?php } ?>
                           					</select>
                          				</td>
                           			</tr>
                           			<tr>
                           				<td valign="top"><strong>Reviewer Comments</strong></td>
                           				<td><?
                           					if (strlen($data['this']->fields['AssetReviewerComments'])) {
                           						print(ss_HTMLEditFormatWithBreaks($data['this']->fields['AssetReviewerComments']));
                           					} else {
                           						print('None');	
                           					}
                           				?></td>
                           			</tr>
                           			<tr>
                           				<td width="200"><strong>Review</strong><br />
											If you want to request your changes to go live, click the Request Review button. Once sent for review, you can not edit again until it has been reviewed.
										</td>
										<td>
											<input type="submit" name="RequestReview" value="Request Review" onClick="if (this.form.AssetReviewer.selectedIndex == 0) { alert('Please select a reviewer'); return false; } if (confirm('Are you sure you wish to send this item for review?')) { document.getElementById('SaveStarting').innerHTML = 'Saving&nbsp;and&nbsp;sending&nbsp;for&nbsp;review...&nbsp;please&nbsp;wait';disableButtons(); return true;} else { return false; }">
										</td>
                           			</tr>
                           			<tr>
                           				<td width="200"><strong>Delete</strong><br />
											If you want to remove this item permanently, you must request approval from the reviewer by clicking the Request Delete button.
										</td>
										<td>
											<input type="submit" name="RequestDelete" value="Request Delete" style="color:red;" onClick="if (this.form.AssetReviewer.selectedIndex == 0) { alert('Please select a reviewer'); return false; } if (confirm('Are you sure you wish request this item to be DELETED?')) { document.getElementById('SaveStarting').innerHTML = 'Requesting&nbsp;Delete...&nbsp;please&nbsp;wait';disableButtons(); return true;} else { return false; }">
										</td>
                           			</tr>
                           		</table>
							</td></tr></table>
						
						</td>
					</tr>
				</table>			
                                  </td>
                                </tr>
                                <?php } ?>
                                <tr> 
                                  <td> 
								
				
				<?php $result = new Request("Asset.OpenerCloser",array('Icon' =>	'layout', 'Panel'	=>	0, 'Name' => 'Layout',));	print($result->display); ?>

				<table id="Panel0" width="100%" style="display:none;" class="panelBackground" cellpadding="10">
					<tr>
						<td>
                           <table bgcolor="white" width="100%" border="0" cellspacing="0" cellpadding="3" style="border: 1px solid black;"><tr><td>
						<?php $data['this']->layoutForm();  ?>												
							</td></tr></table>
						
						</td>
					</tr>
				</table>			
                                  </td>
                                </tr>

               <tr> 
              <td> 
	             <?php if ($data['IsDeployer']) { ?>
				<?php $result = new Request("Asset.OpenerCloser",array('Icon' =>	'security', 'Panel'	=>	2, 'Name' => 'Security',));	print($result->display); ?>
				<table id="Panel2" width="100%" style="display:none;" class="panelBackground" cellpadding="10">
	                <tr>
	                    <td>
                           <table bgcolor="white" width="100%" border="0" cellspacing="0" cellpadding="3" style="border: 1px solid black;"><tr><td>
							<?php $result = new Request("Asset.Security",array('as_id' => $data['as_id'], 'as_owner_au_id'	=>	$data['as_owner_au_id'],));	print($result->display); ?>
							</td></tr></table>
						</td>
	            	</tr>
	            <?php } ?>
	             <?php if ($data['IsDeployer']) { ?>
                               <tr> 
                                  <td> 
				<?php $result = new Request("Asset.OpenerCloser",array('Icon' =>	'development', 'Panel'	=>	4, 'Name' => 'Development',));	print($result->display); ?>
				<table id="Panel4" width="100%" style="display:none;" class="panelBackground" cellpadding="10">
	                <tr>
	                    <td>
                           <table bgcolor="white" width="100%" border="0" cellspacing="0" cellpadding="3" style="border: 1px solid black;"><tr>
							<td class="bodytext">Type: </td><td class="bodytext"><?php $data['fieldSet']->displayField('as_type'); ?></td>					
							</tr>
							<tr>
							<td class="bodytext">Development: </td><td class="bodytext"><?php $data['fieldSet']->displayField('as_dev_asset'); ?></td>					
							</tr>
							</table>
						</td>
	            	</tr>
	          	</table>            
	          	    
                                  </td>
                                </tr> 
                                <?php } ?>    	
	          	</table>
				</td>
                </tr>                            
                
  		</table>
					<?php if (ss_optionExists('Asset Edit Fixed Buttons')) { ?>
						</div>
					<?php } ?>
  		
				<script language="Javascript">
					toggleView(3,'sub');
					   <?php if ($data['this']->supportsReview and !$data['this']->underReview) { ?>toggleView(5,'sub');<?php } ?>
					   <?php if (ss_optionExists('Schedule Assets')) { ?>toggleView(6,'sub');<?php } ?>
					//toggleView(0,'layout');
				</script>
				
			</td>
	    </tr>
    </table>
    <input type="HIDDEN" name="as_id" 		value="<?php print(ss_HTMLEditFormat($data['as_id'])); ?>">
    <input type="HIDDEN" name="as_parent_as_id" value="<?php print(ss_HTMLEditFormat($data['as_parent_as_id'])); ?>">
    <input type="HIDDEN" name="ViewAfter" 		value="No">
	<input type="HIDDEN" name="soHeight"		value="<?php print(ss_HTMLEditFormat($data['SoHeight'])); ?>">
	

<script type="text/javascript">
function mySubmit() {
	//document.AssetForm.onsubmit(); // workaround browser bugs.
//	document.AssetForm.submit();
};
</script>	
	
</form>
<?php if (array_key_exists('DoAction',$data)) { ?>
    <script language="JavaScript">
    	//parent.frames.AssetPanelFrame.MTreeRoot.nodes[<?php print(ss_HTMLEditFormat($data['as_id'])); ?>].changeDisplay('<?php print(ss_JSStringFormat($data['as_name'])); ?>');
    	<?php if ($data['AssetNameChanged']) { ?>
			parent.assetReload();
		<?php } ?>
		
		<?php if (count($data['errors'])) { ?>
			alert("Save failed.  Please correct errors and try again.");
		<?php } else { ?>
	    	<?php if ($data['AssetNameChanged']) { ?>
				parent.changeTitle(<?php print(ss_HTMLEditFormat($data['as_id'])); ?>, '<?php print(ss_JSStringFormat(str_replace(' ', '&nbsp;', $data['as_name']))) ?>');
			<?php } ?>
			document.getElementById('SaveCompleted').innerHTML = '<?php print($data['JustDid']); ?>';
			setTimeout("document.getElementById('SaveCompleted').innerHTML = '&nbsp;';",4000);
//			alert("Save completed.");
		<?php } ?>
		
				
		// note actually needed as assets refresh sub assets when they focus
		
//		document.forms.AssetForm.FocusGetter.focus();
	</script>
<?php } ?>
<?
	$GLOBALS['cfg']['debugMode'] = false;
?>
