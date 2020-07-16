<!-- TinyMCE -->
<script type="text/javascript" src="jscripts/tiny_mce/tiny_mce.js"></script>
<script type="text/javascript">
	tinyMCE.init({
		mode : "textareas",
		// theme : "simple"
		theme : "advanced",
		theme_advanced_toolbar_location : "top",
		theme_advanced_toolbar_align : "left",
		theme_advanced_disable : "strikethrough,styleselect,formatselect",
		theme_advanced_buttons1_add : "bullist,numlist,separator,undo,redo,separator,link",
		theme_advanced_buttons3 : "",
		plugins : "paste,spellchecker",
        theme_advanced_buttons2_add : "pastetext,pasteword,selectall,forecolor,backcolor,spellchecker",
        paste_auto_cleanup_on_paste : true,
        });
</script>
<!-- /TinyMCE -->
<script language="Javascript">
function showProd(what)
    {  
	showme=document.getElementById('more'+what);
	showme.style.display='none';
	showme=document.getElementById('less'+what);
	showme.style.display='';
	}
function hideProd(what)
    {  
	showme=document.getElementById('more'+what);
	showme.style.display='';
	showme=document.getElementById('less'+what);
	showme.style.display='none';
	}
</script>
<br />
<a href='<?=$data['BackURL']?>'><h2>Back</h2></a>
<?php if( !$data['Issue']['ci_closed'] ) { ?>
<a href="index.php?act=ShopSystem_Issues.Close&ci_id=<? echo $data['Issue']['ci_id']."&BackURL=".ss_URLEncodedFormat('index.php?act=ShopSystem_Issues.Display')?>">Close Issue, Back to List</a>
<?php } else {?>
<a href="index.php?act=ShopSystem_Issues.Open&ci_id=<? echo $data['Issue']['ci_id']."&BackURL=".ss_URLEncodedFormat('index.php?act=ShopSystem_Issues.Display')?>">Open Issue, Back to List</a>
<?php }?>
<br />
<br />
<br />
Issue number <?php print(ss_HTMLEditFormat($data['ci_id'])); ?> for <?php
if( strlen( $data['Issue']['ci_verified_email'] ) )
	echo "Guest : ".$data['Issue']['ci_verified_email'];
else {
?>
	<strong>User:</strong> <a target='_blank' href='/index.php?act=UsersAdministration.Edit&us_id=<? echo $data['Issue']['us_id']; ?>'>
<?php
	echo $data['Issue']['us_first_name']?>&nbsp;<?=$data['Issue']['us_last_name'];
	echo "</a>";
	echo '&nbsp;&nbsp;&nbsp;&nbsp;'.$data['Issue']['us_notes'];
	echo '&nbsp;&nbsp;&nbsp;&nbsp;'.$data['othersTotal']['Num']." Orders <b>Total </b>".ss_decimalFormat( $data['othersTotal']['Total']).'&nbsp;';
	if( $data['Issue']['us_account_credit'] > 0.00001)
		echo "<br /><strong><font color='blue'>Account CREDIT is ".$data['Issue']['us_account_credit']." from ".$data['Issue']['po_id']." ".$data['Issue']['pg_name']." ".$data['Issue']['po_currency_name']."</font></strong>";
	if( $data['Issue']['us_account_credit'] < -0.00001)
		echo "<br /><strong><font color='red'>Account DEBIT is ".$data['Issue']['us_account_credit']." from ".$data['Issue']['po_id']." ".$data['Issue']['pg_name']." ".$data['Issue']['po_currency_name']."</font></strong>";
?>
<br/><strong>Other Issues</strong><br/>
<table>
<tr>
<?php $tmpl_loop_rows = $data['Q_OtherIssues']->numRows(); $tmpl_loop_counter = 0; while ($row = $data['Q_OtherIssues']->fetchRow()) { $tmpl_loop_counter++; ?>
	<td><a href="index.php?act=ShopSystem_Issues.Edit&ci_id=<?php print(ss_HTMLEditFormat($row['ci_id'])); ?>&BackURL=index.php%3FFuseAction%3DOnlineShop_Issues.Display"><?php print(ss_HTMLEditFormat($row['ci_id'])); ?></a><br/>
	<?php print(ss_HTMLEditFormat($row['ci_transaction_number'])); ?>
	</td>
<?php } ?>
</tr>
</table>
<br />
<strong>Gateway</strong>
<?php
$pg = getUserPaymentGateway( $data['Issue']['us_id'] );
echo implode( '<br />', $pg['Desc']).'<br />';
echo $pg['Gateway']['pg_description'];
$i = 0;
?>
<br />
<br />
<table border=1>
<tr>
<?php $tmpl_loop_rows = $data['Q_Others']->numRows(); $tmpl_loop_counter = 0; while ($row = $data['Q_Others']->fetchRow()) { $tmpl_loop_counter++; ?>
<?php
	if( strlen( $row['or_reshipment'] ) || ( ( $row['tr_profit'] < 0 ) && !strlen( $row['or_out_of_stock'] ) ) )
		$colourHex = "#FF0000";
	else
		$colourHex = "#000000";
	
	$cs = "<font color=$colourHex>";
	$cc = "</font>";
	echo "<td valign='top'>";
	echo $cs.$row['or_tr_id'].$cc.'<br />';
	echo $cs.formatDateTime($row['or_recorded'], 'j-M-Y').$cc.'<br />';
	echo $cs.$row['tr_currency_code'].'&nbsp;'.$row['tr_profit'].$cc.'<br />';
	echo $cs.$row['pg_name'].$cc;
	echo "</td>";
	if( ++$i > 15 )
	{
		echo "</tr><tr></tr><tr>";
		$i = 0;
	}

?>
<?php } ?>
</tr>
</table>
<?php
	}
