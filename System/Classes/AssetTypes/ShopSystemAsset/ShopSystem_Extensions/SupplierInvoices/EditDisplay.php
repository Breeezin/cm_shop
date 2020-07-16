<?php

	// Check if any errors occured
	if (count($errors) != 0) {
		$errorText = '<P><TABLE WIDTH="95%" BORDER="0" ALIGN="CENTER"><TR><TD CLASS="entryErrors">
			Errors were detected in the data you entered, please correct the
			following issues and re-submit.  Nothing has been changed or added
			to the database at this point.<UL>';
		foreach ($errors as $messages) {
			foreach ($messages as $message) {
				$errorText .= "<LI>$message</LI>";
			}
		}
		$errorText .= '</UL></TD></TR></TABLE></P>';
	} else {
		$errorText = '';
	}

	// set the title for the page
	$this->display->title = $this->ATTRIBUTES['BreadCrumbs'].' : View/Edit '.$this->singular;

	// get the form fields to display in the form
	$form = $this->form($errors);
?>

<?php print $errorText ?>
<FORM enctype="MULTIPART/FORM-DATA" METHOD="POST" ACTION="<?php print basename($_SERVER['SCRIPT_NAME']).'?act='.$this->ATTRIBUTES['act'] ?>" ID="adminForm" NAME="adminForm" ONSUBMIT="processForm()">
	<script language="javascript">
		var extraProcesses = new Array();
		function processForm() {
			for (var x = 0; x < extraProcesses.length; x++) {
				extraProcesses[x]();
			}
		}
	</script>
	<P>
	<?php print $form ?>
	</P>

Invoice Records<br />
<?php
global $theaders;
global $tdisplay;
global $ar;
global $saveQty;
global $productSort;

$saveQty = 0;

$theaders = array(
'sil_id' => '',
'sil_sin_id' => '',
'sil_supplier_sku' => 'Supplier SKU',
'sil_description' => 'Description',
'sil_pr_id' => 'Product Name',
'sil_qty' => 'Qty',
'sil_qty_received' => 'Qty Received',
'sil_qty_put_in_stock' => 'Qty to put in stock',
'sil_raw_line_cost' => 'Total Cost',
'sil_computed_cost' => 'Box Cost'
);


$tdisplay = array(
'sil_supplier_sku' => '',
'sil_description' => '',
'sil_pr_id' => 'select pr_id, pro_stock_code, pr_name from shopsystem_products join shopsystem_product_extended_options on pro_pr_id = pr_id where pr_id = ',
'sil_qty' => '',
'sil_qty_received' => '',
'sil_qty_put_in_stock' => '',
'sil_raw_line_cost' => '',
'sil_computed_cost' => ''
);


//ss_log_message_r(  'Log:'.__FILE__.':'.__LINE__, $this );
$sp_id = $this->fields['sin_sp_id']->value;
$ve_id = $this->fields['sin_dest_ve_id']->value;
$put_in_stock_date = $this->fields['sin_instock_date']->value;
$productSort = $this->fields['sin_product_sort']->value;
if( strlen( $put_in_stock_date ) )
	$saveQty = 1;

if( !strlen( $ve_id ) )
	$ve_id = 2;	/// swiss
if( $sp_id )
	$catlist = getField( "select sp_category_list from supplier where sp_id = $sp_id" );
$ar = array();

$guessql = "select pr_id, pro_stock_code, pr_name, skl_sku from shopsystem_products join shopsystem_product_extended_options on pro_pr_id = pr_id left join supplier_sku_lookup on skl_pr_id = pr_id and skl_sp_id = $sp_id  where pr_ve_id = $ve_id and pr_combo IS NULL and pr_deleted IS NULL";
if( $sp_id && $catlist )
	$guessql .= " and pr_ca_id in ($catlist)";
$guessql .= " order by $productSort";

if( $gq = Query( $guessql ) )
	while( $other_row = $gq->fetchRow() )
		$ar[] = $other_row;

