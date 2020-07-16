<?php 

if( file_exists( "Custom/ContentStore/Layouts/acmerockets/phone.php" ) )
	include( "Custom/ContentStore/Layouts/acmerockets/phone.php" );

if( ss_getUserID() > 0 )
{
	if( array_key_exists( 'User', $_SESSION ) && array_key_exists( 'us_password', $_SESSION['User'] ) && !strlen( $_SESSION['User']['us_password'] ) )
	{ ?>
	<h1>You have no password.<br /><a href='/Members/Service/Edit'>Click here to edit my profile to add a password</a></h1>
	<br />
	<?php
		return;
	}
	else
	{
		$Order = 0;
		if( array_key_exists( 'Order',  $this->ATTRIBUTES ) )
			$Order = (int)$this->ATTRIBUTES['Order'];
		/*
				and tr_completed = 1
				and or_cancelled IS NULL
		*/
		$visibleSQL = "select tr_id, or_id, or_shipped, tr_completed, or_paid, or_paid_not_shipped, or_cancelled, or_recorded, or_reshipment from shopsystem_orders, transactions 
				where or_us_id = ".ss_getUserID()."
				and or_tr_id = tr_id
				and or_deleted  = 0
				and ( or_shipped IS NULL OR or_shipped > NOW() - INTERVAL 24 WEEK )
				and or_recorded >  NOW() - INTERVAL 40 WEEK
				";
		if( $Order == 0 )
			$visibleSQL .= " order by !(tr_completed > 0), or_id desc";
		else
			$visibleSQL .= " and or_tr_id = $Order";

		$visibleOrders = query( $visibleSQL );

		// lets have a look and see if there is a response to an existing issue here.

		$QHTML = "";
		if( $Order > 0 )
		{
			// grab the last response for this order

			$QHTML = getField( "select cir_text from client_issue_response join client_issue on cir_ci_id = ci_id where ci_transaction_number = $Order order by cir_id desc limit 1" );
		}

		$customerOrders = array();
		while( $row = $visibleOrders->fetchRow() )
		{
			$packingBoxes = array();
			if( $boxesQ = query( "select * from shopsystem_order_sheets_items where orsi_or_id = {$row['or_id']}" ) )
				while( $packingRow = $boxesQ->fetchRow() )
					$packingBoxes[] = $packingRow;
			$orderBoxes = array();
			$orderCount = 0;
			if( $boxesQ = query( "select * from ordered_products where op_or_id = {$row['or_id']}" ) )
				while( $orderRow = $boxesQ->fetchRow() )
				{
					$orderBoxes[] = $orderRow;
					$orderCount += $orderRow['op_quantity'];
				}

			$customerOrders[$row['tr_id']] = array( 'order' => $row, 'packing' => $packingBoxes, 'boxes' => $orderBoxes, 'count' => $orderCount );
		}
		$visibleQuestions = query( "select * from canned_question where cq_invisible = false" );

	?>
	<script language="Javascript">
	var order_number = -1;
	function showOrder(what)
		{
		order_number = what;
		<?php
			foreach( $customerOrders as $tr_id=>$foo )
				echo "	hideOrder( $tr_id );\n";
		?>
		if( showme=document.getElementById('Order'+what) )
			{
			showme.style.display='block';
			rb=document.adminForm.boxchoice;
			for( i = 0; i < rb.length; i++ )
				rb[i].checked = false;
			}
		}
	function hideOrder(what)
		{  
		showme=document.getElementById('Order'+what);
		showme.style.display='none';
		}
	function checkOrderNumber()
		{
		<?php if( $Order == 0 ) { ?>
		if( order_number == -1 )
			{
			alert( "Please choose and applicable order number for your message." );
			return false;
			}

		rb=document.adminForm.boxchoice;
		for( i = 0; i < rb.length; i++ )
			if( rb[i].checked )
			{
				document.adminForm.chosen_box.value = rb[i].value;
				return true;
			}

		if( order_number == 0 )
			return true;

		alert( "Please choose an item in your order for this message." );
		return false;
		<?php } else { ?>
			order_number = <?= $Order?>;
			return true;
		<?php } ?>
		}
	</script>

	<FORM NAME="adminForm" ENCTYPE="multipart/form-data" METHOD="POST" ACTION="<?=$assetPath?>/Service/Issue/Do_Service/Yes" onsubmit="return checkOrderNumber();">
		<P>
		<?php
		if( $Order == 0 )
		{
		echo "<strong>If your message is about an order, you MUST select the RIGHT one here.</strong><br />";
		?>
		Order:
		<INPUT type='hidden' name='chosen_box' value='' />
		<SELECT name='order_number' onchange='showOrder(this.value)';>
		<OPTION value=-1 selected>Please choose an applicable order for your message.
		<OPTION value=0>None of these, my message is NOT about an order
		<?php
			foreach( $customerOrders as $tr_id=>$foo )
			{
				$row = $foo['order'];
				if( $row['tr_completed'] == 0 )
					echo "<OPTION value={$row['tr_id']}>".$row['tr_id'].'&nbsp;-&nbsp;'.formatDateTime( $row['or_recorded'] ).' unpaid';
				else
					echo "<OPTION value={$row['tr_id']}>".$row['tr_id'].'&nbsp;-&nbsp;'.formatDateTime( $row['or_recorded'] ).'&nbsp;-&nbsp;'.$foo['count'].'&nbsp;Products&nbsp;'.count($foo['packing'])."&nbsp;in&nbsp;packing";
			}
		?>
		</SELECT>
		<?php
		}
		else
		{
			foreach( $customerOrders as $tr_id=>$foo )
			{
				$row = $foo['order'];
				echo $row['tr_id'].'&nbsp;-&nbsp;'.formatDateTime( $row['or_recorded'] ).'&nbsp;-&nbsp;'.$foo['count'].'&nbsp;Products&nbsp;'.count($foo['packing'])."&nbsp;in&nbsp;packing";
			}
			?>
		<INPUT type='hidden' name='chosen_box' value='' />
		<INPUT type='hidden' name='order_number' value='<?=$Order?>' />
		<script language="Javascript">showOrder(<?=$Order?>);</script>
		<?php
		}
		foreach( $customerOrders as $tr_id=>$foo )
		{
			$row = $foo['order'];
			echo "<div id='Order$tr_id' style='display:none;'>";
			echo 'Order number '.$row['tr_id'].' from '.formatDateTime( $row['or_recorded'] ).'<br />';
			if( strlen( $row['or_reshipment'] ) )
				echo "This order is a reshipment, <strong>you were not charged</strong>. It was created at ".formatDateTime( $row['or_reshipment'] ).'<br />';
			if( strlen( $row['or_paid'] ) )
				echo "This order was marked paid at ".formatDateTime( $row['or_paid'] ).'<br />';
			else
				echo "This order is unpaid<br />";
			if( $row['tr_completed'] == 0 )
			{
				echo "This order has been abandonded<br />";
				echo "<strong>Please choose an item in this order that relates to your message</strong><br />";
				echo '<input  type="radio" name="boxchoice" id="boxchoice" value="'.$tr_id.'-all" />All of this order<br />';
			}
			else
			{
				echo "<strong>Please choose an item in this order that relates to your message</strong><br />";
				foreach( $foo['boxes'] as $box )
					echo '<input  type="radio" name="boxchoice" id="boxchoice" value="'.$tr_id.'-'.$box['op_pr_name'].'" />'. $box['op_quantity'].' x '.$box['op_pr_name'].'<br />';
				echo '<input  type="radio" name="boxchoice" id="boxchoice" value="'.$tr_id.'-all" />All of this order<br />';
				echo '<br />The packing floor has/had '.count($foo['packing']).' items<br />';

				$item = 0;
				foreach( $foo['packing'] as $rw )
				{
					echo "Item #".++$item." of {$rw['orsi_pr_name']} ";
					if( strlen( $rw['orsi_date_shipped'] ) )
						echo "was sent at ".formatDateTime( $rw['orsi_date_shipped'] );
					else
						if( strlen( $rw['orsi_no_stock'] ) )
							echo "was marked out of stock at ".formatDateTime( $rw['orsi_no_stock'] );
						else
							echo "is awaiting packing";
					if( strlen( $rw['orsi_received'] ) )
						echo " and you indicated you received it at ".formatDateTime( $rw['orsi_received'] );
					echo "<br />";
				}
			}
			echo "</div>";
	//		if( count( $customerOrders ) )
	//			echo "<script language=\"Javascript\">showOrder($Order);</script>";
		}
		if( strlen( $QHTML ) )
			echo "<div>$QHTML</div>";
		else
			if( $visibleQuestions->numRows() > 0 )
		{
		?>
		<br />
		Problem:
		<SELECT name='canned_question'>
		<?php
			while( $row = $visibleQuestions->fetchRow() )
				echo "<OPTION value=".$row['cq_id'].">".$row['cq_text'];
		?>
			<OPTION value=-1>None of these
		</SELECT>
		<?php } ?>
		<br />
		<input type="hidden" name="MAX_FILE_SIZE" value="8388608" />
		<br />
		Upload a supporting <strong>photo</strong><br />
		<input type="file" name="photo" size="40" /><br />
		Photos greater that 8 Megabytes <strong>cannot be uploaded</strong>.  Please scale down larger images.<br />
		If you are photographing damaged items, please upload one photo showing all the damage<br />
		Please upload only image files, the system will not accept documents of any sort.<br />
		<a href='/PhotoGuidelines'>Click here for guidelines on uploading photos.</a>
		<br />

<?php
	}
}
else		// not logged in
{ ?>
<FORM NAME="adminForm" METHOD="POST" ACTION="<?=$assetPath?>/Service/Issue/Do_Service/Yes">
	<P>
	You are not logged in.  If you purchased here before, you should log in <a href='/Members'>HERE</a><br />
	Name<br />
	<INPUT type='text' size='40' name='name' /><br />
	Email Address<br />
	<INPUT type='text' size='40' name='email' /><br />
<?php
	if( isset( $error ) && strlen( $error ) )
		echo '<font color=red>'.$error.'</font>';
}
?>
	<BR />
	Your message:<BR />
	<TEXTAREA name='issue' cols=100 rows=24></TEXTAREA>
	</P>	
	<INPUT TYPE="hidden" name="DoAction" value="yes">
<?php
if( ss_getUserID() <= 0 )
	echo "please type the word 'robot' backwards in this box <INPUT type='text' size='10' name='human' /><br />";
?>
	<INPUT TYPE="submit" NAME="Submit" VALUE="Send message to Support">		
	<h4><a href="/Members/Service/Messages">Cancel</a></h4>


</FORM>	