?>
<br />
<a href='index.php?act=WebPayAdministration.List&as_id=514&SearchArea=2&SearchKeyword=<?=$data['Issue']['us_email']?>' target='_blank'><?=$data['Issue']['us_email']?></a><br />
<?php if( $data['Issue']['ci_invisible'] ) echo "This Issue will NOT show on {$data['Issue']['us_first_name']}\'s member page"; ?><br/>
<br />
<form method="post" action="index.php?act=ShopSystem_Issues.Assign&ci_id=<?php print(ss_HTMLEditFormat($data['ci_id'])); ?>">
<input type="hidden" name="BackURL" value="<?=$_SERVER['REQUEST_URI']?>" />
<input type="radio" name="assigned_to" value="IS NULL" <?php if( strlen( $data['Issue']['ci_assigned_to'] ) == 0 ) echo "checked='checked'";?> />Unassigned
<?php $tmpl_loop_rows = $data['Q_Administrators']->numRows(); $tmpl_loop_counter = 0; while ($row = $data['Q_Administrators']->fetchRow()) { $tmpl_loop_counter++; ?>
<input type="radio" name="assigned_to" value="<?php print(ss_HTMLEditFormat($row['us_id'])); ?>"  <?php if( $data['Issue']['ci_assigned_to'] == $row['us_id'] ) echo "checked='checked'";?>/><?php print(ss_HTMLEditFormat($row['us_first_name'])); ?>
<?php } ?>
&nbsp;&nbsp;&nbsp;<input type="submit" name="Submit" value="Reassign">
</form>
<br />
<form method="post" action="<?=$_SERVER['REQUEST_URI']?>">
Canned Question
<select name='CannedQuestion'>
<option value="NULL"  <?php if( !$data['Issue']['ci_cq_id'] ) echo "selected='selected'";?>/>UnCanned
<?php $tmpl_loop_rows = $data['Q_CannedQuestions']->numRows(); $tmpl_loop_counter = 0; while ($row = $data['Q_CannedQuestions']->fetchRow()) { $tmpl_loop_counter++; ?>
<option value="<?php print(ss_HTMLEditFormat($row['cq_id'])); ?>"  <?php if( $data['Issue']['ci_cq_id'] == $row['cq_id'] ) echo "selected='selected'";?>/><?php print(ss_HTMLEditFormat($row['cq_text'])); ?>
<?php } ?>
</select>
<span id='moreQ' style="display:;" >
<a style="display:" href="Javascript:showProd('Q');void(0);" class="morelink">+ More</a>
</span>
<span id="lessQ"  style="display:none;" >
<a style="display:" href="Javascript:hideProd('Q');void(0);" class="morelink">+ Less</a>
<input type='text' name='newQuestion' size=80 />
</span>
&nbsp;<input type="submit" name="Submit" value="Update"></form>
<br />
<?php if( $data['Issue']['ci_cq_id'] ) { ?>
<form method="post" action="<?=$_SERVER['REQUEST_URI']?>#the_end">
Canned Responses
<input type='hidden' name='newResponse_cq_id' value='<?=$data['Issue']['ci_cq_id']?>' />
<select name='CannedResponse'>
<?php $tmpl_loop_rows = $data['Q_CannedResponses']->numRows(); $tmpl_loop_counter = 0; while ($row = $data['Q_CannedResponses']->fetchRow()) { $tmpl_loop_counter++; ?>
<option value="<?php print(ss_HTMLEditFormat($row['cr_id'])); ?>" /><?php print(ss_HTMLEditFormat($row['cr_name'])); ?>
<?php } ?>
</select>
<span id='moreCan' style="display:;" >
<a style="display:" href="Javascript:showProd('Can');void(0);" class="morelink">+ More</a>
</span>
<span id="lessCan"  style="display:none;" >
<a style="display:" href="Javascript:hideProd('Can');void(0);" class="morelink">+ Less</a>
<span><input type='text' name='newResponseName' size=80 /><br /><textarea name="newResponseText" rows="10" cols="80"></textarea></span>&nbsp;&nbsp;
</span>
<input type="submit" name="Submit" value="Insert">
</form>
<br />
<?php } ?>
<br />
Edit History, conflicts in <font color=red><strong>RED</strong></font> mean you shouldn't be editing this.
<br />
<table cellspacing="0" cellpadding="3">
<tr>
<th>
	Who
