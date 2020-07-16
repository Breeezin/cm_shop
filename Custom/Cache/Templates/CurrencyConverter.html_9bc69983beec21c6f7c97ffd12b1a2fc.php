<table width="100%">
	<tr>
		<td valign="middle" align="right">
				Show appoximate prices in
		<form method="post" action="<?php print(ss_HTMLEditFormat($data['AssetPath'])); ?>/Service/ChangeCurrencyCountry">
			<input type="hidden" name="BackURL" value="<?php print(ss_HTMLEditFormat($data['BackURL'])); ?>">
				<select name="CurrencyThreeCode" onchange="this.form.submit();">
					<?php $tmpl_loop_rows = $data['Q_Currencies']->numRows(); $tmpl_loop_counter = 0; while ($row = $data['Q_Currencies']->fetchRow()) { $tmpl_loop_counter++; ?>
						<?php if (stristr($row['cn_currency'],'CFA Fran')===false) { ?>
						<option value="<?php print(ss_HTMLEditFormat($row['cn_currency_code'])); ?>" <?php if ($data['CurrentTaxCurrency'] == $row['cn_currency_code']) { ?>selected="selected"<?php } ?>><?php print(ss_HTMLEditFormat($row['cn_currency'])); ?></option>
						<?php } ?>
					<?php } ?>
				</select>		
			</td>
		</form>
	</tr>
</table>
