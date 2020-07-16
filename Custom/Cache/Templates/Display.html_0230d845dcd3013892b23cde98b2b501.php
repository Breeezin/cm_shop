<form method='POST' action='<?=$_SERVER['REQUEST_URI']?>'>
<input type="radio" name="admin_filter" value="IS NULL" <?php if( $data['filters']['admin_filter'] == 'IS NULL' ) echo "checked='checked'";?> />Unassigned
<input type="radio" name="admin_filter" value="" <?php if( $data['filters']['admin_filter'] == '' ) echo "checked='checked'";?> />All
<?php $tmpl_loop_rows = $data['Q_Administrators']->numRows(); $tmpl_loop_counter = 0; while ($row = $data['Q_Administrators']->fetchRow()) { $tmpl_loop_counter++; ?>
<input type="radio" name="admin_filter" value="= <?php print(ss_HTMLEditFormat($row['us_id'])); ?>"  <?php if( $data['filters']['admin_filter'] == "= {$row['us_id']}" ) echo "checked='checked'";?>/><?php print(ss_HTMLEditFormat($row['us_first_name'])); ?>
<?php } ?>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<input type="radio" name="closed_filter" value="IS NULL" <?php if( $data['filters']['closed_filter'] == 'IS NULL' ) echo "checked='checked'";?> />Open
<input type="radio" name="closed_filter" value=" > NOW() - INTERVAL 7 DAY" <?php if( $data['filters']['closed_filter'] == ' > NOW() - INTERVAL 7 DAY' ) echo "checked='checked'";?> />Closed in last week
<input type="radio" name="closed_filter" value=" > NOW() - INTERVAL 31 DAY" <?php if( $data['filters']['closed_filter'] == ' > NOW() - INTERVAL 31 DAY' ) echo "checked='checked'";?> />Closed in last month
<input type="radio" name="closed_filter" value=" > NOW() - INTERVAL 62 DAY" <?php if( $data['filters']['closed_filter'] == ' > NOW() - INTERVAL 62 DAY' ) echo "checked='checked'";?> />Closed in last 2 months
<input type="radio" name="closed_filter" value="IS NOT NULL" <?php if( $data['filters']['closed_filter'] == 'IS NOT NULL' ) echo "checked='checked'";?> />Closed
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<select name="user_filter_type">
	<option value="Name" <?php if( $data['filters']['user_filter_type'] == 'Name' ) echo "selected='selected'";?>>Name</option>
	<option value="Email" <?php if( $data['filters']['user_filter_type'] == 'Email' ) echo "selected='selected'";?>>Email</option>
	<option value="OrderNumber" <?php if( $data['filters']['user_filter_type'] == 'OrderNumber' ) echo "selected='selected'";?>>Order</option>
</select>
<input type='text' name='user_filter' value="<?= $data['filters']['user_filter']?>" />
&nbsp;<input type='submit' value='Filter' />
</form>

<table width="100%" cellspacing="0" cellpadding="3">

<?
$firstTime = true; $oddEven = true;
	$sep = false;