</th>
<th>
	When
</th>
<th>
	Seconds ago
</th>
<th>
	Closed?
</th>
</tr>
<?php $tmpl_loop_rows = $data['Q_Edits']->numRows(); $tmpl_loop_counter = 0; while ($row = $data['Q_Edits']->fetchRow()) { $tmpl_loop_counter++; ?>
	<tr>
	<?php
	$conflict = false;
	if( ($row['cie_us_id'] != ss_getUserID() ) && ( $row['ago'] < 300 ) )
		$conflict = true;
	?>
	<td>
		<?php if( $conflict ) echo "<font color=red><strong>"; ?>
		<?php print(ss_HTMLEditFormat($row['us_first_name'])); ?>
		<?php if( $conflict ) echo "</strong></font>"; ?>
	</td>
	<td>
		<?php if( $conflict ) echo "<font color=red><strong>"; ?>
		<?php echo formatDateTime($row['cie_when'], 'j-M-Y h:m');?>
		<?php if( $conflict ) echo "</strong></font>"; ?>
	</td>
	<td>
		<?php if( $conflict ) echo "<font color=red><strong>"; ?>
		<?php print(ss_HTMLEditFormat($row['ago'])); ?>
		<?php if( $conflict ) echo "</strong></font>"; ?>
	</td>
	<td>
		<?php if( $conflict ) echo "<font color=red><strong>"; ?>
		<?php if( $row['cie_closed'] ) echo "Yes"; ?>
		<?php if( $conflict ) echo "</strong></font>"; ?>
	</td>
	</tr>
<?php } ?>
</table>
<br />
<form method="post" action="<?=$_SERVER['REQUEST_URI']?>">
<table width="100%" cellspacing="0" cellpadding="3">
<tr>
<td>
<?php $order = array(); ?>
<input type="radio" name="order_number" value="IS NULL" <?php if( strlen( $data['Issue']['ci_transaction_number'] ) == 0 ) echo "checked='checked'";?> />Unassigned
</td>
<?php $tmpl_loop_rows = $data['Q_VisibleOrders']->numRows(); $tmpl_loop_counter = 0; while ($row = $data['Q_VisibleOrders']->fetchRow()) { $tmpl_loop_counter++; ?>
<td>
<input type="radio" name="order_number" value="<?php print(ss_HTMLEditFormat($row['tr_id'])); ?>"  <?php if( $data['Issue']['ci_transaction_number'] == $row['tr_id'] ) {echo "checked='checked'";$order = $row;}?>/>
<a target='_blank' href='/index.php?act=OnlineShop.ViewOrder&or_id=<?php print(ss_HTMLEditFormat($row['or_id'])); ?>&tr_id=<?php print(ss_HTMLEditFormat($row['tr_id'])); ?>&as_id=514&BreadCrumbs=Administration : Orders :'>
<?php print(ss_HTMLEditFormat($row['tr_id'])); ?></a><br />
<?php
echo 'Placed:'.formatDateTime($row['or_recorded'], 'j-M-Y');
if( strlen( $row['or_cancelled'] ) ) echo '<br />Cancelled';
if( strlen( $row['or_paid'] ) ) echo '<br />Paid:'.formatDateTime($row['or_paid'], 'j-M-Y');
if( strlen( $row['or_paid_not_shipped'] ) ) echo '<br />PaidNotShipped';
if( strlen( $row['or_shipped'] ) ) echo '<br />Shipped:'.formatDateTime($row['or_shipped'], 'j-M-Y');
?>
</td>
<?php } ?>
</tr>
</table>
&nbsp;&nbsp;&nbsp;<input type="submit" name="Submit" value="Reassign">
</form>
<?php
$order_number = $order['or_tr_id'];
$overdue = 'xxxxxxx';
$shipped = 'xxxxxxx';
$working_days = 'xxxxxxx';
$out_of_stock = array();
$to_credit = 0;
if( strlen( $order['or_shipped'] ) )
{
	$overdue = formatDateTime(overdue_in($order['or_shipped'], $order['or_country'], 28), 'j-M-Y');
	$shipped = formatDateTime($order['or_shipped'], 'j-M-Y');
	$working_days = days_in_transit($order['or_shipped'], $order['or_country']);
	echo "<br />Overdue:$overdue<br/>";
}
$priceShow = $order['or_details'];
$priceShowArray = unserialize( $priceShow );
$basketHTML = $priceShowArray['BasketHTML'];
echo "<br />$basketHTML<br/>";
?>
<table>
<?php
	$oddEven = 0;

	if( array_key_exists( 'or_id', $order ) && ((int) $order['or_id'] > 0 ) )
	{
		$SheetQ = query( "select * from shopsystem_order_sheets_items where orsi_or_id = ".(int)$order['or_id'] );
		if( $SheetQ->numRows() > 0 )
		{
			echo "<tr class=\"".$rowClass."\"><th>&nbsp;Box&nbsp;</th><th>&nbsp;Code&nbsp;</th><th>&nbsp;Shipped&nbsp;</th><th>&nbsp;Days in Transit&nbsp;</th><th>&nbsp;Received&nbsp;</th></tr>";
			while( $SheetItems = $SheetQ->fetchRow() )
			{
				if( strlen( $SheetItems['orsi_no_stock'] ) )
				{
					$out_of_stock[] = $SheetItems['orsi_pr_name'];
					$to_credit += $SheetItems['orsi_price'];
				}
				$rowClass = $oddEven ? 'EvenRow' : 'OddRow'; $oddEven = !$oddEven;
				echo "<tr class=\"".$rowClass."\">";
				echo "<td width='40%'>&nbsp;{$SheetItems['orsi_pr_name']}&nbsp;</td>";
				echo "<td>&nbsp;{$SheetItems['orsi_stock_code']}&nbsp;</td>";
				if( strlen( $SheetItems['orsi_date_shipped'] ) )
				{
					echo "<td>&nbsp;".formatDateTime($SheetItems['orsi_date_shipped'], 'j-M-Y')."&nbsp;</td>";
					echo "<td>&nbsp;";
					if( !strlen( $SheetItems['orsi_received'] ) )
						echo days_in_transit($SheetItems['orsi_date_shipped'], $order['or_country']);
					echo "&nbsp;</td>";
					echo "<td>&nbsp;";
					if( strlen( $SheetItems['orsi_received'] ) )
						echo formatDateTime($SheetItems['orsi_received'], 'j-M-Y');
					echo "&nbsp;</td>";
				}
				else
					echo "<td></td>";
				echo "</tr>";
			}
		}
		else
			echo "<tr><td>Nothing on packing sheet</td></tr>";

		$or_shipping_details = getField( "select or_shipping_details from shopsystem_orders where or_tr_id = ".(int)$order['or_tr_id'] );
		if( strlen( $or_shipping_details ) )
		{
			$rowClass = $oddEven ? 'EvenRow' : 'OddRow'; $oddEven = !$oddEven;
			echo "</table><br /><table><tr class=\"".$rowClass."\"><td><strong>Shipping Details</strong></td></tr>";
			$shippingDetails = unserialize( $or_shipping_details );
			$details = $shippingDetails['ShippingDetails'];
			/*
			print_r( $details );
			Array ( [Name] => kevin danaher [first_name] => kevin [last_name] => danaher
				[0_B4BF] => [Email] => kevld123@yahoo.com [0_50A1] => 6418 menlo dr. [0_50A2] => san jose [0_50A4] => CA United States
				[0_B4C0] => 95120 [0_B4C1] => 408-206-5619 )
			*/
			$rowClass = $oddEven ? 'EvenRow' : 'OddRow'; $oddEven = !$oddEven;
			echo "<tr class=\"".$rowClass."\"><td>{$details['Name']}</td><td>{$details['Email']}</td></tr>";
			if( strlen( $details['0_B4BF'] ) )
			{
				$rowClass = $oddEven ? 'EvenRow' : 'OddRow'; $oddEven = !$oddEven;
				echo "<tr class=\"".$rowClass."\"><td>{$details['0_B4BF']}</td><td>&nbsp;</td></tr>";
			}
			$rowClass = $oddEven ? 'EvenRow' : 'OddRow'; $oddEven = !$oddEven;
			echo "<tr class=\"".$rowClass."\"><td>{$details['0_50A1']}</td><td>{$details['0_50A2']}</td></tr>";
			$rowClass = $oddEven ? 'EvenRow' : 'OddRow'; $oddEven = !$oddEven;
			echo "<tr class=\"".$rowClass."\"><td>{$details['0_50A4']}</td><td>{$details['0_B4C0']}</td></tr>";
		}
	}
	else
		echo "<!--- No Order Number -->";

	if( count( $out_of_stock ) )
		$nostock = implode( '/', $out_of_stock );
	else
		$nostock = 'xxxxxxx';
	$to_credit = number_format( $to_credit, 2 );
	if( strlen( $data['response'] ) )
	{
		$nr = '';
		eval( '$nr = "'.addslashes($data['response']).'";' );
		if( strlen( $nr ) )
			$data['response'] = $nr;
	}