function emitRow( $row, $n )
{
	global $theaders;
	global $tdisplay;
	global $ar;
	global $saveQty;
	global $productSort;

	$retval = '';

	$retval .= "<tr>";
	foreach( $row as $key=>$val )
		if( array_key_exists( $key, $theaders ) )
			if( strlen($theaders[$key]) )
				switch( $key )
				{
				case 'sil_pr_id':
					$retval .= "<td><select  name='$key$n' id='$key$n'>";
					// options list etc
					$this_row = getRow( 'select pr_id, pro_stock_code, pr_name from shopsystem_products join shopsystem_product_extended_options on pro_pr_id = pr_id where pr_id = '.$val );
					if( $productSort == 'pr_name' )
						$retval .= "<option value='$val'>{$this_row['pr_name']}({$this_row['pro_stock_code']}) </option>";
					else
						$retval .= "<option value='$val'>{$this_row['pro_stock_code']}({$this_row['pr_name']}) </option>";
					$retval .= "<option value=''>&nbsp;</option>";
					foreach( $ar as $a )
					{
						if( $productSort == 'pr_name' )
							$retval .= "<option value='{$a['pr_id']}'>{$a['pr_name']} ({$a['pro_stock_code']})";
						else
							$retval .= "<option value='{$a['pr_id']}'>{$a['pro_stock_code']} ({$a['pr_name']})";
						if( strlen( $a['skl_sku'] ) )
							$retval .= " (SKU:{$a['skl_sku']})";
						$retval .= "</option>\n";
					}
					$retval .= "</select></td>";
					break;

				case 'sil_description':
					$retval .= "<td><input type='text' name='$key$n' id='$key$n' size=60 value='$val' /></td>";
					break;

				case 'sil_qty':
					$retval .= "<td><input type='text' name='$key$n' id='$key$n' value='$val'  onchange='boxCost($n)'/></td>";
					break;

				case 'sil_raw_line_cost':
					$retval .= "<td><input type='text' name='$key$n' id='$key$n' value='$val'  onchange='totalUp();boxCost($n)'/></td>";
					break;

				case 'sil_computed_cost':		// read only
					$retval .= "<td><input type='text' name='$key$n' id='$key$n' value='$val' readonly/></td>";
					break;

				case 'sil_supplier_sku':
					$retval .= "<td><input type='text' name='$key$n' id='$key$n' value='$val'  onchange='lookupSKU($n)'/></td>";
					break;

				case 'sil_qty_received':
				case 'sil_qty_put_in_stock':
					$retval .= "<td><input type='text' name='$key$n' id='$key$n' value='$val'  onfocus='focusQtyReceived($n)'/></td>";
					if( $val > 0 )
						$saveQty = 1;
					break;

				default:
					$retval .= "<td><input type='text' name='$key$n' id='$key$n' value='$val' /></td>";
				}
			else
				$retval .= "<input type='hidden' name='$key$n' id='$key$n' value='$val' />";
				
	$retval .= "<tr>";
	return $retval;
}