// if( ss_adminCapability( ADMIN_CUSTOMER_ISSUE ) )
if( true )
{
?>
<?php $tmpl_loop_rows = $data['Q_New']->numRows(); $tmpl_loop_counter = 0; while ($row = $data['Q_New']->fetchRow()) { $tmpl_loop_counter++; ?>

<?php if ($firstTime) { ?>
<tr>
	<td><strong>Issue</strong></td>
	<td><strong>Assigned</strong></td>
	<td><strong>Issue Created</strong></td>
	<td><strong>Entry Created</strong></td>
	<td><strong>User</strong></td>
	<td><strong>Email</strong></td>
	<td><strong>Order</strong></td>
	<td><strong>Text</strong></td>
</tr>
<tr>
<td> New Entries </td>
</tr>

<? $firstTime = false; ?>
<?php } ?>
<?php
	$data['rowClass'] = $oddEven ? 'AdminEvenRow' : 'AdminOddRow';
	$oddEven = !$oddEven;
?>

<tr class="<?php print(ss_HTMLEditFormat($data['rowClass'])); ?>">
	<td><?php print(ss_HTMLEditFormat($row['ci_id'])); ?></td>
	<td><?php print(ss_HTMLEditFormat($row['admin'])); ?></td>
	<td><? echo formatDateTime($row['ci_created'], 'j-M-Y h:m');?></td>
	<td><? echo formatDateTime($row['ce_created'], 'j-M-Y h:m');?></td>
	<?php if( strlen( $row['ci_verified_email'] ) ) echo '<td>Guest</td><td>'.$row['ci_verified_email']."</td>"; else { ?>
	<td>
	<?php if( array_key_exists( 'us_bl_id', $row ) && $row['us_bl_id'] > 0 ) echo "<font color=red>"; ?>
	<?php print(ss_HTMLEditFormat($row['us_first_name'])); ?> <?php print(ss_HTMLEditFormat($row['us_last_name'])); ?>
	<?php if( array_key_exists( 'us_bl_id', $row ) && $row['us_bl_id'] > 0 ) echo "</font>"; ?>
	</td>
	<td><?php print(ss_HTMLEditFormat($row['us_email'])); ?></td>
	<?php } ?>
	<td><strong><?php print(ss_HTMLEditFormat($row['ci_transaction_number'])); ?></strong></td>
	<td><?php print($row['ce_text']); ?></td>
<form>
	<td align="right">
		<select name="jumperSelect" onChange="jumper(this)"><option value="#">Manage</option>
			<option value="index.php?act=ShopSystem_Issues.Edit<?print("&ci_id={$row['ci_id']}&BackURL=".ss_URLEncodedFormat('index.php?act=ShopSystem_Issues.Display'))?>">Edit</option>
			<?php if( !$row['ci_closed'] ) { ?>
			<option value="index.php?act=ShopSystem_Issues.Close&ci_id=<? echo $row['ci_id']."&BackURL=".ss_URLEncodedFormat('index.php?act=ShopSystem_Issues.Display')?>">Close Issue</option>
			<?php } else { ?>
			<option value="index.php?act=ShopSystem_Issues.Open&ci_id=<? echo $row['ci_id']."&BackURL=".ss_URLEncodedFormat('index.php?act=ShopSystem_Issues.Display')?>">Open Issue</option>
			<?php } ?>
			<?php if($row['ci_invisible'] ) { ?>
			<option value="index.php?act=ShopSystem_Issues.UnHide&ci_id=<? echo $row['ci_id']."&BackURL=".ss_URLEncodedFormat('index.php?act=ShopSystem_Issues.Display')?>">Unhide Issue</option>
			<?php } else { ?>
			<option value="index.php?act=ShopSystem_Issues.Hide&ci_id=<? echo $row['ci_id']."&BackURL=".ss_URLEncodedFormat('index.php?act=ShopSystem_Issues.Display')?>">Hide Issue</option>
			<?php } ?>
		</select>
	</td>
</form></tr>
<?php } ?>
<?php } ?>
<tr>
<td> Awaiting Response</td>
</tr>
<?php $tmpl_loop_rows = $data['Q_Awaiting']->numRows(); $tmpl_loop_counter = 0; while ($row = $data['Q_Awaiting']->fetchRow()) { $tmpl_loop_counter++; ?>

<?php
	$data['rowClass'] = $oddEven ? 'AdminEvenRow' : 'AdminOddRow';
	$oddEven = !$oddEven;
?>

<tr class="<?php print(ss_HTMLEditFormat($data['rowClass'])); ?>">
	<td><?php print(ss_HTMLEditFormat($row['ci_id'])); ?></td>
	<td><?php print(ss_HTMLEditFormat($row['admin'])); ?></td>
	<td><? echo formatDateTime($row['ci_created'], 'j-M-Y h:m');?></td>
	<td><? echo formatDateTime($row['ce_created'], 'j-M-Y h:m');?></td>
	<?php if( strlen( $row['ci_verified_email'] ) ) echo '<td>Guest</td><td>'.$row['ci_verified_email']."</td>"; else { ?>
	<td>
	<?php if( array_key_exists( 'us_bl_id', $row ) && $row['us_bl_id'] > 0 ) echo "<font color=red>"; ?>
	<?php print(ss_HTMLEditFormat($row['us_first_name'])); ?> <?php print(ss_HTMLEditFormat($row['us_last_name'])); ?>
	<?php if( array_key_exists( 'us_bl_id', $row ) && $row['us_bl_id'] > 0 ) echo "</font>"; ?>
	</td>
	<td><?php print(ss_HTMLEditFormat($row['us_email'])); ?></td>
	<?php } ?>
	<td><strong><?php print(ss_HTMLEditFormat($row['ci_transaction_number'])); ?></strong></td>
	<td><font color=blue><?php print($row['cir_text']); ?></font></td>
<form>
	<td align="right">
		<select name="jumperSelect" onChange="jumper(this)"><option value="#">Manage</option>
			<option value="index.php?act=ShopSystem_Issues.Edit<?print("&ci_id={$row['ci_id']}&BackURL=".ss_URLEncodedFormat('index.php?act=ShopSystem_Issues.Display'))?>">Edit</option>
			<?php if( !$row['ci_closed'] ) { ?>
			<option value="index.php?act=ShopSystem_Issues.Close&ci_id=<? echo $row['ci_id']."&BackURL=".ss_URLEncodedFormat('index.php?act=ShopSystem_Issues.Display')?>">Close Issue</option>
			<?php } ?>
			<?php if($row['ci_invisible'] ) { ?>
			<option value="index.php?act=ShopSystem_Issues.UnHide&ci_id=<? echo $row['ci_id']."&BackURL=".ss_URLEncodedFormat('index.php?act=ShopSystem_Issues.Display')?>">Unhide Issue</option>
			<?php } else { ?>
			<option value="index.php?act=ShopSystem_Issues.Hide&ci_id=<? echo $row['ci_id']."&BackURL=".ss_URLEncodedFormat('index.php?act=ShopSystem_Issues.Display')?>">Hide Issue</option>
			<?php } ?>
		</select>
	</td>
</form></tr>

<?php } ?>
</table>