?>
</table>
<br />
<strong>Attachments</strong><br />
<table width="100%" cellspacing="0" cellpadding="3">
<?php $tmpl_loop_rows = $data['Q_Attachments']->numRows(); $tmpl_loop_counter = 0; while ($row = $data['Q_Attachments']->fetchRow()) { $tmpl_loop_counter++; ?>
<?php $data['rowClass'] = $oddEven ? 'AdminEvenRow' : 'AdminOddRow'; $oddEven = !$oddEven; ?>
<tr class="<?php print(ss_HTMLEditFormat($data['rowClass'])); ?>">
	<td width='10%'><?php print(ss_HTMLEditFormat($row['cia_created'])); ?></td>
	<td width='10%'><?php print(ss_HTMLEditFormat($row['cia_name'])); ?></td>
	<td><a target='_blank' href='index.php?act=ImageManager.get&Image=<?php print(ss_HTMLEditFormat($row['cia_filename'])); ?>'>Download</a></td>
</tr>
<?php } ?>
</table>
<br />
<br />
<br />
<table width="100%" cellspacing="0" cellpadding="3">

<? $firstTime = true; $oddEven = true; ?>

<?php $tmpl_loop_rows = $data['Q_Issues']->numRows(); $tmpl_loop_counter = 0; while ($row = $data['Q_Issues']->fetchRow()) { $tmpl_loop_counter++; ?>

<?php if ($firstTime) { ?>
<tr>
	<td><strong>Who</strong></td>
	<td><strong>Session</strong></td>
	<td><strong>Created</strong></td>
	<td><strong>Text</strong></td>
	<td><strong>Emailed</strong></td>
</tr>
<? $firstTime = false; ?>
<?php } ?>
<?php $data['rowClass'] = $oddEven ? 'AdminEvenRow' : 'AdminOddRow'; $oddEven = !$oddEven; ?>

<tr class="<?php print(ss_HTMLEditFormat($data['rowClass'])); ?>">
	<td width='5%'><font color=<?php print(ss_HTMLEditFormat($row['colour'])); ?>><?php print(ss_HTMLEditFormat($row['who'])); ?></font></td>
	<td width='10%'><font color=<?php print(ss_HTMLEditFormat($row['colour'])); ?>>
		<a target='_blank' href='/index.php?act=ShopSystem_Issues.ShowLog&ce_id=<?php print(ss_HTMLEditFormat($row['id'])); ?>'>
		<?php print(ss_HTMLEditFormat($row['session'])); ?></a><br /><?php print(ss_HTMLEditFormat($row['website'])); ?></font></td>
	<td width='10%'><font color=<?php print(ss_HTMLEditFormat($row['colour'])); ?>><?php print(ss_HTMLEditFormat($row['created'])); ?></font><?php if( !$row['entry'] ) if( strcmp( $data['Issue']['us_members_viewed'], $row['created'] ) > 0 ) echo " seen"; else echo " unseen"; ?></td>
	<td><font color=<?php print(ss_HTMLEditFormat($row['colour'])); ?>><?php echo nl2br( $row['text'] ); ?></font></td>
	<td width='10%'><font color=<?php print(ss_HTMLEditFormat($row['colour'])); ?>><?php print(ss_HTMLEditFormat($row['emailed'])); ?></font>
	<?php if( $row['entry'] ) { ?>
	<a href='Javascript:confirmSplit("index.php?act=ShopSystem_Issues.Split&ce_id=<?php print(ss_HTMLEditFormat($row['entry'])); ?>");void(0);'>Split</a>
	<?php } else {
	if( $row['deleted'] )
		echo "Response DELETED<br />";
	else
		if( ss_adminCapability( ADMIN_DELETE_ISSUE ) ) { ?>
			<a href='Javascript:confirmDelete("index.php?act=ShopSystem_Issues.Delete&cir_id=<?php print(ss_HTMLEditFormat($row['response'])); ?>&BackURL=<?=ss_URLEncodedFormat($_SERVER['REQUEST_URI'])?>");void(0);'>Delete</a>
	<?php } } ?>
	</td>
</tr>

<?php } ?>
</table>