if( ss_isAdmin() )
{
	echo "This invoice has been received <INPUT TYPE=\"CHECKBOX\" NAME=\"SaveQtyCheckbox\" ID=\"SaveQtyCheckbox\" VALUE=\"1\" onClick=\"toggleSave()";
	if( strlen( $put_in_stock_date ) )
		echo " checked ";
	echo "\">";
	if( $this->primaryKey > 0 )
	{
		foreach($this->linkedTables as $linkedTable)
		{
			$columns = array();
			if( $result = query("SHOW COLUMNS FROM {$linkedTable->tableName}") )
				while( $row = $result->fetchRow() )
					$columns[] = $row;

			$Q_linkedItems = query("
				select * from {$linkedTable->tableName}
				where {$linkedTable->ourKey} = {$this->primaryKey}
			");
			echo "<table border=1 name='records{$linkedTable->tableName}' id='records{$linkedTable->tableName}'>";

			// headers
			echo "<tr>";
			foreach( $columns as $column )
				if( array_key_exists( $column['Field'], $theaders ) && strlen($theaders[$column['Field']]) )
						echo "<th>{$theaders[$column['Field']]}</th>";
			echo "</tr>";

			$r = 0;

			$Q_linkedItems->reset();
			$total = 0;
			while( $rw = $Q_linkedItems->fetchRow() )
			{
				echo emitRow( $rw, $r );
				$total += $rw['sil_raw_line_cost'];
				$r++;
			}

			echo "</table>\n";
?>
			<script language="Javascript">

				var r = <?=$r?>;
				var saveQty;

<?php echo " saveQty = $saveQty;"; if( $saveQty ) echo "document.getElementById( \"SaveQtyCheckbox\" ).checked = true;";  ?>

				function toggleSave( )
				{
					if( document.getElementById("SaveQtyCheckbox").value == 1 )
					{
						if( saveQty != 1 )
						{
							document.getElementsByName("sin_instock_date")[0].value = '<?php echo date( 'Y-m-d' );?>';
							saveQty = 1;
							for( i = 0; i < r; i++ )
							{
								document.getElementById("sil_qty_received"+i).value = document.getElementById("sil_qty"+i).value;
								document.getElementById("sil_qty_put_in_stock"+i).value = document.getElementById("sil_qty"+i).value;
							}
						}
					}
					else
						saveQty = 0;
					//saveQty = document.getElementById("SaveQty").value;
				}

				function focusQtyReceived( row )
				{
					if( !saveQty )
						document.getElementById("sil_raw_line_cost"+row).focus();
				}

				function lookupSKU( row )
				{
					var sku =  document.getElementById("sil_supplier_sku"+row).value;
					var prod =  document.getElementById("sil_pr_id"+row);
					for( i = 0; i < prod.length; i++)
					{
						if( prod[i].text.search( "(SKU:"+sku+")" ) > 0 )
						{
							prod[i].selected = 1;
							break;
						}
					}
				}

				function totalUp( )
				{
					var i;
					var ttl = 0.0;
					for( i = 0; i < r; i++ )
					{
						tv = parseFloat(document.getElementById("sil_raw_line_cost"+i).value);
						if( tv > 0 )
							ttl += tv;
					}

					document.getElementById("Total").value = ttl.toString();
				}

				totalUp( );

				function boxCost( row )
				{
					var qty =  document.getElementById("sil_qty"+row).value;
					var total_cost =  document.getElementById("sil_raw_line_cost"+row).value;
					if( qty > 0 && total_cost > 0  )
					{
						var per_cost = total_cost / qty;
						document.getElementById("sil_computed_cost"+row).value = ""+(Math.round(per_cost*100)/100).toString();
					}
					else
					{
						if( qty.length() > 0 || total_cost.length() > 0 )
							alert( "Either "+qty+" or "+total_cost+" is invalid" );
					}
				}

				function another<?=$linkedTable->tableName?>Row()
				{
					var table = document.getElementById("records<?=$linkedTable->tableName?>");
					var form = document.getElementById("adminForm");

					var tr = document.createElement("tr");
<?php
//			if( $rw = $Q_linkedItems->fetchRow() )		// poor assumption that there is already one row.
//				foreach( $rw as $key=>$val )
			foreach( $columns as $rw )
				if( array_key_exists( $rw['Field'], $theaders ) )
					if( strlen($theaders[$rw['Field']]) )
					{
?>
					var td = document.createElement("td");
					td.innerHTML = "<?php
						switch( $rw['Field'] )
						{
						case 'sil_pr_id':
							echo "<select  name='{$rw['Field']}\"";?>+r+<?php echo "\"' id='{$rw['Field']}\"";?>+r+<?php echo "\"'>";
							// options list etc
							foreach( $ar as $a )
							{
								if( $productSort == 'pr_name' )
									echo "<option value='{$a['pr_id']}'>".addslashes($a['pr_name'])." ({$a['pro_stock_code']})";
								else
									echo "<option value='{$a['pr_id']}'> {$a['pro_stock_code']} (".addslashes($a['pr_name']).")";
								if( strlen( $a['skl_sku'] ) )
									echo " (SKU:{$a['skl_sku']})";
								echo " </option>";
							}
							echo "</select>";
							break;

						case 'sil_qty':
							echo "<input type='text' name='{$rw['Field']}\"";?>+r+<?php echo "\"' id='{$rw['Field']}\"";?>+r+<?php echo "\"' value='' onchange='boxCost(\""?>+r+<?php echo "\")'/>";
							break;

						case 'sil_raw_line_cost':
							echo "<input type='text' name='{$rw['Field']}\"";?>+r+<?php echo "\"' id='{$rw['Field']}\"";?>+r+<?php echo "\"' value='' onchange='totalUp();boxCost(\""?>+r+<?php echo "\");'/>";
							break;

						case 'sil_computed_cost':		// read only
							echo "<input type='text' name='{$rw['Field']}\"";?>+r+<?php echo "\"' id='{$rw['Field']}\"";?>+r+<?php echo "\"' value='' readonly/>";
							break;

						case 'sil_description':
							echo "<input type='text' name='{$rw['Field']}\"";?>+r+<?php echo "\"' id='{$rw['Field']}\"";?>+r+<?php echo "\"' size=60 value='' />";
							break;

						case 'sil_supplier_sku':
							echo "<input type='text' name='{$rw['Field']}\"";?>+r+<?php echo "\"' id='{$rw['Field']}\"";?>+r+<?php echo "\"' value='' onchange='lookupSKU(\""?>+r+<?php echo "\")'/>";
							break;

						case 'sil_qty_received':
						case 'sil_qty_put_in_stock':
							echo "<input type='text' name='{$rw['Field']}\"";?>+r+<?php echo "\"' id='{$rw['Field']}\"";?>+r+<?php echo "\"' value='' onfocus='focusQtyReceived(\""?>+r+<?php echo "\")'/>";
							break;

						default:
							echo "<input type='text' name='{$rw['Field']}\"";?>+r+<?php echo "\"' id='{$rw['Field']}\"";?>+r+<?php echo "\"' value='' />";
						}
?>";
					tr.appendChild(td);
<?php
					}
					else
					{
?>
					hiddenField = document.createElement("input");
					hiddenField.type = "hidden";
					hiddenField.innerHTML = "<?php
							echo "<input type='hidden' name='{$rw['Field']}\"";?>+r+<?php echo "\"' id='{$rw['Field']}\"";?>+r+<?php echo "\"' value='' />";

?>";
					form.appendChild(hiddenField);
<?php				} ?>
					table.appendChild(tr);
					// focus on ...
					document.getElementById("sil_supplier_sku"+r).focus();
					r++;
				}
			</script>
<?php
			echo "<a href='javascript:another{$linkedTable->tableName}Row();'>[+]</a><br/><br/><br/>";
		}
	}
}

?>
	Total:<input type='text' name='Total' id='Total' value='<?=$total?>' readonly/>
	<br/>
	<br/>
	<INPUT TYPE="HIDDEN" NAME="BreadCrumbs" VALUE="<?php print ss_HTMLEditFormat($this->ATTRIBUTES['BreadCrumbs']) ?>">
	<INPUT TYPE="HIDDEN" NAME="BackURL" VALUE="<?php print ss_HTMLEditFormat($this->ATTRIBUTES['BackURL']) ?>">
	<?php 
	//if( !strlen( $put_in_stock_date ) ) {
	if( true ) {
	?>
	<INPUT TYPE="HIDDEN" NAME="DoAction" VALUE="Submit">
	<INPUT TYPE="SUBMIT" NAME="DoAction" VALUE="Submit">
	<INPUT TYPE="RESET" NAME="RESET" VALUE="Reset">
	<?php } ?>
	<? if ($this->backButtonText !== null) { ?>
		<INPUT TYPE="BUTTON" NAME="Back" VALUE="<?php print($this->backButtonText); ?>" ONCLICK="document.location='<?php print ss_JSStringFormat($this->ATTRIBUTES['BackURL']) ?>';">
	<? } ?>
	<INPUT TYPE="HIDDEN" NAME="<?php print $this->tablePrimaryKey ?>" VALUE="<?php print $this->ATTRIBUTES[$this->tablePrimaryKey] ?>">
	<INPUT TYPE="HIDDEN" NAME="as_id" VALUE="<?php print $this->assetLink ?>">
</FORM>			