<SCRIPT language="Javascript">
<!--

	/*for(var i=0;i<rowCount;i++) {
		theRow = document.getElementById('row'+i);
		rowPositions[i] = getPageOffsetTop(theRow);
	}*/

	function confirmDelete(URL) {
		if ( confirm("Are you sure you want to delete this record ?") ) {
			document.location=URL;
		}
	}

	function toClip( copyme ) {
		netscape.security.PrivilegeManager.enablePrivilege('UniversalXPConnect');
	   const gClipboardHelper = Components.classes["@mozilla.org/widget/clipboardhelper;1"].  
	   getService(Components.interfaces.nsIClipboardHelper);  
	   gClipboardHelper.copyString(copyme);
	}
	
	function jumper(selectList) {		
		urlAppend = selectList.options[selectList.selectedIndex].value;
		var winOpenReg = new RegExp('windowOpen:');
		if(urlAppend.search(winOpenReg) != -1) {	
			urlAppend = urlAppend.replace(winOpenReg, '');
			 w = 550;
		     h = 700;
		     x = Math.round((screen.availWidth-w)/2); //center the top edge
		     y = Math.round((screen.availHeight-h)/2); //center the left edge
		     popupWin = window.open(urlAppend, 'Win', "width="+w+",height="+h+",toolbar=0,location=0,scrollbars=1,statusbar=1,menubar=0,resizable=1,top="+y+",left="+x+",screeenY="+y+",screenX="+x);
		
		     popupWin.creator=self;		
			 popupWin.focus();
		} else {
			selectList.form.reset();
			document.location = urlAppend;
		}
	}
//-->
</SCRIPT>