<?php
$name = strtoupper( $data['Issue']['us_first_name'][0] ).substr( $data['Issue']['us_first_name'], 1 );
?>

<form method="post" action="index.php?act=ShopSystem_Issues.AddResponse&ci_id=<?php print(ss_HTMLEditFormat($data['ci_id'])); ?>">
	<input type="hidden" name="BackURL" value="<?=$_SERVER['REQUEST_URI']?>" />
	<textarea name="Note" rows="40" cols="100" style="width:100%"><?php 
		echo "Hello $name<br /><br />";
		echo nl2br($data['response']);
		echo "<br /><br />Regards<br />{$_SESSION['User']['us_first_name']}";
	?></textarea><br /><br />
	SendEmail ?<input type='checkbox' name='SendEmail' value=1 checked='checked' />
	<input type="submit" name="Submit" value="Save Response">
</form>

<?php if( !$data['Issue']['ci_closed'] ) { ?>
<a href="index.php?act=ShopSystem_Issues.Close&ci_id=<? echo $data['Issue']['ci_id']."&BackURL=".ss_URLEncodedFormat('index.php?act=ShopSystem_Issues.Display')?>">Close Issue, Back to List</a>
<?php } ?>
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

	function confirmSplit(URL) {
		if ( confirm("Split this issue here?") ) {
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
<br />
<br />
<br />
<a name='the_end' href='<?=$data['BackURL']?>'><h2>Back</h2></a>

<?php
$foo = '';
?>
<?php $tmpl_loop_rows = $data['Q_Audit']->numRows(); $tmpl_loop_counter = 0; while ($row = $data['Q_Audit']->fetchRow()) { $tmpl_loop_counter++; ?>
<?php
	$foo = $row['au_timestamp'].'&nbsp;'.$row['au_notes']."<br />".$foo;
?>
<?php } ?>
Audit Trail for this user<br /><?=$foo?>
